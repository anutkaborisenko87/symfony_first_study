<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface
     */
    protected $password_encoder;

    public function __construct(UserPasswordHasherInterface $password_encoder)
    {
        $this->password_encoder =$password_encoder;

    }
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles]) {
            $user = new User();
            $user->setName($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setVimeoApiKey($api_key);
            $user->setPassword($this->password_encoder->hashPassword($user, $password));
            $user->setRoles($roles);
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
          ['Admin', 'Default', 'admin@example.ua', 'symfony_admin', 'hjd8dehdh', ['ROLE_ADMIN']],
          ['Anna', 'DefUser', 'anna@default.ua', 'symfony_anna_def', null, ['ROLE_USER']],
          ['Anna', 'SecondDefUser', 'anna_2user@default.ua', 'symfony_anna2user_def', null, ['ROLE_USER']]
        ];
    }
}
