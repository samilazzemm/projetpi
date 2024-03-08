<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\String\Slugger\SluggerInterface;

class UsersFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder,
                                private SluggerInterface $slugger)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $admin = new User;
        $admin->setName('admin');
        $admin->setEmail('admin@admin.tn');
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordEncoder->hashPassword($admin ,'admin'));
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);
        
        $manager->flush();
    }
}
