<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * ==========================================================
 *  CategorieCrudController (EasyAdmin)
 * ----------------------------------------------------------
 *  Rôle :
 *   - CRUD back-office pour l’entité Categorie.
 *   - Index simple avec affichage du nombre de "flashes" liés.
 *
 *  Hypothèses côté modèle :
 *   - Categorie possède une relation (probable OneToMany) vers "Flash" (méthode getFlashes()).
 *   - getFlashes()->count() renvoie le nombre de flashes liés.
 *
 *  Points d’attention :
 *   - [PERF] count() sur une collection Doctrine peut déclencher une requête SQL
 *            par ligne si la relation n’est pas chargée en EXTRA_LAZY (ou si on n’agrège pas).
 *   - [UX] On affiche seulement le nom + compteur en INDEX pour garder une liste compacte.
 *   - [SECURITY] Restreindre l’accès à ce CRUD si nécessaire (ROLE_ADMIN).
 * ==========================================================
 */
class CategorieCrudController extends AbstractCrudController
{
    /**
     * FQCN (= nom de classe complet) de l’entité gérée.
     * EasyAdmin s’en sert pour générer formulaires & listes.
     */
    public static function getEntityFqcn(): string
    {
        return Categorie::class;
    }

    /**
     * Configuration globale du CRUD :
     * - libellés, titres de pages, champs de recherche, pagination.
     * - (Option) permission spécifique par rôle si besoin.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Libellés dans l’UI
            ->setEntityLabelInSingular('Catégorie')
            ->setEntityLabelInPlural('Catégories')

            // Titres des pages
            ->setPageTitle(Crud::PAGE_INDEX, 'Catégories')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle catégorie')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la catégorie')

            // Recherche côté admin sur le nom
            ->setSearchFields(['nom'])

            // Pagination : 15 lignes / page (cohérent avec tes autres CRUD)
            ->setPaginatorPageSize(15);

            // [SECURITY] Option : restreindre l’accès à un rôle
            // ->setEntityPermission('ROLE_ADMIN');
    }

    /**
     * Déclaration des champs affichés selon la page.
     * [UX] Liste épurée : nom + compteur calculé.
     * [DX] Utilise formatValue() pour convertir dynamiquement le compteur.
     */
    public function configureFields(string $pageName): iterable
    {
        // ---------------------------------------------------------------------
        // Champ "nom" : identifiant humain de la catégorie
        // [UX] MaxLength côté UI ; prévoir Assert\Length côté entité pour garantir en BDD.
        // ---------------------------------------------------------------------
        yield TextField::new('nom', 'Nom')
            ->setMaxLength(50);

        // ---------------------------------------------------------------------
        // Champ "Nb de flashes" : affiché uniquement en INDEX (lecture seule)
        // [PERF] formatValue() est appelé pour chaque ligne : getFlashes()->count()
        //        peut déclencher une requête SQL "SELECT COUNT(*)" si la collection
        //        est EXTRA_LAZY ; sinon Doctrine pourrait charger toute la collection.
        //        Voir les alternatives plus bas pour scaler.
        // ---------------------------------------------------------------------
        yield IntegerField::new('flashesCount', 'Nb de flashes')
            ->onlyOnIndex()
            ->formatValue(function ($value, Categorie $cat) {
                // [ROBUSTESSE] Si getFlashes() renvoie une PersistentCollection,
                // count() déclenchera normalement un COUNT(*) SQL (EXTRA_LAZY conseillé).
                // Sinon, selon la stratégie de fetch, ça peut charger la collection entière (à éviter).
                return $cat->getFlashes()->count();
            });

        // ---------------------------------------------------------------------
        // (Option) Si tu veux rendre le compteur triable/filtrable :
        // -> Nécessite de surcharger createIndexQueryBuilder() avec un COUNT agrégé
        //    + GROUP BY, puis exposer un champ triable basé sur l’alias SQL.
        //    Voir "ALTERNATIVE PERF" plus bas (commentée).
        // ---------------------------------------------------------------------
    }

    /**
     * Personnalisation des actions (libellés + icônes) :
     * - On NE rajoute pas d’actions existantes : on les "update".
     * - Homogénéité UI avec le reste de l’admin.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // INDEX : bouton "Nouvelle catégorie"
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) =>
                $a->setLabel('Nouvelle catégorie')->setIcon('fa fa-plus')
            )
            // NEW : boutons d’enregistrement
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn(Action $a) =>
                $a->setLabel('Enregistrer et ajouter une autre')->setIcon('fa fa-plus-circle')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn(Action $a) =>
                $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            )
            // EDIT : boutons d’enregistrement
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $a) =>
                $a->setLabel('Enregistrer et continuer')->setIcon('fa fa-save')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn(Action $a) =>
                $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            );
    }

    // =========================================================================
    // === ALTERNATIVES / NOTES D’OPTIMISATION (commentées, pour ton rapport) ===
    // =========================================================================

    /*
    // [PERF+] Alternative 1 : Rendre le compteur triable/scalable en INDEX
    // Principe :
    //  - On surcharge createIndexQueryBuilder() pour faire un COUNT agrégé des flashes.
    //  - On ajoute un champ virtuel triable en s’appuyant sur l’alias SQL.
    //  Avantage :
    //  - Une seule requête avec GROUP BY pour l’ensemble de la page.
    //  - Tri possible sur le nombre de flashes.
    //  Inconvénient :
    //  - Nécessite d’écrire le QB à la main et de gérer le mapping du champ triable.

    use Doctrine\ORM\QueryBuilder;

    public function createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // On repart de zéro si besoin, sinon on adapte le parent :
        // alias par défaut "entity" pour la table Categorie
        $qb
            ->leftJoin('entity.flashes', 'f')       // suppose relation "flashes" sur Categorie
            ->addSelect('COUNT(f.id) AS HIDDEN flashesCountAgg') // champ caché pour tri
            ->groupBy('entity.id');                 // groupement par catégorie

        // Pour autoriser le tri sur ce champ dans EA, il faut :
        // - Exposer un champ (e.g. TextField) avec ->setSortable(true)
        // - Et connecter le nom de la propriété au "flashesCountAgg"
        //   via un Field nommé comme une propriété "virtuelle" gérée par un DataTransformer
        //   ou via un dto custom. C’est plus avancé → mentionner dans le rapport si utile.

        return $qb;
    }
    */

    /*
    // [PERF+] Alternative 2 : Dénormaliser le compteur
    // Principe :
    //  - Ajouter une colonne "flashes_count" en BDD sur Categorie.
    //  - La tenir à jour via des listeners (postPersist/postRemove sur Flash) ou au niveau domaine.
    // Avantage :
    //  - Lecture instantanée, triable nativement.
    // Inconvénient :
    //  - Cohérence à maintenir (source de vérité répartie).
    */

    /*
    // [SECURITY] Alternative : restreindre l’accès au CRUD par rôle
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // ...
            ->setEntityPermission('ROLE_ADMIN');
    }
    */

    /*
    // [DX] Alternative : help-text, placeholders, layout
    // Tu peux enrichir le champ "nom" :
    yield TextField::new('nom', 'Nom')
        ->setHelp('Nom court et explicite, max 50 caractères.')
        ->setFormTypeOption('attr.placeholder', 'Ex : Géométrique');
    */
}

