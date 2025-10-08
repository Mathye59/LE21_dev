<?php

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
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        /**
         * —————————————————————————————————————————————————————————————————
         * 1) PAGE "NOUVEAU" : on propose 2 modes
         *    a) Upload unitaire mappé (Vich) — fonctionne pour le +Nouveau (modal EA)
         *    b) Upload multiple non mappé (files[]) — pratique pour importer en masse
         *    NB : la création/MAJ de l’entrée Carrousel est faite par le SUBSCRIBER,
         *        donc on n’a rien à coder ici.
         * —————————————————————————————————————————————————————————————————
         */
        if ($pageName === Crud::PAGE_NEW) {
            yield Field::new('file', 'Fichier (unitaire)')
                ->setFormType(VichImageType::class)
                ->setRequired(false); // on peut aussi n’utiliser que le multiple

            yield Field::new('files', 'Fichiers (multiple)')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'multiple'   => true,
                    'mapped'     => false, // non mappé à l’entité => on le récupère à la main
                    'required'   => false,
                    'constraints'=> [
                        new Count([
                            'max' => 10,
                            'maxMessage' => '10 fichiers maximum par import.',
                        ]),
                        new All([new FileConstraint([
                            'maxSize' => '25M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                            'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP.',
                        ])]),
                    ],
                ])
                ->onlyOnForms();
        } else {
            /**
             * ————————————————————————————————————————————————————————
             * 2) PAGE "EDIT" : on propose un remplacement unitaire (Vich)
             * ————————————————————————————————————————————————————————
             */
            yield Field::new('file', 'Nouveau fichier')
                ->setFormType(VichImageType::class)
                ->setRequired(false)
                ->onlyOnForms();
        }

        /**
         * ————————————————————————————————————————————————————————————————
         * 3) Champs communs (index)
         * ————————————————————————————————————————————————————————————————
         */
        yield ImageField::new('filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->hideOnForm();

        yield DateField::new('date', 'Date')->hideOnForm();
    }

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
     * Création :
     *  - Si files[] (non mappé) existe -> import multiple : on crée un Media par fichier.
     *  - Sinon -> comportement unitaire par défaut (parent::persistEntity).
     *  NB : le SUBSCRIBER Doctrine se chargera de créer l’entrée Carrousel
     *       pour chaque Media, donc pas de logique carrousel ici.
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        // Récupération éventuelle des fichiers multiples non mappés
        $filesBag = $this->getContext()->getRequest()->files->all();
        $files = [];
        foreach ($filesBag as $payload) {
            if (isset($payload['files']) && is_iterable($payload['files'])) {
                $files = $payload['files'];
                break;
            }
        }

        // Mode MULTI : on crée un Media par fichier puis on flush une fois
        if (!empty($files)) {
            $imported = 0;

            foreach ($files as $uploaded) {
                if (!$uploaded) { continue; }

                $m = new Media();
                $m->setFile($uploaded);   // Vich déplacera et alimentera filename
                $em->persist($m);
                $imported++;
            }

            $em->flush();
            $this->addFlash('success', sprintf('%d fichier(s) importé(s).', $imported));
            return; // on ne continue pas sur le parent en mode multi
        }

        // Mode UNITAIRE : on laisse EA/Vich gérer normalement
        parent::persistEntity($em, $entityInstance);
    }

    /**
     * Édition : rien de spécifique, on laisse EA/Vich gérer.
     * Le subscriber gèrera aussi la synchro Carrousel si nécessaire.
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::updateEntity($em, $entityInstance);
    }
}
