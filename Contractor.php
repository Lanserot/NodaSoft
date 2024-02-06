<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * @property Seller $Seller
 */
class Contractor
{
    const TYPE_CUSTOMER = 0;
    const TYPE_NEW = 1;
    const TYPE_CHANGE = 2;
    private int $id;
    private int $type;
    private string $name;
    private string $email;

    public static function getById(int $id): self
    {
        return new self($id); // fakes the getById method
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }
}