<?php
/**
 * ==========================================================
 *  MediaCrudController (EasyAdmin)
 * ----------------------------------------------------------
 *  Objectif :
 *   - Gérer les imports d'images pour l'entité Media via EasyAdmin.
 *   - Offrir 2 modes côté page "Nouveau" :
 *       (a) upload unitaire mappé Vich (champ 'file'),
 *       (b) upload multiple non mappé (champ 'files[]') pour l'import en masse.
 *   - Laisser VichUploaderBundle déplacer le fichier et renseigner 'filename'.
 *   - Laisser un SUBSCRIBER Doctrine (mentionné) gérer la création/synchro Carrousel.
 *
 *  Hypothèses modèle & config :
 *   - Entité Media :
 *       * propriété 'file' (non mappée Doctrine) annotée @Vich\UploadableField(..., fileNameProperty="filename")
 *       * propriété 'filename' (string) persistée en BDD.
 *       * propriété 'date' (DateTimeImmutable ?), remplie par défaut (ex: PrePersist).
 *   - vich_uploader.yaml :
 *       * mapping "media" (ou équivalent) avec upload_destination '%kernel.project_dir%/public/uploads/media'
 *       * uri_prefix '/uploads/media'
 *
 *  Points d’attention :
 *   - [UPLOAD] Limites PHP ini (upload_max_filesize / post_max_size) > tailles de constraints.
 *   - [SECURITY] Valider les MIME + éventuellement la signature réelle (finfo) côté serveur.
 *   - [PERF] Import multiple → un seul flush ; éviter de lire les images en mémoire (laisser Vich travailler).
 *   - [UX] Feedback clair avec flash messages ; aperçu en INDEX.
 * ==========================================================
 */

namespace App\Controller\Admin;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class MediaCrudController extends AbstractCrudController
{
    /** FQCN de l’entité gérée par ce CRUD. */
    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    /**
     * Réglages globaux EA : libellés, titres, pagination.
     * [UX] Page "Nouveau" intitulée "Importer des médias" pour clarifier le lot.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Média')
            ->setEntityLabelInPlural('Médias')
            ->setPageTitle(Crud::PAGE_INDEX, 'Médias')
            ->setPageTitle(Crud::PAGE_NEW, 'Importer des médias')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le média')
            ->setPaginatorPageSize(15);
    }

    /**
     * Déclaration des champs affichés selon la page (INDEX/NEW/EDIT).
     * [UPLOAD] NEW : propose unitaire (Vich) + multiple (FileType non mappé).
     * [UPLOAD] EDIT : propose remplacement unitaire (Vich).
     * [UX] INDEX : aperçu + date (lecture seule).
     */
    public function configureFields(string $pageName): iterable
    {
        // =====================================================================
        // 1) PAGE NEW → 2 modes d'upload
        // =====================================================================
        if ($pageName === Crud::PAGE_NEW) {
            // (a) Unitaire : champ Vich mappé sur 'file' (non mappé Doctrine, mais relié au mapping Vich)
            yield Field::new('file', 'Fichier (unitaire)')
                ->setFormType(VichImageType::class)
                ->setRequired(false); // permettre d’utiliser uniquement le multiple si besoin

            // (b) Multiple : champ FileType non mappé 'files[]' (on persistera à la main en persistEntity)
            yield Field::new('files', 'Fichiers (multiple)')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'multiple'   => true,
                    'mapped'     => false, // [DX] indispensable : on récupère depuis la Request
                    'required'   => false,
                    // [SECURITY] & [UPLOAD] Constraints côté serveur (mime, taille, nombre)
                    'constraints'=> [
                        new Count([
                            'max' => 10,
                            'maxMessage' => '10 fichiers maximum par import.',
                        ]),
                        new All([
                            new FileConstraint([
                                'maxSize' => '25M', // [UPLOAD] cohérent avec PHP ini
                                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP.',
                            ])
                        ]),
                    ],
                ])
                ->onlyOnForms();
        } else {
            // =================================================================
            // 2) PAGE EDIT → remplacement unitaire via Vich
            // =================================================================
            yield Field::new('file', 'Nouveau fichier')
                ->setFormType(VichImageType::class)
                ->setRequired(false)
                ->onlyOnForms();
        }

        // =====================================================================
        // 3) Champs communs (INDEX/DETAIL) : aperçu + date
        // =====================================================================
        yield ImageField::new('filename', 'Aperçu')
            ->setBasePath('/uploads/media') // [ASSET] doit matcher uri_prefix du mapping Vich
            ->hideOnForm();                 // [UX] lecture seule en back-office

        yield DateField::new('date', 'Date')
            ->hideOnForm();                 // [DX] si gérée en PrePersist/PreUpdate côté entité
    }

    /**
     * Personnalisation des actions (labels + icônes).
     * [UX] Icônes FA pour confort visuel ; labels clairs.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW,
                fn(Action $a) => $a->setLabel('Nouveau média')->setIcon('fa fa-plus')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $a) => $a->setLabel('Enregistrer et ajouter un autre')->setIcon('fa fa-plus-circle')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                fn(Action $a) => $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE,
                fn(Action $a) => $a->setLabel('Enregistrer et continuer')->setIcon('fa fa-save')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN,
                fn(Action $a) => $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            );
    }

    /**
     * PERSIST (création) :
     *  - Si 'files[]' (non mappé) est fourni → import multiple : 1 entité Media par fichier, 1 seul flush.
     *  - Sinon → comportement standard (unitaire) : parent::persistEntity() (Vich fera le job).
     *
     * [PERF] Un flush unique pour le lot.
     * [DX] On lit la Request pour récupérer 'files' car non mappé (mapped=false).
     * [SUBSCRIBER] La logique Carrousel reste dans un Subscriber Doctrine, pas ici.
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        // Récupération des fichiers multiples envoyés (clé 'files') via la Request
        $filesBag = $this->getContext()->getRequest()->files->all();
        $files = [];
        foreach ($filesBag as $payload) {
            if (isset($payload['files']) && is_iterable($payload['files'])) {
                $files = $payload['files']; // tableau d'UploadedFile
                break;
            }
        }

        // === Mode MULTIPLE : créer un Media par fichier + flush une fois ===
        if (!empty($files)) {
            $imported = 0;

            foreach ($files as $uploaded) {
                if (!$uploaded) { continue; } // robuste aux trous dans le tableau

                $m = new Media();
                $m->setFile($uploaded); // [UPLOAD] Vich déplacera le fichier et alimentera 'filename'
                $em->persist($m);
                $imported++;
            }

            $em->flush();
            $this->addFlash('success', sprintf('%d fichier(s) importé(s).', $imported));
            return; // [DX] ne pas tomber sur le parent en mode multi
        }

        // === Mode UNITAIRE : laisser EA/Vich gérer normalement ===
        parent::persistEntity($em, $entityInstance);
    }

    /**
     * UPDATE (édition) :
     *  - Remplacement unitaire via Vich.
     *  - Rien de spécifique ici (le Subscriber gère Carrousel si nécessaire).
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::updateEntity($em, $entityInstance);
    }
}
