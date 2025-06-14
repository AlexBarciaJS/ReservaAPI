<?php

namespace App\Tests\Controller;

use App\Entity\Space;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SpaceControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $spaceRepository;
    private string $path = '/space/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->spaceRepository = $this->manager->getRepository(Space::class);

        foreach ($this->spaceRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Space index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'space[name]' => 'Testing',
            'space[description]' => 'Testing',
            'space[capacity]' => 'Testing',
            'space[photoUrl]' => 'Testing',
            'space[availableFrom]' => 'Testing',
            'space[availableTo]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->spaceRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Space();
        $fixture->setName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setCapacity('My Title');
        $fixture->setPhotoUrl('My Title');
        $fixture->setAvailableFrom('My Title');
        $fixture->setAvailableTo('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Space');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Space();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setCapacity('Value');
        $fixture->setPhotoUrl('Value');
        $fixture->setAvailableFrom('Value');
        $fixture->setAvailableTo('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'space[name]' => 'Something New',
            'space[description]' => 'Something New',
            'space[capacity]' => 'Something New',
            'space[photoUrl]' => 'Something New',
            'space[availableFrom]' => 'Something New',
            'space[availableTo]' => 'Something New',
        ]);

        self::assertResponseRedirects('/space/');

        $fixture = $this->spaceRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getCapacity());
        self::assertSame('Something New', $fixture[0]->getPhotoUrl());
        self::assertSame('Something New', $fixture[0]->getAvailableFrom());
        self::assertSame('Something New', $fixture[0]->getAvailableTo());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Space();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setCapacity('Value');
        $fixture->setPhotoUrl('Value');
        $fixture->setAvailableFrom('Value');
        $fixture->setAvailableTo('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/space/');
        self::assertSame(0, $this->spaceRepository->count([]));
    }
}
