<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Task;
use App\Entity\User;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Filesystem\Filesystem;

class AppFixtures extends Fixture
{
    private $filesystem;

    public function __construct(private UserPasswordHasherInterface $passwordEncoder, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($i = 1; $i <= 10; $i++){
        $task = new Task();
        $uniqueTitle = $faker->unique()->word();
        $task->setTitle($uniqueTitle);
        $task->setContent($faker->text());
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setIsDone($faker->boolean());
        $manager->persist($task);
        $this->addReference('task'.$i, $task);
        }

        $user = new User();
        $username = 'anonyme';
        $user->setUsername($username);
        $user->setPassword(
            $this->passwordEncoder->hashPassword($user, $username)
        );
        $user->setEmail($faker->email());
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        for($j = 1; $j <= 5; $j++){
            $user = new User();
            $username = $faker->username;
            $user->setUsername($username);
            $user->setPassword(
                $this->passwordEncoder->hashPassword($user, $username)
            );
            $user->setEmail($faker->email());
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $this->addReference('user'.$j, $user);
            }

        $manager->flush();
    }
}
