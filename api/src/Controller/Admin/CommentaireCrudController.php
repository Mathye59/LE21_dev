<?php

namespace App\Controller\Admin;

use App\Entity\Commentaire;
use App\Entity\ArticleBlog;
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class CommentaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commentaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Commentaires')
            ->setEntityLabelInSingular('Commentaire')
            ->setPageTitle(Crud::PAGE_INDEX, 'Modération des commentaires')
            ->setDefaultSort(['date' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield AssociationField::new('article', 'Article')
            ->setRequired(true)
            ->setFormTypeOption('choice_label', 'titre') // adapte si besoin

            // petit confort : sur index, on montre seulement le titre
        ;

        yield TextField::new('pseudoClient', 'Pseudo')
            ->setMaxLength(50)
            ->setRequired(true);

        yield TextField::new('texte', 'Texte')
            ->setMaxLength(200)
            ->setRequired(true);

        yield DateTimeField::new('date', 'Posté le')
            ->onlyOnIndex();

        yield BooleanField::new('approuve', 'Approuvé');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('approuve', 'Approuvé'))
            ->add(EntityFilter::new('article', 'Article'))
            ->add(DateTimeFilter::new('date', 'Date'));
    }

    public function configureActions(Actions $actions): Actions
    {
        // Actions custom : Approuver / Retirer
        $approve = Action::new('approve', 'Approuver', 'fa fa-check')
            ->linkToCrudAction('approveAction')
            ->displayIf(fn(Commentaire $c) => !$c->isApprouve());

        $unapprove = Action::new('unapprove', 'Retirer', 'fa fa-ban')
            ->linkToCrudAction('unapproveAction')
            ->displayIf(fn(Commentaire $c) => $c->isApprouve());

        // Version batch
        $approveBatch = Action::new('approveBatch', 'Approuver la sélection', 'fa fa-check')
            ->linkToCrudAction('approveBatchAction')
            ->createAsGlobalAction();

        $unapproveBatch = Action::new('unapproveBatch', 'Retirer la sélection', 'fa fa-ban')
            ->linkToCrudAction('unapproveBatchAction')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $approve)
            ->add(Crud::PAGE_INDEX, $unapprove)
            ->add(Crud::PAGE_INDEX, $approveBatch)
            ->add(Crud::PAGE_INDEX, $unapproveBatch)

            ->add(Crud::PAGE_EDIT, $approve)
            ->add(Crud::PAGE_EDIT, $unapprove)

            // on évite l’édition “inline” sur index pour ne pas valider par erreur
            ;
    }

    /** À la création, on force la date si absente et approuve=false par défaut */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Commentaire) {
            if ($entityInstance->getDate() === null) {
                $entityInstance->setDate(new \DateTimeImmutable());
            }
            // l’entité a déjà false par défaut, on sécurise au cas où
            if ($entityInstance->isApprouve() !== false) {
                $entityInstance->setApprouve(false);
            }
        }
        parent::persistEntity($em, $entityInstance);
    }

    // ==== Actions CRUD custom (mono) =========================================

    public function approveAction(EntityManagerInterface $em)
    {
        /** @var Commentaire $comment */
        $comment = $this->getContext()->getEntity()->getInstance();
        $comment->setApprouve(true);
        $em->flush();

        $this->addFlash('success', 'Commentaire approuvé.');
        return $this->redirect($this->adminUrlGenerator()->setAction(Action::INDEX)->generateUrl());
    }

    public function unapproveAction(EntityManagerInterface $em)
    {
        /** @var Commentaire $comment */
        $comment = $this->getContext()->getEntity()->getInstance();
        $comment->setApprouve(false);
        $em->flush();

        $this->addFlash('info', 'Approbation retirée.');
        return $this->redirect($this->adminUrlGenerator()->setAction(Action::INDEX)->generateUrl());
    }

    // ==== Actions CRUD custom (batch) ========================================

    public function approveBatchAction(EntityManagerInterface $em)
    {
        $ids = $this->getContext()->getRequest()->query->all('entityId');
        if (!empty($ids)) {
            $repo = $em->getRepository(Commentaire::class);
            foreach ($ids as $id) {
                if ($c = $repo->find($id)) {
                    $c->setApprouve(true);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Commentaires approuvés.');
        }
        return $this->redirect($this->adminUrlGenerator()->setAction(Action::INDEX)->generateUrl());
    }

    public function unapproveBatchAction(EntityManagerInterface $em)
    {
        $ids = $this->getContext()->getRequest()->query->all('entityId');
        if (!empty($ids)) {
            $repo = $em->getRepository(Commentaire::class);
            foreach ($ids as $id) {
                if ($c = $repo->find($id)) {
                    $c->setApprouve(false);
                }
            }
            $em->flush();
            $this->addFlash('info', 'Approbation retirée pour la sélection.');
        }
        return $this->redirect($this->adminUrlGenerator()->setAction(Action::INDEX)->generateUrl());
    }

    private function adminUrlGenerator()
    {
        return $this->container->get(\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator::class)
            ->setController(self::class);
    }
}
