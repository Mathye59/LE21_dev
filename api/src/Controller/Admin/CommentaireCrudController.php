<?php

namespace App\Controller\Admin;

use App\Entity\Commentaire; // Entité modérée ici
use App\Entity\ArticleBlog; // Utilisé dans les filtres/associations
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;     // Config générale EA (titres, tri, pagination…)
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;   // Représentation d’une action (bouton) EA
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;  // Builder des actions disponibles
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;  // Builder des filtres EA

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField; // Champ relationnel (vers Article)
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;     // Case à cocher (approuvé ?)
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;    // Affichage DateTime (lecture seule ici)
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;          // ID en index
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;        // Pseudo / texte

use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;   // Filtre par booléen (approuvé)
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;    // Filtre par entité (article)
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;  // Filtre par date

class CommentaireCrudController extends AbstractCrudController
{
    /**
     * FQCN (= nom de classe complet) de l’entité gérée.
     * EA l’utilise pour générer automatiquement les pages CRUD.
     */
    public static function getEntityFqcn(): string
    {
        return Commentaire::class;
    }

    /**
     * Configuration globale du CRUD :
     * - Libellés, titre, tri par défaut et pagination.
     * - Ici on parle d’un écran de "modération".
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Commentaires')               // libellé pluriel
            ->setEntityLabelInSingular('Commentaire')              // libellé singulier
            ->setPageTitle(Crud::PAGE_INDEX, 'Modération des commentaires') // titre de la liste
            ->setDefaultSort(['date' => 'DESC'])                   // [UX] les plus récents d’abord
            ->setPaginatorPageSize(20);                            // [UX] 20 par page : lecture confortable
            // ->setEntityPermission('ROLE_ADMIN') // [SECURITY] option : restreindre par rôle
    }

    /**
     * Déclaration des champs affichés selon la page (INDEX/NEW/EDIT/DETAIL).
     * [UX] On garde l’édition simple : pseudo + texte + article + approuvé.
     * [SECURITY] On évite l’édition inline en index pour limiter les fausses manips.
     */
    public function configureFields(string $pageName): iterable
    {
        // ID interne : utile en index pour diagnostiquer rapidement
        yield IdField::new('id')->onlyOnIndex();

        // Article lié : affiché/éditable via un select (ou autocomplete si besoin)
        yield AssociationField::new('article', 'Article')
            ->setRequired(true)
            ->setFormTypeOption('choice_label', 'titre'); // [UX] affiche le titre de l’article dans le select

        // Pseudo auteur du commentaire (si tu n’as pas d’auth côté front)
        yield TextField::new('pseudoClient', 'Pseudo')
            ->setMaxLength(50)
            ->setRequired(true); // [VALIDATION UI] prévoir aussi Assert\NotBlank + Length côté entité

        // Corps du commentaire (court). Si besoin de + long, passer en TextEditorField/TextareaField.
        yield TextField::new('texte', 'Texte')
            ->setMaxLength(200)
            ->setRequired(true); // [VALIDATION UI] côté entité : Length(max=…)+NotBlank

        // Date de publication (lecture seule en index)
        yield DateTimeField::new('date', 'Posté le')
            ->onlyOnIndex(); // [UX] on n'édite pas la date à la main

        // Statut de modération
        yield BooleanField::new('approuve', 'Approuvé');
    }

