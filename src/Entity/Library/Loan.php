<?php

namespace App\Entity\Library;

use App\Entity\users\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'loans')]
class Loan
{
    // Statuts possibles pour un emprunt
    public const STATUS_PENDING = 'PENDING';      // En attente d'approbation
    public const STATUS_APPROVED = 'APPROVED';    // Approuvé par l'admin
    public const STATUS_REJECTED = 'REJECTED';    // Rejeté par l'admin
    public const STATUS_ACTIVE = 'ACTIVE';        // Livre récupéré par l'utilisateur
    public const STATUS_RETURNED = 'RETURNED';    // Livre retourné
    public const STATUS_OVERDUE = 'OVERDUE';      // En retard

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Book must be selected')]
    private ?Book $book = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'User must be selected')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Library::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Library $library = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'Loan start date cannot be empty')]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startAt', message: 'Loan end date must be after start date')]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Status is required')]
    #[Assert\Choice(
        choices: [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_ACTIVE, self::STATUS_RETURNED, self::STATUS_OVERDUE],
        message: 'Invalid loan status'
    )]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $requestedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getLibrary(): ?Library
    {
        return $this->library;
    }

    public function setLibrary(?Library $library): static
    {
        $this->library = $library;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): static
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;

        return $this;
    }

    /**
     * Vérifie si l'emprunt est en retard
     */
    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_RETURNED || !$this->startAt) {
            return false;
        }

        $now = new \DateTimeImmutable();
        $expectedReturn = $this->startAt->modify('+14 days'); // 14 jours par défaut

        return $now > $expectedReturn;
    }

    /**
     * Calcule le nombre de jours de retard
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $now = new \DateTimeImmutable();
        $expectedReturn = $this->startAt->modify('+14 days');
        
        return $now->diff($expectedReturn)->days;
    }

    /**
     * Retourne le statut en français pour l'affichage
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_RETURNED => 'Retourné',
            self::STATUS_OVERDUE => 'En retard',
            default => 'Inconnu',
        };
    }

    /**
     * Retourne la classe CSS Bootstrap pour le badge de statut
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_ACTIVE => 'bg-info',
            self::STATUS_RETURNED => 'bg-secondary',
            self::STATUS_OVERDUE => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
