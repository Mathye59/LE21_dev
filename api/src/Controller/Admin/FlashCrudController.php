<?php

namespace App\Controller\Admin;

use App\Entity\Flash;                            // Entité gérée par ce CRUD
use App\Entity\Categorie;                        // Utilisée via AssociationField (M2M)
use App\Entity\Tatoueur;                         // Utilisée via AssociationField (M2O)

use App\Controller\Admin\CategorieCrudController;// Pour ouvrir la modale EA sur Catégorie
use App\Enum\StatutFlash;                        // Enum métier (DISPONIBLE, RESERVE, INDISPONIBLE)
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;   // Config des pages EA (INDEX/NEW/EDIT/DETAIL)
use EasyCorp\Bundle\EasyAdminBundle\Config\Action; // Représentation d’une action EA
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;// Builder des actions
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField; // Champs relationnels
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;      // Sélecteur pour Enum / listes
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;        // Champs texte simple
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;       // Affichage d’images (lecture seule)
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;  // (non utilisé ici) pour textarea riche
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;    // Pour updatedAt en index
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;            // Champ générique (pour Vich)
use Symfony\Component\Form\Extension\Core\Type\EnumType;    // Rendu formulaire pour backed enums PHP 8.1+
use Symfony\Component\Validator\Constraints\Length;         // (pas utilisé ici) — validations possibles
use Vich\UploaderBundle\Form\Type\VichImageType;            // Champ d’upload Vich (UploadableField)

/**
 * ==========================================================
 *  FlashCrudController (EasyAdmin)
 * ----------------------------------------------------------
 *  Rôle :
 *   - Back-office de l’entité "Flash" (dessins disponibles).
 *   - Edition des métadonnées (temps estimé, statut), relations (catégories, tatoueur),
 *     et image (upload Vich + aperçu).
 *
 *  Hypothèses côté modèle :
 *   - Flash possède :
 *       * string|null $temps
 *       * StatutFlash $statut (backed enum)
 *       * ManyToMany Categorie $categories
 *       * ManyToOne Tatoueur $tatoueur
 *       * Vich fields : imageFile (UploadedFile) + imageName (string)
 *       * DateTimeImmutable|null $updatedAt (si utilisé)
 *   - Mapping Vich "flashes" avec uri_prefix /uploads/flashes.
 *
 *  Points d’attention :
 *   - [ENUM] ChoiceField + EnumType pour sélectionner l’Enum en form, + badges en index.
 *   - [RELATION] M2M Catégories : 'by_reference' => false pour déclencher add/remove*.
 *   - [UPLOAD] VichImageType sur imageFile, requis à la création, optionnel à l’édition.
 *   - [UX] Aperçu image via ImageField (imageName) en index/détail (lecture seule).
 *   - [PERF] Tri par tatoueur.nom + statut ; pagination 15.
 * ==========================================================
 */
class FlashCrudController extends AbstractCrudController
{
    /** FQCN de l’entité gérée (indique à EA la cible du CRUD). */
    public static function getEntityFqcn(): string
    {
        return Flash::class;
    }

