<?php
/**
 * ==========================================================
 *  TatoueurCrudController (EasyAdmin)
 * ----------------------------------------------------------
 *  Objectif :
 *   - Gérer le CRUD EasyAdmin pour l'entité Tatoueur.
 *   - Synchroniser automatiquement un compte User associé :
 *       * création User si absent,
 *       * copie de l'email pro vers l'email de connexion,
 *       * garantie du rôle ROLE_USER,
 *       * génération d’un mot de passe temporaire hashé,
 *       * envoi d’un email d’invitation avec lien de définition de mot de passe (ResetPasswordBundle).
 *
 *  Hypothèses côté modèle :
 *   - Tatoueur possède une relation vers User (ex: OneToOne $user).
 *   - Tatoueur a des champs : prenom, nom, pseudo, email (pro), entreprise (ManyToOne ?), user.
 *   - Formulaire embarqué TatoueurUserType expose les rôles du User (réservé admin).
 *
 *  Points d’attention :
 *   - [SECURITY] Unicité de l’email sur User (UniqueEntity + index DB).
 *   - [DB] Cascade persist/merge sur relation Tatoueur <-> User pour éviter les "EntityManager persist" manuels.
 *   - [MAIL] Envoi de l’email d’invitation juste après le persist : prévoir gestion d’échec (try/catch + flash).
 *   - [DX] applyRolesFromSubform lit la requête brute : utile mais fragile si le nom du formulaire change.
 *   - [SECURITY] isGranted('ROLE_ADMIN') protège la saisie des rôles ; laisser ROLE_USER inchangé.
 *   - [UX] autocomplete() sur l’Association Entreprise pour un back-office fluide.
 * ==========================================================
 */

namespace App\Controller\Admin;

use App\Entity\Tatoueur;
use App\Entity\User;
use App\Form\TatoueurUserType;

