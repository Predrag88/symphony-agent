<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: 'App\Repository\UserCurrencyRepository')]
#[ORM\Table(name: 'user_currency')]
class UserCurrency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private ?string $baseCurrency = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $selectedCryptos = null;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
    private ?string $userIp = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(string $baseCurrency): static
    {
        $this->baseCurrency = $baseCurrency;
        return $this;
    }

    public function getSelectedCryptos(): ?array
    {
        return $this->selectedCryptos;
    }

    public function setSelectedCryptos(?array $selectedCryptos): static
    {
        $this->selectedCryptos = $selectedCryptos;
        return $this;
    }

    public function getUserIp(): ?string
    {
        return $this->userIp;
    }

    public function setUserIp(?string $userIp): static
    {
        $this->userIp = $userIp;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}