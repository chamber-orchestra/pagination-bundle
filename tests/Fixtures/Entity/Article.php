<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class Article
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    private Ulid $id;

    #[ORM\Column(type: 'string')]
    private string $title;

    public function __construct(string $title, ?Ulid $id = null)
    {
        $this->id = $id ?? new Ulid();
        $this->title = $title;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