use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class TatoueurCrudController extends AbstractCrudController
{
    /**
     * DI des services nécessaires :
     * - ResetPasswordHelperInterface : génération du token de réinitialisation (invitation).
     * - MailerInterface : envoi d’emails.
     * - UrlGeneratorInterface : fabrication d’URLs absolues vers les routes de reset/login.
     * - UserPasswordHasherInterface : hashing du mot de passe temporaire.
     *
     * [SECURITY] S’assurer que ces services sont correctement configurés (DSN Mailer, routes Reset).
     */
    public function __construct(
        private ResetPasswordHelperInterface $resetHelper,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /** FQCN de l’entité gérée par ce CRUD. */
    public static function getEntityFqcn(): string
    {
        return Tatoueur::class;
    }

    /**
     * Configuration globale du CRUD :
     * - libellés, titres page, pagination, champs de recherche.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tatoueur')
            ->setEntityLabelInPlural('Tatoueurs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Tatoueurs')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau tatoueur')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le tatoueur')
            ->setPaginatorPageSize(15)
            // [DX] searchFields facilite la recherche en back-office (INDEX).
            ->setSearchFields(['prenom', 'nom', 'pseudo', 'email']);
    }

    /**
     * Déclaration des champs d’édition/affichage.
     * [UX] MaxLength et placeholders aident l’admin ; prévoir en parallèle des constraints côté entité.
     */
    public function configureFields(string $pageName): iterable
    {
        // --- Identité ---
        yield TextField::new('prenom', 'Prénom')
            ->setFormTypeOption('attr.maxlength', 50);

        yield TextField::new('nom', 'Nom')
            ->setFormTypeOption('attr.maxlength', 50);

        yield TextField::new('pseudo', 'Pseudo')
            ->setRequired(false)
            ->setFormTypeOption('attr.placeholder', '(optionnel)')
            ->setFormTypeOption('attr.maxlength', 50);

        // --- Email pro (copié sur User.email au persist/update) ---
        // [SECURITY] Vérifier Assert\Email(strict=true) côté entité Tatoueur.
        yield EmailField::new('email', 'Email')
            ->setFormTypeOption('attr.maxlength', 255);

        // --- Relation Entreprise ---
        // [UX] autocomplete() pour confort si beaucoup d’entreprises.
        yield AssociationField::new('entreprise', 'Entreprise')
            ->autocomplete()
            ->setRequired(true);

        // --- Sous-formulaire "compte" (User) ---
        // [DX] property_path 'user' mappe le sous-formulaire vers la propriété Tatoueur::$user.
        // Avertissement : pas de mot de passe ici, uniquement email (sync) + rôles via TatoueurUserType.
        yield Field::new('compte', 'Compte')
            ->setFormType(TatoueurUserType::class)
            ->setFormTypeOption('property_path', 'user')
            ->onlyOnForms();
    }

    /**
     * Prépare une entité Tatoueur par défaut lors d’un "Nouveau".
     * - Si aucun User associé, on crée un User minimal avec ROLE_USER.
     *
     * [DX] Évite à l’admin d’oublier de créer le compte ; plus fluide au premier persist.
     * [DB] Si relation OneToOne, penser à cascade={"persist"} côté Tatoueur::$user.
     */
    public function createEntity(string $entityFqcn)
    {
        $t = new Tatoueur();

        // Option : autoriser une fiche sans compte → commenter le bloc ci-dessous.
        if (null === $t->getUser()) {
            $u = (new User())->setRoles(['ROLE_USER']);
            $t->setUser($u);
        }

        return $t;
    }

    /**
     * PERSIST (création) :
     * - Synchronise le User (email, rôles, mot de passe temporaire si besoin).
     * - Applique les rôles soumis dans le sous-formulaire (admin only).
     * - Persiste via parent, puis envoie l’email d’invitation.
     *
     * [MAIL] En cas d’échec d’envoi, prévoir try/catch pour informer l’admin sans bloquer le persist.
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Tatoueur) {
            $this->prepareAndSyncUser($entityInstance);
            $this->applyRolesFromSubform($entityInstance);

            parent::persistEntity($em, $entityInstance);

            // Envoi de l’invitation (définition du mot de passe via ResetPasswordBundle).
            $this->sendInviteEmail($entityInstance->getUser());
            $this->addFlash('success', 'Invitation envoyée au tatoueur pour définir son mot de passe.');
            return;
        }

        parent::persistEntity($em, $entityInstance);
    }

    /**
     * UPDATE (édition) :
     * - Resynchronise User (email / rôles).
     * - Pas d’email automatique ici (évite du bruit sur simples modifications).
     *
     * [DX] Si tu veux réinviter sur changement d’email, tu peux ajouter une détection et renvoyer un mail.
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Tatoueur) {
            $this->prepareAndSyncUser($entityInstance);
            $this->applyRolesFromSubform($entityInstance);
        }

        parent::updateEntity($em, $entityInstance);
    }

    /**
     * Synchronisation Tatoueur -> User :
     * - Crée un User si manquant (ROLE_USER par défaut).
     * - Copie l’email pro du Tatoueur vers User.email si fourni.
     * - Garantit le rôle ROLE_USER.
     * - Si pas de mot de passe défini, génère un mot de passe temporaire (hashé).
     *
     * [SECURITY] Le mot de passe temporaire n’est jamais affiché/loggé, seulement hashé.
     * [DB] S’assurer que User.email est unique (contrainte UniqueEntity + index unique DB).
     */
    private function prepareAndSyncUser(Tatoueur $t): void
    {
        $u = $t->getUser();
        if (!$u) {
            $u = (new User())->setRoles(['ROLE_USER']);
            $t->setUser($u);
        }

        // Synchronise l’email pro vers l’email de connexion du User, si changé/non vide.
        if ($t->getEmail() && $u->getEmail() !== $t->getEmail()) {
            $u->setEmail($t->getEmail());
        }

        // Assure la présence de ROLE_USER
        if (!in_array('ROLE_USER', $u->getRoles(), true)) {
            $u->setRoles([...$u->getRoles(), 'ROLE_USER']);
        }

        // Si aucun mot de passe n’est encore défini, on en crée un temporaire (hashé).
        // [SECURITY] random_bytes fournit une entropie forte ; rien n’est stocké en clair.
        if (!$u->getPassword()) {
            $random = bin2hex(random_bytes(12));
            $u->setPassword($this->passwordHasher->hashPassword($u, $random));
        }
    }

    /**
     * Applique les rôles soumis via le sous-formulaire embarqué (TatoueurUserType).
     * - Lecture de la requête (payload POST) à la racine du formulaire Tatoueur.
     * - Autorisé uniquement pour les admins.
     * - ROLE_USER est forcé même si omis.
     *
     * [SECURITY] Protection par isGranted('ROLE_ADMIN').
     * [DX] Sensible au nom du formulaire (clé 'Tatoueur'). Si tu le changes, mets-le à jour ici.
     * [ALTERNATIVE] Manipuler les rôles via le DataMapper/FormEvents pour éviter la dépendance à la request brute.
     */
    private function applyRolesFromSubform(Tatoueur $t): void
    {
        $data = $this->getContext()->getRequest()->request->all();
        $root = $data['Tatoueur'] ?? null; // <- nom du formulaire parent. Adapter si renommé.
        if (!is_array($root) || !isset($root['user'])) return;

        $payload = $root['user'];
        if (isset($payload['roles']) && is_array($payload['roles']) && $this->isGranted('ROLE_ADMIN')) {
            // Normalisation : uppercase + unique
            $roles = array_values(array_unique(array_map('strtoupper', $payload['roles'])));

            // Toujours ROLE_USER
            if (!in_array('ROLE_USER', $roles, true)) {
                $roles[] = 'ROLE_USER';
            }

            $t->getUser()->setRoles($roles);
        }
    }

    /**
     * Envoie l'email d’invitation au User pour définir son mot de passe.
     * - Génère un token via ResetPasswordHelper (valable n minutes/heures selon config).
     * - Fabrique un lien absolu vers la route app_reset_password avec le token.
     * - Passe aussi un lien de login dans le contexte du template.
     *
     * [MAIL] Nécessite un template Twig 'emails/invite_set_password.html.twig' adapté.
     * [SECURITY] Le token est à usage unique et expirera ; ne pas logguer le token.
     */
    private function sendInviteEmail(User $user): void
    {
        // Génère le token de reset (invit)
        $resetToken = $this->resetHelper->generateResetToken($user);

        // URL "définir le mot de passe" (route créée par make:reset-password ; adapter le nom si différent).
        $resetWithTokenUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $resetToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // URL de login (dashboard)
        $loginUrl = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Email templatisé (Twig) avec variables de contexte
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Définis ton mot de passe — Accès au Dashboard')
            ->htmlTemplate('emails/invite_set_password.html.twig')
            ->context([
                'resetWithTokenUrl' => $resetWithTokenUrl,
                'loginUrl'          => $loginUrl,
                'user'              => $user,
            ]);

        // Envoi
        $this->mailer->send($email);
    }
}
