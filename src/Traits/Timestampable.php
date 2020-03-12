<?php

declare(strict_types=1);

namespace Flashkick\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

trait Timestampable
{
    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTime $updatedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $deletedAt = null;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @throws Exception
     */
    public function autoTimestamp(): void
    {
        $this->updatedAt = new DateTime();

        if ($this->getCreatedAt() === null) {
            $this->createdAt = new DateTime();
        }
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(DateTime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}