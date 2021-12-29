<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\EventType;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $eventType = new EventType();
        $eventType->setName('login');
        $manager->persist($eventType);

        $eventType1 = new EventType();
        $eventType1->setName('register');
        $manager->persist($eventType1);

        $eventType2 = new EventType();
        $eventType2->setName('logout');
        $manager->persist($eventType2);

        $manager->flush();
    }
}
