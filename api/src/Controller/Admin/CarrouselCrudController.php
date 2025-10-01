<?php

namespace App\Controller\Admin;

use App\Entity\Carrousel;
use App\Controller\Admin\MediaCrudController;
use App\Repository\CarrouselRepository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

use Symfony\Component\HttpFoundation\Response;

class CarrouselCrudController extends AbstractCrudController
{
    /**
     * On injecte le repository pour:
     * - récupérer la position max à la création (auto MAX+1),
     * - (éventuellement) toute logique de position additionnelle plus tard.
     */
    public function __construct(  private CarrouselRepository $repo,
    private AdminUrlGenerator $urlGenerator,) {}

    /** Entité gérée par ce CRUD. */
    public static function getEntityFqcn(): string
    {
        return Carrousel::class;
    }

    /** Libellés et tri par défaut. */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Carrousel')
            ->setEntityLabelInSingular('Image')
            ->setDefaultSort(['position' => 'ASC']); // index toujours trié par position
    }

    /**
     * Index : on force un tri secondaire sur position ASC
     * (au cas où l’utilisateur manipule le tri via l’UI).
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->addOrderBy('entity.position', 'ASC');
    }

    /**
     * Création : si la position n’est pas renseignée, place l’élément à la fin
     * (MAX(position) + 1).
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Carrousel) {
            if (!$entityInstance->getPosition()) {
                $entityInstance->setPosition($this->repo->getMaxPosition() + 1);
            }
        }

        parent::persistEntity($em, $entityInstance);
    }

    /**
     * Champs du formulaire / index.
     *
     * - media : Association vers Media (sélection d’un existant + bouton “+ Nouveau” en modal)
     * - preview (HTML) : aperçu **dans** le formulaire (non mappé, pas d’upload)
     * - image (ImageField) : aperçu en **liste/détail** uniquement (pas en form)
     * - title/isActive : infos du slide
     * - position : visible seulement en index (gérée via les boutons, pas en édition)
     */
    public function configureFields(string $pageName): iterable
    {
        // Sélection d’un Media existant (et possibilité d’en créer un via le modal)
        yield AssociationField::new('media', 'Média')
            ->setCrudController(MediaCrudController::class) // ➜ bouton “+ Nouveau” en modal
            ->autocomplete();                                // recherche dans les médias

        // Aperçu DANS le formulaire (statique, non-live) : champ HTML non mappé.
        yield TextField::new('preview', 'Aperçu')
            ->onlyOnForms()
            ->renderAsHtml()
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('data', (function () {
                $entity = $this->getContext()?->getEntity()?->getInstance();
                $url = ($entity && $entity->getMedia())
                    ? '/uploads/media/' . $entity->getMedia()->getFilename()
                    : '';

                return $url
                    ? sprintf('<img src="%s" style="max-width:160px;max-height:120px;border-radius:6px;display:block">', $url)
                    : '<em>Aucun média sélectionné</em>';
            })());

        // Aperçu en INDEX/DETAIL (ImageField n’est pas utilisé en form → pas de setUploadDir requis).
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->hideOnForm();

        // Champs métier
        yield TextField::new('title', 'Titre')->hideOnIndex();
        yield BooleanField::new('isActive', 'Actif');

        // Position non éditable manuellement (on utilise Monter/Descendre)
        yield IntegerField::new('position', 'Position')->onlyOnIndex();
    }

    /**
     * Actions :
     * - “Choisir dans les médias” : bouton global qui ouvre la galerie des médias
     *   (pour cocher → action batch “Ajouter au carrousel” dans MediaCrudController).
     * - “Monter” / “Descendre” : réordonnancement simple par échange.
     */
    public function configureActions(Actions $actions): Actions
    {
         // URL vers l’index du CRUD Média
        $mediaIndexUrl = $this->urlGenerator
        ->setController(MediaCrudController::class)
        ->setAction(Crud::PAGE_INDEX)
        ->generateUrl();

        // Bouton global : bascule vers la page index du CRUD Média
        $pickFromMedia = Action::new('pickFromMedia', 'Choisir dans les médias', 'fa fa-images')
            ->linkToUrl($mediaIndexUrl)
            ->createAsGlobalAction();

        // Réordonner
        $moveUp   = Action::new('moveUp', 'Monter', 'fa fa-arrow-up')->linkToCrudAction('moveUp');
        $moveDown = Action::new('moveDown', 'Descendre', 'fa fa-arrow-down')->linkToCrudAction('moveDown');

        return $actions
            ->add(Crud::PAGE_INDEX, $pickFromMedia)
            ->add(Crud::PAGE_INDEX, $moveUp)
            ->add(Crud::PAGE_INDEX, $moveDown);
    }

    /**
     * Action "Monter" : échange la position avec l’élément juste au-dessus.
     */
    public function moveUp(EntityManagerInterface $em): Response
    {
        /** @var Carrousel $item */
        $item = $this->getContext()->getEntity()->getInstance();

        $prev = $em->getRepository(Carrousel::class)->findOneBy([
            'position' => $item->getPosition() - 1,
        ]);

        if ($prev) {
            $p = $item->getPosition();
            $item->setPosition($prev->getPosition());
            $prev->setPosition($p);
            $em->flush();
        }

        return $this->redirect($this->getContext()->getReferrer());
    }

    /**
     * Action "Descendre" : échange la position avec l’élément juste en-dessous.
     */
    public function moveDown(EntityManagerInterface $em): Response
    {
        /** @var Carrousel $item */
        $item = $this->getContext()->getEntity()->getInstance();

        $next = $em->getRepository(Carrousel::class)->findOneBy([
            'position' => $item->getPosition() + 1,
        ]);

        if ($next) {
            $p = $item->getPosition();
            $item->setPosition($next->getPosition());
            $next->setPosition($p);
            $em->flush();
        }

        return $this->redirect($this->getContext()->getReferrer());
    }
}
