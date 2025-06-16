<?php

namespace App\Tests\Controller;

use App\Entity\Space;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SpaceControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testListSpaces(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        $this->client->request('GET', '/api/spaces');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testCreateSpaceAsAdmin(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        $this->client->request('POST', '/api/spaces', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Test Room',
            'description' => 'Description test',
            'capacity' => 20,
            'type' => 'Sala',
            'availableFrom' => '08:00',
            'availableTo' => '17:00',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
    }

    public function testShowSpace(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        $space = $this->createTestSpace();

        $this->client->request('GET', '/api/spaces/' . $space->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testUpdateSpace(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        $space = $this->createTestSpace();

        $this->client->request('PUT', '/api/spaces/' . $space->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Room',
            'capacity' => 30,
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'updated']),
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeleteSpace(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        $space = $this->createTestSpace();

        $this->client->request('DELETE', '/api/spaces/' . $space->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'deleted']),
            $this->client->getResponse()->getContent()
        );
    }

    private function createTestSpace(): Space
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $space = new Space();
        $space->setName('Test');
        $space->setDescription('Desc');
        $space->setCapacity(10);
        $space->setType('Sala');
        $space->setPhotoUrl(null);
        $space->setAvailableFrom(new \DateTime('08:00'));
        $space->setAvailableTo(new \DateTime('17:00'));

        $em->persist($space);
        $em->flush();

        return $space;
    }

    private function createAdminUser(): User
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userRepo = $em->getRepository(User::class);

        $user = $userRepo->findOneBy(['email' => 'admin@test.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('admin@test.com');
            $user->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_ADMIN']);

            $em->persist($user);
            $em->flush();
        }

        return $user;
    }

    private function loginAsAdmin(): string
    {
        $user = $this->createAdminUser();

        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        return $jwtManager->create($user);
    }
}
