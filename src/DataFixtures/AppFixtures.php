<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 9; ++$i) {
            $user = new User();
            $user->setCompagnyName('Compagny n°'.$i)
                ->setContactName('name of compagny n°'.$i)
                ->setEmail('emailofcompagny'.$i.'@email.com')
            ;
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));

            for ($j = 1; $j <= 8; ++$j) {
                $customer = new Customer();
                $customer->setFirstName('FirstName'.$i.$j)
                    ->setLastName('LastName'.$i.$j)
                    ->setEmail('emailofcustomer'.$i.$j.'@email.com')
                    ->setUser($user)
                ;
                $manager->persist($customer);
            }
            $manager->persist($user);
        }
        for ($k = 1; $k <= 10; ++$k) {
            $phone = new Phone();
            $phone->setBrand('brand'.$k)
                ->setModel('model n°'.$k)
                ->setDescription('description of the phone n°'.$k)
                ->setColor('color the phone°'.$k)
                ->setHeight(11 + 0.3 * $k)
                ->setLenght(5 + 0.2 * $k)
                ->setThickness(0.4 + 0.1 * $k)
                ->setPrice(500 + 20 * $k)
            ;

            $manager->persist($phone);
        }
        $manager->flush();
    }
}