    /**
     * Configuration globale des pages :
     * - labels/titres
     * - tri par défaut (tatoueur.nom, puis statut)
     * - pagination
     * [UX] Cohérence avec les autres CRUD : 15 par page.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Libellés & titres homogènes
            ->setEntityLabelInSingular('Flash')
            ->setEntityLabelInPlural('Flashes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Flashes')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau flash')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le flash')

            // Tri par défaut : 1) par nom de tatoueur (ASC) 2) par statut (ASC)
            // [REMARQUE] Assure-toi que la jointure nécessaire est réalisée par EA
            // ou surcharge createIndexQueryBuilder si besoin d’un tri stable/perf.
            ->setDefaultSort(['tatoueur.nom' => 'ASC', 'statut' => 'ASC'])

            // Pagination
            ->setPaginatorPageSize(15);
            // ->setEntityPermission('ROLE_ADMIN'); // [SECURITY] option : restreindre au rôle
    }

    /**
     * Déclaration des champs selon la page (INDEX/NEW/EDIT/DETAIL).
     * [DX] On branche Vich sur 'imageFile' (form) et on affiche 'imageName' (index).
     */
    public function configureFields(string $pageName): iterable
    {
        // Petit helper : savoir si on est sur la page de création (NEW)
        $isNew = $pageName === Crud::PAGE_NEW;

        // ---------------------------------------------------------------------
        // Champs texte simples
        // ---------------------------------------------------------------------

        // "Temps estimé" — libre, non requis (nullable).
        yield TextField::new('temps', 'Temps estimé')
            ->setHelp('Exemple: "2h", "3h30"…')
            ->setMaxLength(50); // [UX] limite UI ; prévoir Assert\Length côté entité

        // ---------------------------------------------------------------------
        // Statut (Enum) — rendu en select et en badges colorés en index
        // ---------------------------------------------------------------------
        yield ChoiceField::new('statut', 'Statut')
            // [ENUM] mapping affichage -> valeur (backed enum). EA sait manipuler l’Enum avec EnumType.
            ->setChoices([
                'Disponible'   => StatutFlash::DISPONIBLE,
                'Réservé'      => StatutFlash::RESERVE,
                'Indisponible' => StatutFlash::INDISPONIBLE,
            ])
            // [FORM] Utilise EnumType pour un <select> lié à la backed enum PHP.
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => StatutFlash::class,
            ])
            // [UX] Rendu badge en index/détail (couleurs sémantiques)
            ->renderAsBadges([
                'Disponible'   => 'success', // vert
                'Réservé'      => 'warning', // jaune/orange
                'Indisponible' => 'danger',  // rouge
            ])
            // [ROBUSTESSE] Harmonise l’affichage en index, que la valeur soit enum|string|null
            ->formatValue(static function ($value): string {
                if ($value instanceof StatutFlash) {
                    return match ($value) {
                        StatutFlash::DISPONIBLE   => 'Disponible',
                        StatutFlash::RESERVE      => 'Réservé',
                        StatutFlash::INDISPONIBLE => 'Indisponible',
                    };
                }
                if (\is_string($value)) {
                    // Si Doctrine hydrate la colonne sous forme de string (selon mapping)
                    return match ($value) {
                        'DISPONIBLE'   => 'Disponible',
                        'RESERVE'      => 'Réservé',
                        'INDISPONIBLE' => 'Indisponible',
                        default        => $value,
                    };
                }
                return '';
            });

        // ---------------------------------------------------------------------
        // Associations (Catégories, Tatoueur)
        // ---------------------------------------------------------------------

        // Catégories : ManyToMany, multi-sélection, création possible via modale Catégorie
        yield AssociationField::new('categories', 'Catégories')
            ->setCrudController(CategorieCrudController::class) // ouvre la modale du CRUD Catégorie
            ->setFormTypeOptions([
                'by_reference' => false, // [M2M] oblige l’usage des add/remove* (sinon set() sur la collection)
                'multiple'     => true,  // multi-select
            ])
            ->autocomplete()             // [UX] utile si beaucoup de catégories
            ->setRequired(true)
            ->onlyOnForms();             // [UX] pas d’intérêt hors formulaire

        // Tatoueur : ManyToOne — on ne crée pas depuis ici (pas de setCrudController)
        yield AssociationField::new('tatoueur', 'Tatoueur')
            ->autocomplete()
            ->setRequired(true);

        // ---------------------------------------------------------------------
        // Image (Vich) : upload + aperçu
        // ---------------------------------------------------------------------

        // Champ FORM : upload via Vich sur la propriété "imageFile" de Flash
        yield Field::new('imageFile', 'Image')
            ->setFormType(VichImageType::class) // [UPLOAD] typé Vich
            ->setFormTypeOptions([
                'required'     => $isNew, // requis à la création, optionnel en édition
                'allow_delete' => false,  // pas de case "supprimer" (selon ta politique)
                // 'download_uri' => false, // [UX] option : masquer lien de téléchargement
            ])
            ->onlyOnForms();

        // Champ INDEX/DETAIL : aperçu de l’image, basé sur "imageName" persisté
        yield ImageField::new('imageName', 'Aperçu')
            ->setBasePath('/uploads/flashes') // [ASSET] doit matcher uri_prefix du mapping Vich
            ->hideOnForm();                   // [UX] lecture seule

        // (Optionnel) Afficher la dernière MAJ en index (si l’entité l’expose)
        yield DateTimeField::new('updatedAt', 'MAJ')
            ->onlyOnIndex(); // [UX] info de suivi ; enlève si non pertinente
    }

    /**
     * Personnalisation des actions (libellés + icônes).
     * [UX] Cohérence des libellés avec le reste de l’admin.
     * [SECURITY] Par défaut, EA exécute en GET ; pour des mutations sensibles,
     *            préférer POST + CSRF (setHtmlAttributes(['data-action' => 'post'])).
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // INDEX : bouton "Nouveau flash"
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) =>
                $a->setLabel('Nouveau flash')->setIcon('fa fa-plus')
            )
            // NEW : boutons d’enregistrement
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn(Action $a) =>
                $a->setLabel('Enregistrer et ajouter un autre')->setIcon('fa fa-plus-circle')
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
}
