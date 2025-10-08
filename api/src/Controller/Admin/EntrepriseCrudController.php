<?php
/**
 * ==========================================================
 *  EntrepriseCrudController (EasyAdmin)
 *  ----------------------------------------------------------
 *  Rôle :
 *   - Définir l'interface CRUD back-office (EasyAdmin) pour l'entité Entreprise.
 *   - Configurer libellés, titres, tri, pagination et champs (dont upload logo via VichUploader).
 *
 *  Contexte :
 *   - Utilise EasyAdmin v4 (AbstractCrudController, Crud, Actions, Field*)
 *   - Upload géré par VichUploaderBundle (champ "logoFile" mappé à "logoName" côté entité)
 *
 *  Hypothèses sur l'entité Entreprise (à vérifier dans App\Entity\Entreprise) :
 *   - Propriétés : nom (string), adresse (text), telephone (string recommandé), email (string),
 *                  facebook (string|null), instagram (string|null),
 *                  horairesOuverture (string|null), horairesFermeture (string|null), horairePlus (string|null),
 *                  logoFile (File|null, non mappé Doctrine, pour Vich), logoName (string|null, mappé Doctrine).
 *
 *  Points d'attention :
 *   - [DB] Téléphone : éviter float (perte de zéros, formatage international). Préférer string.
 *   - [UPLOAD] Vich : s’assurer du mapping (ex: "company_logo") dans vich_uploader.yaml et des getters/setters.
 *   - [SECURITY] Vérifier les rôles sur le Dashboard/CRUD (ROLE_ADMIN), config dans DashboardController et security.yaml.
 *   - [UX] Aider l’admin avec des labels clairs, help-text, hideOnIndex sur les champs verbeux/moins utiles en liste.
 *   - [ASSET] BasePath du logo : doit pointer vers le répertoire public où Vich/Storage écrit les fichiers.
 * ==========================================================
 */

namespace App\Controller\Admin;

use App\Entity\Entreprise;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

// === Champs EasyAdmin (types de Field) =======================================
// TextField, TextEditorField, EmailField, UrlField, ImageField, Field générique
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

// === VichUploader : type de champ de formulaire pour l’upload ================
use Vich\UploaderBundle\Form\Type\VichFileType;

class EntrepriseCrudController extends AbstractCrudController
{
    /**
     * FQCN (= nom de classe complet) de l'entité gérée par ce CRUD.
     * EasyAdmin l’utilise pour générer les formulaires, requêtes, etc.
     */
    public static function getEntityFqcn(): string
    {
        return Entreprise::class;
    }

