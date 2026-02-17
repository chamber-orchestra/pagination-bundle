<?php

declare(strict_types=1);

namespace Tests\Fixtures\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Ulid;
use Tests\Fixtures\Entity\Article;
use Tests\Fixtures\Entity\Book;
use Tests\Fixtures\Entity\Post;

final class DoctrineTestHelper
{
    public static function createEntityManager(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfig([
            __DIR__.'/../Entity',
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

    public static function createEntityManagerWithUlid(): EntityManager
    {
        if (!Type::hasType('ulid')) {
            Type::addType('ulid', UlidType::class);
        }

        $config = ORMSetup::createAttributeMetadataConfig([
            __DIR__.'/../Entity',
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
            $entityManager->getClassMetadata(Article::class),
        ]);

        return $entityManager;
    }

    public static function createEntityManagerWithUuid(): EntityManager
    {
        if (!Type::hasType('uuid')) {
            Type::addType('uuid', UuidType::class);
        }

        $config = ORMSetup::createAttributeMetadataConfig([
            __DIR__.'/../Entity',
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
            $entityManager->getClassMetadata(Post::class),
        ]);

        return $entityManager;
    }

    /**
     * @param list<array{title: string, id?: Ulid}> $articles
     */
    public static function seedArticles(EntityManager $entityManager, array $articles): void
    {
        foreach ($articles as $article) {
            $entityManager->persist(new Article($article['title'], $article['id'] ?? null));
        }

        $entityManager->flush();
    }
}
