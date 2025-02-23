<?php

namespace App\Tests\Integration\DataPersister;

use ApiPlatform\Metadata\Post;
use App\DataPersister\UserDataPersister;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersisterTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?UserDataPersister $persister;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        $this->persister = new UserDataPersister($this->entityManager, $passwordHasher);
    }

    public function testPersistNewUser(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');

        $operation = new Post();

        // Act
        $result = $this->persister->process(
            $user,
            $operation,
            [],
            []
        );

        // Assert
        $this->assertNotNull($result);
        $this->assertNotEquals('password123', $result->getPassword());
        $this->assertEquals(['ROLE_USER'], $result->getRoles());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // On nettoie la base de données après chaque test
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}