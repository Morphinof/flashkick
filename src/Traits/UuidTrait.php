<?php

declare(strict_types=1);

namespace Flashkick\Traits;

use Exception;
use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * Warning ! This trait must be included before EntityTrait (https://github.com/ramsey/uuid/issues/215)
 */
trait UuidTrait
{
    /**
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private UuidInterface $uuid;

    /**
     * Auto-create uuid
     *
     * @ORM\PrePersist
     *
     * @throws Exception
     */
    public function autoUpdateUuid(): void
    {
        if ($this->uuid === null) {
            $this->uuid = Uuid::uuid4();
        }
    }

    public function getUuid(): string
    {
        return (string)$this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = Uuid::fromString($uuid);
    }
}