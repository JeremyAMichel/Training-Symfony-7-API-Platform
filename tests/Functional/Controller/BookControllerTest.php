<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Book;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BookControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGetBooks(): void
    {
        // Act
        $this->client->request('GET', '/api/books');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    public function testCreateBookWithoutAuth(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Book',
            'author' => 'Test Author'
        ];

        // Act
        $this->client->request(
            'POST',
            '/api/books',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json'
            ],
            json_encode($data)
        );

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test si un utilisateur peut modifier un livre dont il est propriétaire
     */
    public function testEditOwnBook(): void
    {
        // 1. Préparation des données: création d'un utilisateur et d'un livre
        $user = new User();
        $user->setEmail('test-edit-book@example.com');
        $user->setPassword(
            $this->client->getContainer()
                ->get('security.user_password_hasher')
                ->hashPassword($user, 'password123')
        );
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);

        $book = new Book();
        $book->setTitle('Livre original');
        $book->setAuthor('Auteur test');
        $book->setUser($user); // Définit l'utilisateur comme propriétaire

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // 2. Génération du token JWT
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $this->client->getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);

        // 3. Préparation des données de modification
        $updatedData = [
            'title' => 'Titre modifié'
        ];

        // 4. Envoi de la requête PATCH avec le token JWT
        $this->client->request(
            'PATCH',
            '/api/books/' . $book->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/merge-patch+json',
                'HTTP_ACCEPT' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode($updatedData)
        );

        // 5. Vérification de la réponse
        $this->assertResponseIsSuccessful();

        // 6. Vérification que les données ont bien été modifiées en base
        $this->entityManager->clear();
        /** @var Book $modifiedBook */
        $modifiedBook = $this->entityManager
            ->getRepository(Book::class)
            ->find($book->getId());

        $this->assertEquals('Titre modifié', $modifiedBook->getTitle());
        $this->assertEquals('Auteur test', $modifiedBook->getAuthor());
    }
}
