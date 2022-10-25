<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setCompagnyName('Compagny n°'. $i)
                ->setContactName('name of compagny n°' . $i)
                ->setEmail('emailofcompagny' . $i . "@email.com");

            for($j = 1; $j <= 10; $j++){
                $customer = new Customer();
                $customer->setFirstName('FirstName' .$i. $j)
                    ->setLastName('LastName' .$i. $j)
                    ->setEmail('emailofcustomer' . $i . $j . "@email.com")
                    ->setUsers($user);
                $manager->persist($customer);

        }
            $manager->persist($user);
        }
        for ($i = 1; $i <= 10; $i++) {
            $phone = new Phone();
            $phone->setBrand("brand" . $i)
                ->setModel('model n°' . $i)
                ->setDescription('description of the phone n°' . $i)
                ->setColor('color the phone°' . $i)
                ->setHeight( 9 + 0.3 * $i)
                ->setLenght(5 + 0.2 * $i)
                ->setThickness(0.4 + 0.1 * $i)
                ->setPrice( 500 + 20 * $i);

            $manager->persist($phone);

        }
        $manager->flush();
    }
}
