<?php

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
    public function __construct(
        private ResetPasswordHelperInterface $resetHelper,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Tatoueur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tatoueur')
            ->setEntityLabelInPlural('Tatoueurs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Tatoueurs')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau tatoueur')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le tatoueur')
            ->setPaginatorPageSize(15)
            ->setSearchFields(['prenom', 'nom', 'pseudo', 'email']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Infos d’identité
        yield TextField::new('prenom', 'Prénom')->setFormTypeOption('attr.maxlength', 50);
        yield TextField::new('nom', 'Nom')->setFormTypeOption('attr.maxlength', 50);
        yield TextField::new('pseudo', 'Pseudo')
            ->setRequired(false)
            ->setFormTypeOption('attr.placeholder', '(optionnel)')
            ->setFormTypeOption('attr.maxlength', 50);

        // Email pro (on le recopie sur le compte User au persist/update)
        yield EmailField::new('email', 'Email')->setFormTypeOption('attr.maxlength', 255);

        // Entreprise
        yield AssociationField::new('entreprise', 'Entreprise')
            ->autocomplete()
            ->setRequired(true);

        // Sous-formulaire Compte (User) : email + rôles (pas de mot de passe ici)
        yield Field::new('compte', 'Compte')
            ->setFormType(TatoueurUserType::class)
            ->setFormTypeOption('property_path', 'user') // ← mappe le champ "compte" vers la propriété "user"
            ->onlyOnForms();
    }

    /** Prépare un User par défaut lors du "Nouveau" */
    public function createEntity(string $entityFqcn)
    {
        $t = new Tatoueur();
        // Si tu veux autoriser la création de la fiche sans compte, commente la suite :
        if (null === $t->getUser()) {
            $u = (new User())->setRoles(['ROLE_USER']);
            $t->setUser($u);
        }
        return $t;
    }

    /** Création : synchronise User + envoie l’invitation */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Tatoueur) {
            $this->prepareAndSyncUser($entityInstance);
            $this->applyRolesFromSubform($entityInstance);

            parent::persistEntity($em, $entityInstance);

            // Invitation (définition du mot de passe)
            $this->sendInviteEmail($entityInstance->getUser());
            $this->addFlash('success', 'Invitation envoyée au tatoueur pour définir son mot de passe.');
            return;
        }

        parent::persistEntity($em, $entityInstance);
    }

    /** Édition : synchronise User (email/roles). Pas d’email automatique ici. */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Tatoueur) {
            $this->prepareAndSyncUser($entityInstance);
            $this->applyRolesFromSubform($entityInstance);
        }

        parent::updateEntity($em, $entityInstance);
    }

    /**
     * - Crée un User s’il n’existe pas
     * - Recopie l’email pro vers l’email de connexion
     * - Garantit ROLE_USER
     */
    private function prepareAndSyncUser(Tatoueur $t): void
    {
        $u = $t->getUser();
        if (!$u) {
            $u = (new User())->setRoles(['ROLE_USER']);
            $t->setUser($u);
        }

        // synchro email pro -> email de connexion (si rempli)
        if ($t->getEmail() && $u->getEmail() !== $t->getEmail()) {
            $u->setEmail($t->getEmail());
        }

        // Toujours ROLE_USER
        if (!in_array('ROLE_USER', $u->getRoles(), true)) {
            $u->setRoles([...$u->getRoles(), 'ROLE_USER']);
        }
        // Si le User n’a pas de mot de passe (création), on en génère un temporaire
        if (!$u->getPassword()) {
            $random = bin2hex(random_bytes(12));
            $u->setPassword($this->passwordHasher->hashPassword($u, $random));
        }
    }

    /**
     * Récupère les rôles soumis dans le sous-formulaire "user"
     * (affiché/éditable uniquement pour les admins via TatoueurUserType)
     */
    private function applyRolesFromSubform(Tatoueur $t): void
    {
        $data = $this->getContext()->getRequest()->request->all();
        $root = $data['Tatoueur'] ?? null;
        if (!is_array($root) || !isset($root['user'])) return;

        $payload = $root['user'];
        if (isset($payload['roles']) && is_array($payload['roles']) && $this->isGranted('ROLE_ADMIN')) {
            $roles = array_values(array_unique(array_map('strtoupper', $payload['roles'])));
            if (!in_array('ROLE_USER', $roles, true)) $roles[] = 'ROLE_USER';
            $t->getUser()->setRoles($roles);
        }
    }

    /** Envoie l’email d’invitation (ResetPasswordBundle) */
    private function sendInviteEmail(User $user): void
    {
        // Génère le token reset
        $resetToken = $this->resetHelper->generateResetToken($user);

        // Lien direct vers la page "définir le mot de passe"
        // (route générée par make:reset-password — adapte si tu as un nom différent)
        $resetWithTokenUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $resetToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Lien login (dashboard)
        $loginUrl = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Définis ton mot de passe — Accès au Dashboard')
            ->htmlTemplate('emails/invite_set_password.html.twig')
            ->context([
                'resetWithTokenUrl' => $resetWithTokenUrl,
                'loginUrl'          => $loginUrl,
                'user'              => $user,
            ]);

        $this->mailer->send($email);
    }
}
