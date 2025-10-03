<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\Carrousel;
use App\Repository\CarrouselRepository;

use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\HttpFoundation\Response;

class MediaCrudController extends AbstractCrudController
{
    public function __construct(private CarrouselRepository $carrouselRepo) {}

    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Média')
            ->setEntityLabelInPlural('Médias')
            ->setPageTitle(Crud::PAGE_INDEX, 'Médias')
            ->setPageTitle(Crud::PAGE_NEW, 'Importer des médias')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le média')
            ->setPaginatorPageSize(15); // 15 éléments par page
    }

    public function configureFields(string $pageName): iterable
    {
        // --- PAGE NEW : on propose les 2 modes -------------
        // (1) Upload unitaire mappé (Vich) -> requis pour le modal "+ Nouveau"
        if ($pageName === Crud::PAGE_NEW) {
            yield Field::new('file', 'Fichier (unitaire)')
                ->setFormType(VichFileType::class)
                ->setRequired(false);

            // (2) Upload multiple non mappé (files[])
            yield Field::new('files', 'Fichiers (multiple)')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'mapped'   => false,
                    'required' => false, // important (modal unitaire)
                    'constraints' => [
                        new Count(['max' => 10, 'maxMessage' => '10 fichiers maximum.']),
                        new All([new FileConstraint([
                            'maxSize' => '25M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                            'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP.',
                        ])]),
                    ],
                ])
                ->onlyOnForms();
        } else {
            // --- PAGE EDIT : upload unitaire (remplacement) ---
            yield Field::new('file', 'Nouveau fichier')
                ->setFormType(VichFileType::class)
                ->setRequired(false)
                ->onlyOnForms();
        }

        // --- Champs communs ---------------------------------
        yield ImageField::new('filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->hideOnForm();

        yield DateField::new('date', 'Date')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
      

        return $actions

            // Personnalise les actions existantes
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
     * Création :
     * - Si files[] est présent => import multiple + synchro carrousel (inactif)
     * - Sinon, si "file" (Vich) est présent => création unitaire + synchro carrousel
     * - Sinon => ne crée pas de Media vide (flash + exit)
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        // Cherche un éventuel champ files[] (upload multiple)
        $filesBag = $this->getContext()->getRequest()->files->all();
        $files = [];
        foreach ($filesBag as $payload) {
            if (isset($payload['files']) && is_iterable($payload['files'])) {
                $files = $payload['files'];
                break;
            }
        }

        // === MODE MULTI ===
        if (!empty($files)) {
            $pos      = (int) $this->carrouselRepo->getMaxPosition();
            $imported = 0;
            $linked   = 0;

            foreach ($files as $uploaded) {
                if (!$uploaded) { continue; }

                $m = new Media();
                $m->setFile($uploaded);     // Vich déplace + détermine filename
                $em->persist($m);
                $imported++;

                // crée l’entrée Carrousel si manquante
                $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $m]);
                if (!$exists) {
                    $c = new Carrousel();
                    $c->setMedia($m);
                    $c->setTitle(pathinfo((string) $m->getFilename(), PATHINFO_FILENAME) ?: '');
                    $c->setIsActive(false);
                    $c->setPosition(++$pos);
                    $em->persist($c);
                    $linked++;
                }
            }

            $em->flush();
            $this->addFlash('success', sprintf('%d fichier(s) importé(s), %d ajouté(s) au carrousel (inactifs).', $imported, $linked));
            return; // on ne passe PAS au parent en mode multi
        }

        // === MODE UNITAIRE ===
        if (!($entityInstance instanceof Media)) {
            parent::persistEntity($em, $entityInstance);
            return;
        }

        // Si l’upload unitaire (Vich) est vide => on bloque la création d’un Media vide
        if ($entityInstance->getFile() === null) {
            $this->addFlash('warning', 'Aucun fichier fourni.');
            return;
        }

        // Création unitaire normale
        parent::persistEntity($em, $entityInstance);

        // Synchro carrousel (si entrée manquante)
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

    /**
     * Édition : on conserve la synchro carrousel si elle n’existe pas encore.
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::updateEntity($em, $entityInstance);

        if (!($entityInstance instanceof Media)) {
            return;
        }

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
