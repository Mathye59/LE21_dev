<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Tatoueur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsCommand(
    name: 'app:migrate:tatoueurs-to-users',
    description: 'Crée un User pour chaque Tatoueur (email identique) et envoie un lien de définition de mot de passe.',
)]
class MigrateTatoueursToUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private ResetPasswordHelperInterface $resetHelper,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repoTat = $this->em->getRepository(Tatoueur::class);
        $repoUser = $this->em->getRepository(User::class);

        /** @var Tatoueur[] $tatoueurs */
        $tatoueurs = $repoTat->findAll();

        $created = 0; $linked = 0; $skipped = 0;
        foreach ($tatoueurs as $t) {
            $email = trim((string)$t->getEmail());
            if ($email === '') { $io->warning("Tatoueur #{$t->getId()} sans email — ignoré"); $skipped++; continue; }

            // si déjà lié à un User, on passe
            if ($t->getUser() instanceof User) { $linked++; continue; }

            // vérifie collision email côté User
            $existingUser = $repoUser->findOneBy(['email' => $email]);
            if ($existingUser) {
                // si un compte existe déjà avec cet email, on lie
                $t->setUser($existingUser);
                $linked++;
                continue;
            }

            // crée un compte
            $u = new User();
            $u->setEmail($email);
            $u->setRoles(['ROLE_USER']); // adapte si besoin
            // mot de passe aléatoire (sera remplacé par reset)
            $random = bin2hex(random_bytes(12));
            $u->setPassword($this->hasher->hashPassword($u, $random));

            $this->em->persist($u);
            $t->setUser($u);
            $created++;

            // envoi de l'invitation (reset password)
            $this->sendInviteEmail($u, $io);
        }

        $this->em->flush();
        $io->success("Créés: $created, liés: $linked, ignorés: $skipped");

        return Command::SUCCESS;
    }

    private function sendInviteEmail(User $user, SymfonyStyle $io): void
    {
        // token reset
        $resetToken = $this->resetHelper->generateResetToken($user);

        $resetWithTokenUrl = $this->urlGenerator->generate(
            'app_reset_password', // route générée par make:reset-password
            ['token' => $resetToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

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

        try {
            $this->mailer->send($email);
            $io->writeln("→ Invitation envoyée à {$user->getEmail()}");
        } catch (\Throwable $e) {
            $io->warning("Email NON envoyé à {$user->getEmail()} : ".$e->getMessage());
        }
    }
}
