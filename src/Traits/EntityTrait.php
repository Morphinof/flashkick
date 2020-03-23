<?php

declare(strict_types=1);

namespace Flashkick\Traits;

use Doctrine\ORM\Mapping as ORM;

trait EntityTrait
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }
}