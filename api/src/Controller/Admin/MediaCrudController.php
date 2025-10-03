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
    public function __construct(private CarrouselRepository $carrouselRepo) {}
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
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le média')
            ->setPaginatorPageSize(15) ;       // <= 15 par page
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
     * + synchronisation avec carrousel
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

        // MODE MULTI-UPLOAD (champ non mappé "files[]")
        if (!empty($files)) {
            // position de départ = MAX(position)
            $pos = (int) $this->carrouselRepo->getMaxPosition();
            $imported = 0;
            $linked   = 0;

            foreach ($files as $uploaded) {
                if (!$uploaded) {
                    continue;
                }

            // Crée le Media (Vich gère move+filename via setFile)
            $m = new Media();
            $m->setFile($uploaded);
            $em->persist($m);
            $imported++;

            // Crée l’entrée Carrousel associée si elle n’existe pas
            $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $m]);
            if (!$exists) {
                $c = new Carrousel();
                $c->setMedia($m);
                $c->setTitle(pathinfo((string) $m->getFilename(), PATHINFO_FILENAME) ?: '');
                $c->setIsActive(false);       // inactif par défaut
                $c->setPosition(++$pos);      // à la suite
                $em->persist($c);
                $linked++;
            }
        }

        $em->flush();

        $this->addFlash('success', sprintf('%d fichier(s) importé(s), %d ajouté(s) au carrousel (inactifs).', $imported, $linked));
        return; // on ne passe pas par le parent en mode multi-upload
    }

        // MODE UNITAIRE (pas de files[] → on laisse EA créer le Media)
        parent::persistEntity($em, $entityInstance);

        if ($entityInstance instanceof Media) {
            // S’assure qu’il existe une ligne Carrousel pour ce média
            $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $entityInstance]);
            if (!$exists) {
                $pos = (int) $this->carrouselRepo->getMaxPosition() + 1;

                $c = new Carrousel();
                $c->setMedia($entityInstance);
                $c->setTitle(pathinfo((string) $entityInstance->getFilename(), PATHINFO_FILENAME) ?: '');
                $c->setIsActive(false);
                $c->setPosition($pos);

                $em->persist($c);
                $em->flush();

                $this->addFlash('info', 'Le média a été synchronisé automatiquement dans le carrousel (inactif).');
            }
        }
    }

    /**
     * Édition : Vich remplace l’éventuel fichier si "file" est renseigné.
     */
   public function updateEntity(EntityManagerInterface $em, $entityInstance): void
{
    parent::updateEntity($em, $entityInstance);

    if ($entityInstance instanceof Media) {
        $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $entityInstance]);
        if (!$exists) {
            $pos = (int) $this->carrouselRepo->getMaxPosition() + 1;

            $c = new Carrousel();
            $c->setMedia($entityInstance);
            $c->setTitle(pathinfo((string) $entityInstance->getFilename(), PATHINFO_FILENAME) ?: '');
            $c->setIsActive(false);
            $c->setPosition($pos);

            $em->persist($c);
            $em->flush();

            $this->addFlash('info', 'Ce média a été synchronisé dans le carrousel (inactif).');
        }
    }
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
): Response {
    $request = $context->getRequest();

    // Récupère les IDs médias sélectionnés (tous les cas)
    $ids = $request->request->all('entityIds');                 // cas EA récent
    if (empty($ids)) {
        $batchForm = $request->request->all('batch_form');      // cas EA plus ancien
        if (\is_array($batchForm) && !empty($batchForm['entityIds'])) {
            $ids = $batchForm['entityIds'];
        }
    }
    if (\is_string($ids)) {                                     // cas "1,2,3"
        $ids = array_filter(array_map('trim', explode(',', $ids)));
    }
    // Normalise : tableau d’entiers uniques
    $ids = array_values(array_unique(array_map('intval', (array) $ids)));

    // Si rien de coché → retour propre avec message
    if (empty($ids)) {
        $this->addFlash('warning', 'Aucun média sélectionné.');
        $fallback = $url->setController(MediaCrudController::class)
                        ->setAction(Crud::PAGE_INDEX)
                        ->generateUrl();
        return $this->redirect($context->getReferrer() ?: $fallback);
    }

    // Position de départ = MAX(position) 
    $pos = (int) $repo->getMaxPosition();

    $added   = 0;
    $skipped = 0;

    // Récupère les images cochées
    $medias = $em->getRepository(Media::class)->findBy(['id' => $ids], ['id' => 'ASC']);

    foreach ($medias as $m) {
        // ignorer si déjà présent dans le carrousel
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

    // Résultat affiché
    if ($added > 0) {
        $this->addFlash('success', sprintf('%d image(s) ajoutée(s) au carrousel.', $added));
    }
    if ($skipped > 0) {
        $this->addFlash('info', sprintf('%d image(s) déjà présentes ont été ignorées.', $skipped));
    }

    //Retour vers carrousel
    $carrouselUrl = $url->setController(CarrouselCrudController::class)
                        ->setAction(Crud::PAGE_INDEX)
                        ->generateUrl();

    return $this->redirect($carrouselUrl);
}
}
