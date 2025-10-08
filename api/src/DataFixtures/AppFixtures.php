<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Admin temporaire
        $admin = new User();
        $admin->setEmail('lherbiermanon@gmail.com');
        $admin->setRoles(['ROLE_USER','ROLE_ADMIN']); // ROLE_USER sera garanti par getRoles() si tu l'as codé ainsi
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin1234!')); // mdp connu pour tests
        $manager->persist($admin);

        $manager->flush();
    }
}