    /**
     * Configuration "globale" du CRUD (libellés, titres, pagination, tri par défaut).
     * @param Crud $crud l'objet de config EasyAdmin
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Libellés utilisés dans l’UI d’EA. Ici singulier/pluriel identiques (car une seule entreprise ?)
            ->setEntityLabelInPlural('Entreprise')
            ->setEntityLabelInSingular('Entreprise')

            // Titres des pages (INDEX/LISTE, NEW, EDIT). Personnalisables selon ta charte.
            ->setPageTitle(Crud::PAGE_INDEX, 'Entreprise')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer l’entreprise')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l’entreprise')

            // Pagination : 15 éléments/page (suffisant si 1 enregistrement ; neutre sinon)
            // [UX] Si tu n'auras qu’un seul enregistrement d’Entreprise, tu peux même monter à 50/100
            ->setPaginatorPageSize(15)

            // Tri par défaut sur l'ID descendant (le plus récent en premier).
            // [DX] Pratique si tu crées plusieurs "Entreprise" pendant les tests.
            ->setDefaultSort(['id' => 'DESC']);
    }

    /**
     * Personnalisation des actions (labels, visibilité, ordre, etc.).
     * Ici on renomme certains boutons pour coller à ta terminologie.
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        // [DX] Ne pas "add" une action déjà existante (sinon "already exists").
        // On utilise ->update() pour modifier l’existant proprement.
        return $actions
            // Page NEW : bouton "Save and Return" devient "Créer"
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn (Action $a) =>
                $a->setLabel('Créer'))

            // Page EDIT : on renomme "Save and Continue" pour clarifier le comportement
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $a) =>
                $a->setLabel('Enregistrer et continuer'))

            // Page EDIT : "Save and Return" renommé
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $a) =>
                $a->setLabel('Enregistrer et revenir'));
    }

    /**
     * Déclaration des champs affichés en fonction des pages (index/new/edit/detail).
     * EasyAdmin appelle cette méthode et "yield" permet de produire un iterable paresseux.
     *
     * @param string $pageName Nom de la page en cours (Crud::PAGE_INDEX/EDIT/NEW/DETAIL).
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        // ---------------------------------------------------------------------
        // --- Identité / Contact
        // ---------------------------------------------------------------------

        // Nom de l’entreprise (obligatoire côté entité si pertinent).
        // [UX] MaxLength limite la saisie front ; pense à valider aussi côté entité (Assert\Length).
        yield TextField::new('nom', 'Nom')
            ->setMaxLength(50);

        // Adresse postale : TextEditorField offre un textarea WYSIWYG simplifié (selon config).
        // [UX] Aide pour guider la saisie (rue, CP, ville).
        yield TextEditorField::new('adresse', 'Adresse')
            ->setHelp('Adresse postale complète');

        // Téléphone :
        // [UX] type TextField (pas Integer/Float) pour conserver zéros initiaux et formatage.
        
        yield TextField::new('telephone', 'Téléphone')
            ->setMaxLength(10)
            ->setHelp('Ex: 03 20 12 34 56 ');

        // Email : type dédié qui applique validations front (et d’éventuels widgets).
        // [SECURITY] Valider côté entité avec Assert\Email(strict).
        yield EmailField::new('email', 'Email');

        // ---------------------------------------------------------------------
        // --- Réseaux sociaux
        // ---------------------------------------------------------------------
        // [UX] hideOnIndex : masque en liste (INDEX) pour garder une liste compacte.
        // [SECURITY] Valider URL côté entité (Assert\Url) + normaliser (https obligatoire ?).
        yield UrlField::new('facebook', 'Facebook')->hideOnIndex()
            ->setHelp('URL complète (https://...)');

        yield UrlField::new('instagram', 'Instagram')->hideOnIndex()
            ->setHelp('URL complète (https://...)');

        // ---------------------------------------------------------------------
        // --- Horaires (affichage selon ta maquette)
        // ---------------------------------------------------------------------
        // [UX] Si tu veux améliorer : utiliser un TimeField (si stocké en \DateTimeInterface) +
        // un Value Object (ou normaliser format HH:mm). Ici tu gardes string pour la flexibilité.
        yield TextField::new('horairesOuverture', 'Heure d’ouverture')
            ->setHelp('Ex: 10h');

        yield TextField::new('horairesFermeture', 'Heure de fermeture')
            ->setHelp('Ex: 19h');

        // Mention additionnelle (ex: fermé le lundi, sur RDV, etc.). Inutile en INDEX -> hideOnIndex.
        yield TextField::new('horairePlus', 'Mention complémentaire')
            ->hideOnIndex()
            ->setHelp('Ex: Sur rendez-vous uniquement via contact');

        // ---------------------------------------------------------------------
        // --- Logo (Upload via VichUploader)
        // ---------------------------------------------------------------------
        // Champ "form only" : utilisé pour Uploader un nouveau fichier.
        // [UPLOAD] Ce champ DOIT correspondre à la propriété non mappée Doctrine "logoFile" dans l’entité,
        //          avec annotations/attributes Vich : @Vich\UploadableField(mapping="company_logo", fileNameProperty="logoName")
        // [SECURITY] Ajouter des contraintes MimeType/MaxSize (via Assert\File côté entité ou form options).
        yield Field::new('logoFile', 'Logo')
            ->setFormType(VichFileType::class) // on dit à EA d’utiliser le champ de formulaire Vich
            ->onlyOnForms()                    // n’apparaît QUE dans les formulaires (NEW/EDIT)
            ->setHelp('PNG/JPG • le fichier remplacera l’actuel');

        // Aperçu (READ ONLY) basé sur le nom de fichier persistant "logoName".
        // [ASSET] setBasePath doit pointer vers le dossier public où sont servis les fichiers uploadés.
        //         Exemple : public/uploads/company_logos (donc base path "/uploads/company_logos").
        // [UX] hideOnForm : affiché en INDEX et DETAIL, masqué en formulaire.
        yield ImageField::new('logoName', 'Aperçu logo')
            ->setBasePath('/uploads/company_logos')
            ->hideOnForm();
    }
}
