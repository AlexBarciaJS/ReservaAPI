<?php

namespace App\Tests\Controller;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReservationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $reservationRepository;
    private string $path = '/reservation/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->reservationRepository = $this->manager->getRepository(Reservation::class);

        foreach ($this->reservationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Reservation index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'reservation[eventName]' => 'Testing',
            'reservation[startTime]' => 'Testing',
            'reservation[endTime]' => 'Testing',
            'reservation[userRelation]' => 'Testing',
            'reservation[spaceRelation]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->reservationRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Reservation();
        $fixture->setEventName('My Title');
        $fixture->setStartTime('My Title');
        $fixture->setEndTime('My Title');
        $fixture->setUserRelation('My Title');
        $fixture->setSpaceRelation('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Reservation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Reservation();
        $fixture->setEventName('Value');
        $fixture->setStartTime('Value');
        $fixture->setEndTime('Value');
        $fixture->setUserRelation('Value');
        $fixture->setSpaceRelation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'reservation[eventName]' => 'Something New',
            'reservation[startTime]' => 'Something New',
            'reservation[endTime]' => 'Something New',
            'reservation[userRelation]' => 'Something New',
            'reservation[spaceRelation]' => 'Something New',
        ]);

        self::assertResponseRedirects('/reservation/');

        $fixture = $this->reservationRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getEventName());
        self::assertSame('Something New', $fixture[0]->getStartTime());
        self::assertSame('Something New', $fixture[0]->getEndTime());
        self::assertSame('Something New', $fixture[0]->getUserRelation());
        self::assertSame('Something New', $fixture[0]->getSpaceRelation());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Reservation();
        $fixture->setEventName('Value');
        $fixture->setStartTime('Value');
        $fixture->setEndTime('Value');
        $fixture->setUserRelation('Value');
        $fixture->setSpaceRelation('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/reservation/');
        self::assertSame(0, $this->reservationRepository->count([]));
    }
}
