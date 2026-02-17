<?php

declare(strict_types=1);

namespace Tests\Unit\Cursor;

use ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldResolver;
use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\UuidV7;
use Symfony\Component\VarExporter\ProxyHelper;
use Tests\Fixtures\Doctrine\DoctrineTestHelper;
use Tests\Fixtures\Entity\Article;
use Tests\Fixtures\Entity\Book;
use Tests\Fixtures\Entity\Post;

final class CursorFieldResolverTest extends TestCase
{
    private function skipIfLazyGhostUnavailable(): void
    {
        if (PHP_VERSION_ID < 80400 && (!\class_exists(ProxyHelper::class) || !\method_exists(ProxyHelper::class, 'generateLazyGhost'))) {
            $this->markTestSkipped('symfony/var-exporter is required for Doctrine lazy ghosts.');
        }
    }

    public function testResolveUlidEntity(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManagerWithUlid();
        $qb = $em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a');

        $resolver = new CursorFieldResolver();
        $result = $resolver->resolve($qb);

        $this->assertSame('a.id', $result['cursor_field']);
        $this->assertInstanceOf(\Closure::class, $result['cursor_getter']);

        $ulid = new Ulid('01000000000000000000000001');
        $article = new Article('Test', $ulid);
        $this->assertSame($ulid, $result['cursor_getter']($article));
    }

    public function testResolveUuidEntity(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManagerWithUuid();
        $qb = $em->createQueryBuilder()
            ->select('p')
            ->from(Post::class, 'p');

        $resolver = new CursorFieldResolver();
        $result = $resolver->resolve($qb);

        $this->assertSame('p.id', $result['cursor_field']);
        $this->assertInstanceOf(\Closure::class, $result['cursor_getter']);

        $uuid = new UuidV7();
        $post = new Post('Test', $uuid);
        $this->assertSame($uuid, $result['cursor_getter']($post));
    }

    public function testThrowsForNonUlidIdentifier(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b');

        $resolver = new CursorFieldResolver();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cursor pagination requires a time-sortable identifier');
        $resolver->resolve($qb);
    }
}
