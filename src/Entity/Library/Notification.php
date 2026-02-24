<?php

namespace App\Entity\Library;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité pour les notifications utilisateur liées à la bibliothèque
 */
#[ORM\Entity]
#[ORM\Table(name: 'library_notifications')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\users\User')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 50)]
    private string $type; // 'loan_approved', 'loan_rejected', 'loan_due', 'loan_overdue', etc.

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $link = null; // URL to related resource

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        if ($isRead && !$this->readAt) {
            $this->readAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getIcon(): string
    {
        return match($this->type) {
            'loan_approved' => 'bi-check-circle-fill text-success',
            'loan_rejected' => 'bi-x-circle-fill text-danger',
            'loan_active' => 'bi-book-fill text-primary',
            'loan_returned' => 'bi-check2-circle text-info',
            'loan_due' => 'bi-clock-fill text-warning',
            'loan_overdue' => 'bi-exclamation-triangle-fill text-danger',
            'payment_success' => 'bi-credit-card-fill text-success',
            'payment_failed' => 'bi-credit-card-fill text-danger',
            default => 'bi-bell-fill text-primary',
        };
    }

    public function getTimeAgo(): string
    {
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $this->createdAt->getTimestamp();
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' min ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $this->createdAt->format('M d, Y');
        }
    }
}
