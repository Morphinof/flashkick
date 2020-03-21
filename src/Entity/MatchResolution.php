<?php

namespace Flashkick\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Flashkick\Traits\EntityTrait;

/**
 * @ORM\Entity(repositoryClass="Flashkick\Repository\MatchResolutionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MatchResolution
{
    public const LOOSE = 0;
    public const WIN = 1;
    public const DRAW = 2;

    public const VALIDATIONS = [
        self::LOOSE,
        self::WIN,
        self::DRAW,
    ];

    use EntityTrait;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $validationP1;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $dateValidationP1;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $validationP2;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $dateValidationP2;

    public function getValidationP1(): ?int
    {
        return $this->validationP1;
    }

    public function setValidationP1(?int $validationP1): void
    {
        $this->dateValidationP1 = new DateTime();
        $this->validationP1 = $validationP1;
    }

    public function getDateValidationP1(): ?DateTime
    {
        return $this->dateValidationP1;
    }

    public function getValidationP2(): ?int
    {
        return $this->validationP2;
    }

    public function setValidationP2(?int $validationP2): void
    {
        $this->dateValidationP2 = new DateTime();
        $this->validationP2 = $validationP2;
    }

    public function getDateValidationP2(): ?DateTime
    {
        return $this->dateValidationP1;
    }

    public function isDraw(): bool
    {
        return $this->validationP1 !== null && $this->validationP1 === self::DRAW
            && $this->validationP2 !== null && $this->validationP2 === self::DRAW;
    }
}