    /**
     * Filtres EA en index :
     * - approuvé ? (bool)
     * - article (entité)
     * - date (intervalle)
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('approuve', 'Approuvé'))
            ->add(EntityFilter::new('article', 'Article'))
            ->add(DateTimeFilter::new('date', 'Date'));
    }

    /**
     * Définition des actions :
     * - Deux actions mono-objet : Approuver / Retirer (s’affichent selon l’état).
     * - Deux actions globales (batch) sur la sélection : Approuver / Retirer.
     *
     * [UX] On n’active pas l’édition inline en index pour éviter de cocher/décocher par erreur.
     * [SECURITY] Par défaut, ces actions sont en GET ; pour une hygiène stricte, préférer POST + CSRF (voir note).
     */
    public function configureActions(Actions $actions): Actions
    {
        // Action mono-objet : "Approuver"
        // [UX] visible uniquement si pas encore approuvé
        $approve = Action::new('approve', 'Approuver', 'fa fa-check')
            ->linkToCrudAction('approveAction')
            // NB : selon ta version d’EA, displayIf reçoit plutôt un EntityDto.
            // Ici tu passes un Commentaire typé ; si souci, remplace par ->displayIf(fn(EntityDto $e) => !($e->getInstance())->isApprouve())
            ->displayIf(fn(Commentaire $c) => !$c->isApprouve());

        // Action mono-objet : "Retirer" (désapprouver)
        $unapprove = Action::new('unapprove', 'Retirer', 'fa fa-ban')
            ->linkToCrudAction('unapproveAction')
            ->displayIf(fn(Commentaire $c) => $c->isApprouve());

        // Actions globales (batch) : créées comme "global action" (bouton en haut)
        $approveBatch = Action::new('approveBatch', 'Approuver la sélection', 'fa fa-check')
            ->linkToCrudAction('approveBatchAction')
            ->createAsGlobalAction(); // [UX] agit sur la sélection

        $unapproveBatch = Action::new('unapproveBatch', 'Retirer la sélection', 'fa fa-ban')
            ->linkToCrudAction('unapproveBatchAction')
            ->createAsGlobalAction();

        return $actions
            // Index : ajout des actions mono-objet + batch
            ->add(Crud::PAGE_INDEX, $approve)
            ->add(Crud::PAGE_INDEX, $unapprove)
            ->add(Crud::PAGE_INDEX, $approveBatch)
            ->add(Crud::PAGE_INDEX, $unapproveBatch)

            // Edit : on remet les actions mono-objet (pratique depuis l’écran d’édition)
            ->add(Crud::PAGE_EDIT, $approve)
            ->add(Crud::PAGE_EDIT, $unapprove);

            // [UX] Pas d’édition inline en index (volontairement), donc pas d’Action::EDIT_IN_LIST
    }

    /**
     * À la création :
     *  - Force la date si absente -> now (DateTimeImmutable).
     *  - Force approuve=false par défaut (hygiène).
     *
     * [DATA] On s’assure d’un état initial "non approuvé", même si le form/UI
     *        a une valeur différente (défense côté serveur).
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Commentaire) {
            // Date par défaut (si non fournie) : maintenant
            if ($entityInstance->getDate() === null) {
                $entityInstance->setDate(new \DateTimeImmutable());
            }
            // Sécurise l’état initial : non approuvé
            if ($entityInstance->isApprouve() !== false) {
                $entityInstance->setApprouve(false);
            }
        }
        parent::persistEntity($em, $entityInstance);
    }

    // ==== Actions CRUD custom (mono) =========================================

    /**
     * Action : approuver un commentaire (mono-objet).
     * [SECURITY] Ici, mutation via GET. Pour un durcissement : forcer POST + CSRF (voir notes en bas).
     */
    public function approveAction(EntityManagerInterface $em)
    {
        /** @var Commentaire $comment L’instance ciblée par le bouton */
        $comment = $this->getContext()->getEntity()->getInstance();
        $comment->setApprouve(true);
        $em->flush();

        $this->addFlash('success', 'Commentaire approuvé.');
        // Redirection vers l’index de CE contrôleur
        return $this->redirect($this->adminUrlGenerator()->setAction(Action::INDEX)->generateUrl());
    }

    /**
     * Action : retirer l’approbation (mono-objet).
     */
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

    /**
     * Action globale : approuver tous les éléments sélectionnés en index.
     * [PERF] Ici on itère sur les ids -> find() -> setApprouve(true).
     *        Pour de très gros volumes, préférer une DQL UPDATE en masse.
     */
    public function approveBatchAction(EntityManagerInterface $em)
    {
        // Récupère la liste des IDs cochés dans l’index EA (paramètre 'entityId')
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

    /**
     * Action globale : retirer l’approbation pour tous les éléments sélectionnés.
     */
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

    /**
     * Petit helper pour construire une URL EA pointant vers CE contrôleur.
     * [DX] Évite de répéter la construction dans chaque action custom.
     */
    private function adminUrlGenerator()
    {
        return $this->container
            ->get(\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator::class)
            ->setController(self::class);
    }
}

