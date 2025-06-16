<?php

namespace App\Tests\Controller;

use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReservationControllerTest extends WebTestCase
{
    private $client;
    private $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = self::getContainer()->get('doctrine')->getManager();
    }

    public function testCreateReservation(): void
    {
        $this->client->loginUser($this->createTestUser());

        $space = new Space();
        $space->setName('Test Space');
        $space->setDescription('For testing');
        $space->setCapacity(10);
        $space->setType('Sala');
        $space->setAvailableFrom(new \DateTime('08:00'));
        $space->setAvailableTo(new \DateTime('18:00'));
        $this->em->persist($space);
        $this->em->flush();

        $payload = [
            'eventName' => 'Reserva Test',
            'startTime' => (new \DateTime('+1 hour'))->format('Y-m-d H:i:s'),
            'endTime' => (new \DateTime('+2 hour'))->format('Y-m-d H:i:s'),
            'spaceId' => $space->getId(),
        ];


        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testListUserReservations(): void
    {
        $user = $this->createTestUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/api/reservations');
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testDeleteReservation(): void
    {
        $user = $this->createTestUser();
        $reservation = $this->createReservation($user);

        $this->client->loginUser($user);
        $this->client->request('DELETE', '/api/reservations/' . $reservation->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('eliminada', $this->client->getResponse()->getContent());
    }

    private function createTestUser(): User
    {
        $repo = $this->em->getRepository(User::class);
        $existing = $repo->findOneBy(['email' => 'user@test.com']);
        if ($existing) {
            return $existing;
        }

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setPassword(password_hash('secret', PASSWORD_BCRYPT));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    private function createSpace(): Space
    {
        $space = new Space();
        $space->setName('Sala Test');
        $space->setDescription('Para pruebas');
        $space->setCapacity(10);
        $space->setType('Sala');
        $space->setAvailableFrom(new \DateTime('08:00'));
        $space->setAvailableTo(new \DateTime('18:00'));
        $this->em->persist($space);
        $this->em->flush();
        return $space;
    }

    private function createReservation(User $user): Reservation
    {
        $space = $this->createSpace();

        $reservation = new Reservation();
        $reservation->setEventName('Evento Prueba');
        $reservation->setStartTime(new \DateTime('+1 hour'));
        $reservation->setEndTime(new \DateTime('+2 hour'));
        $reservation->setSpace($space);
        $reservation->setUser($user);

        $this->em->persist($reservation);
        $this->em->flush();

        return $reservation;
    }
}
