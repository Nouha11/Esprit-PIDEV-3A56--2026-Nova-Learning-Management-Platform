<?php

namespace App\Entity\users;

use App\Repository\UserSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
#[ORM\Table(name: 'user_sessions')]
#[ORM\Index(columns: ['user_id', 'is_active'], name: 'idx_user_active')]
#[ORM\Index(columns: ['session_token'], name: 'idx_session_token')]
#[ORM\Index(columns: ['last_activity'], name: 'idx_last_activity')]
class UserSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $sessionToken = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $browser = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $platform = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $device = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastActivity = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column]
    private ?bool $isCurrent = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lastActivity = new \DateTime();
        $this->sessionToken = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSessionToken(): ?string
    {
        return $this->sessionToken;
    }

    public function setSessionToken(string $sessionToken): static
    {
        $this->sessionToken = $sessionToken;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): static
    {
        $this->browser = $browser;
        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(?string $platform): static
    {
        $this->platform = $platform;
        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): static
    {
        $this->device = $device;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
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

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(\DateTimeInterface $lastActivity): static
    {
        $this->lastActivity = $lastActivity;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;
        return $this;
    }

    public function updateActivity(): static
    {
        $this->lastActivity = new \DateTime();
        return $this;
    }
}
