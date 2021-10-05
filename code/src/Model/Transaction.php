<?php

namespace App\Model;

use DateTime;

class Transaction
{
    private $id;
    private $title;
    private $amount;
    private $createdAt;

    public function __construct(?int $id, string $title, float $amount, DateTime $createdAt = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->amount = $amount;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function createdAt(): ?DateTime
    {
        return $this->createdAt;
    }
}
