<?php

declare(strict_types=1);

namespace Flashkick\DataFixtures\Processor;

use Fidry\AliceDataFixtures\ProcessorInterface;
use Flashkick\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserProcessor implements ProcessorInterface
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof User) {
            return;
        }

        $encoded = $this->encoder->encodePassword($object, $object->getPassword());

        $object->setPassword($encoded);
    }

    public function postProcess(string $id, $object): void
    {
    }
}