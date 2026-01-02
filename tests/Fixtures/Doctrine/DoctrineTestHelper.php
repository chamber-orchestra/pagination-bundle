<?php

declare(strict_types=1);

namespace Tests\Fixtures\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Tests\Fixtures\Entity\Book;

final class DoctrineTestHelper
{
    public static function createEntityManager(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfig([
            __DIR__ . '/../Entity',
        ], true);
        if (PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $entityManager = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema([
            $entityManager->getClassMetadata(Book::class),
        ]);

        return $entityManager;
    }

    public static function seedBooks(EntityManager $entityManager, array $titles): void
    {
        foreach ($titles as $title) {
            $entityManager->persist(new Book($title));
        }

        $entityManager->flush();
    }
}
