<?php

namespace App\DataFixtures;

use App\Entity\Space;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SpaceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $spaces = [
            [
                'name' => 'Sala de Reuniones A',
                'description' => 'Espacio moderno para reuniones pequeñas.',
                'capacity' => 8,
                'photoUrl' => 'https://via.placeholder.com/400x200?text=Sala+A',
                'from' => '08:00',
                'to' => '18:00',
            ],
            [
                'name' => 'Auditorio Central',
                'description' => 'Auditorio para presentaciones y eventos grandes.',
                'capacity' => 100,
                'photoUrl' => 'https://via.placeholder.com/400x200?text=Auditorio',
                'from' => '09:00',
                'to' => '20:00',
            ],
            [
                'name' => 'Sala Creativa',
                'description' => 'Ambiente cómodo para sesiones de brainstorming.',
                'capacity' => 12,
                'photoUrl' => 'https://via.placeholder.com/400x200?text=Creativa',
                'from' => '10:00',
                'to' => '17:00',
            ],
        ];

        foreach ($spaces as $data) {
            $space = new Space();
            $space->setName($data['name']);
            $space->setDescription($data['description']);
            $space->setCapacity($data['capacity']);
            $space->setPhotoUrl($data['photoUrl']);
            $space->setAvailableFrom(new \DateTime($data['from']));
            $space->setAvailableTo(new \DateTime($data['to']));
            $manager->persist($space);
        }

        $manager->flush();
    }
}
