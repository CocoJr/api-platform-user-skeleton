<?php

namespace App\DataFixtures\Processor;

use Fidry\AliceDataFixtures\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserProcessor implements ProcessorInterface
{
    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function preProcess(string $id, $object): void
    {
        if (!$object instanceof User) {
            return;
        }

        $password = $this->encoder->encodePassword($object, $object->getPassword());
        $object->setPassword($password);
    }

    public function postProcess(string $id, $object): void
    {

    }
}