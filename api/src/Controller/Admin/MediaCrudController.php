<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\Carrousel;
use App\Repository\CarrouselRepository;

use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

// Champs EasyAdmin
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

// Types & contraintes de formulaire Symfony
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

// Actions EasyAdmin
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

// Routing / contexte admin
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

// VichUploader (upload unitaire en édition)
use Vich\UploaderBundle\Form\Type\VichFileType;

use Symfony\Component\HttpFoundation\Response;

class MediaCrudController extends AbstractCrudController
{
    /**
     * L’entité gérée par ce CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    /**
     * Titres & libellés des pages.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Média')
            ->setEntityLabelInPlural('Médias')
            ->setPageTitle(Crud::PAGE_INDEX, 'Médias')
            ->setPageTitle(Crud::PAGE_NEW, 'Importer des médias')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le média');
    }

    /**
     * Champs affichés selon la page.
     *
     * NEW  : champ NON mappé "files[]" (upload multiple) → on lira en persistEntity()
     * EDIT : champ Vich "file" (upload unitaire pour remplacer le fichier)
     * INDEX/DETAIL : aperçu + date
     */
    public function configureFields(string $pageName): iterable
    {
        // --- PAGE NEW : multi-upload non mappé -------------------------
        if ($pageName === Crud::PAGE_NEW) {
            yield Field::new('files', 'Fichiers')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'multiple' => true, // tableau d’UploadedFile
                    'mapped'   => false, // pas lié à l’entité → géré manuellement
                    'required' => true,

                    // Validation côté Symfony
                    'constraints' => [
                        new Count([
                            'max' => 10,
                            'maxMessage' => '10 fichiers maximum.',
                        ]),
                        new All([
                            new FileConstraint([
                                'maxSize' => '25M', // ≤ upload_max_filesize (PHP)
                                'mimeTypes' => ['image/jpeg','image/png','image/webp'],
                                'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP.',
                            ])
                        ]),
                    ],
                ])
                ->onlyOnForms();
        } else {
            // --- PAGE EDIT : upload unitaire via Vich -------------------
            yield Field::new('file', 'Nouveau fichier')
                ->setFormType(VichFileType::class) // Vich gère move + maj filename
                ->onlyOnForms()
                ->setRequired(false);
        }

        // --- Champs communs (liste/détail) ------------------------------

        // Aperçu : on montre l’image d'après "filename" (en dehors des forms)
        yield ImageField::new('filename', 'Aperçu')
            ->setBasePath('/uploads/media') // adapte si besoin
            ->hideOnForm();

        // Date d’ajout (initialisée côté entité Media)
        yield DateField::new('date', 'Date')
            ->hideOnForm();
    }

    /**
     * Actions de la page.
     * On ajoute une **action batch** “Ajouter au carrousel” sur l’index,
     * et on personnalise quelques actions existantes sans les ré-ajouter.
     */
    public function configureActions(Actions $actions): Actions
    {
        // Action batch : rend les cases à cocher en index et le bouton d’action de masse
        $addToCarousel = Action::new('batchAddToCarousel', 'Ajouter au carrousel', 'fa fa-images')
            ->linkToCrudAction('batchAddToCarousel')
            ->createAsBatchAction();

        return $actions
            // Ajout de l’action batch en INDEX
            ->add(Crud::PAGE_INDEX, $addToCarousel)

            // Personnalisations d’actions existantes (pas d’add() ⇒ pas d’erreur “already exists”)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) =>
                $a->setLabel('Nouveau média')->setIcon('fa fa-plus')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn(Action $a) =>
                $a->setLabel('Enregistrer et ajouter un autre')->setIcon('fa fa-plus-circle')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn(Action $a) =>
                $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $a) =>
                $a->setLabel('Enregistrer et continuer')->setIcon('fa fa-save')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn(Action $a) =>
                $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            );
    }

    /**
     * Création : prise en charge de l’upload multiple.
     * - lit le champ non mappé "files[]"
     * - crée 1 entité Media par fichier
     * - Vich s’occupe du move + nommage via setFile()
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        // Récupération robuste des fichiers (quelque soit la racine du formulaire EA)
        $filesBag = $this->getContext()->getRequest()->files->all();
        $files = [];

        if (!empty($filesBag)) {
            foreach ($filesBag as $payload) {
                if (isset($payload['files']) && is_iterable($payload['files'])) {
                    $files = $payload['files'];
                    break;
                }
            }
        }

        if (!empty($files)) {
            foreach ($files as $uploaded) {
                if (!$uploaded) {
                    continue;
                }
                $m = new Media();
                $m->setFile($uploaded); // Vich : move + maj filename
                $em->persist($m);
            }
            $em->flush();

            $this->addFlash('success', sprintf('%d fichier(s) importé(s).', count($files)));
            return; // on n’utilise pas $entityInstance en mode multi-upload
        }

        // Aucun files[] → comportement par défaut
        parent::persistEntity($em, $entityInstance);
    }

    /**
     * Édition : Vich remplace l’éventuel fichier si "file" est renseigné.
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::updateEntity($em, $entityInstance);
    }

    /**
     * Action BATCH (index Média) :
     * crée une entrée Carrousel pour chaque média coché (position auto).
     */
    public function batchAddToCarousel(
        AdminContext $context,
        EntityManagerInterface $em,
        CarrouselRepository $repo,
        AdminUrlGenerator $url
    ):  Response {
        // URL de repli si jamais le referrer est null
        $mediaIndexUrl = $url->setController(MediaCrudController::class)
                            ->setAction(Crud::PAGE_INDEX)
                            ->generateUrl();

        // IDs cochés (peut être vide)
        $ids = $context->getRequest()->request->all('entityIds');
        if (!\is_array($ids) || empty($ids)) {
            $this->addFlash('warning', 'Aucun média sélectionné.');
            return $this->redirect($context->getReferrer() ?: $mediaIndexUrl);
        }

        // Départ pour la position (MAX + 1, +2, ...)
        $pos = (int) $repo->getMaxPosition();

        $added = 0;
        $skipped = 0;

        // On récupère les médias sélectionnés
        $medias = $em->getRepository(Media::class)->findBy(['id' => $ids]);

        foreach ($medias as $m) {
            // Skip si le média est déjà dans le carrousel
            $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $m]);
            if ($exists) {
                $skipped++;
                continue;
            }

            $pos++;
            $c = new Carrousel();
            $c->setMedia($m);
            $c->setTitle(pathinfo((string) $m->getFilename(), PATHINFO_FILENAME)); // optionnel
            $c->setIsActive(true);
            $c->setPosition($pos);

            $em->persist($c);
            $added++;
        }
        $em->flush();

            // Message de résultat
        if ($added > 0) {
            $this->addFlash('success', sprintf('%d image(s) ajoutée(s) au carrousel.', $added));
        }
        if ($skipped > 0) {
            $this->addFlash('info', sprintf('%d image(s) déjà présentes ont été ignorées.', $skipped));
        }

        // Redirection vers la liste du carrousel
        $carrouselUrl = $url->setController(CarrouselCrudController::class)
                            ->setAction(Crud::PAGE_INDEX)
                            ->generateUrl();

        return $this->redirect($carrouselUrl);
    }
}
