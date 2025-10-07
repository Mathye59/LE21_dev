<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:promote',
    description: 'Ajoute ROLE_ADMIN à un utilisateur (email).'
)]
class UserPromoteCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email du user à promouvoir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = trim((string)$input->getArgument('email'));

        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $io->error("Aucun user avec l'email: {$email}");
            return Command::FAILURE;
        }

        $roles = array_map('strtoupper', $user->getRoles());
        if (!in_array('ROLE_USER', $roles, true)) $roles[] = 'ROLE_USER';
        if (!in_array('ROLE_ADMIN', $roles, true)) $roles[] = 'ROLE_ADMIN';
        $user->setRoles(array_values(array_unique($roles)));

        $this->em->flush();

        $io->success("{$email} promu avec succès (roles: ".implode(', ', $user->getRoles()).')');
        return Command::SUCCESS;
    }
}
