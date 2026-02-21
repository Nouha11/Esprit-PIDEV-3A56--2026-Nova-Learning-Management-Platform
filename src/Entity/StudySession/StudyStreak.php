<?php

namespace App\Entity\StudySession;

use App\Entity\users\User;
use App\Repository\StudySession\StudyStreakRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudyStreakRepository::class)]
#[ORM\Table(name: 'study_streak')]
class StudyStreak
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?User $user = null;

    #[ORM\Column]
    private int $currentStreak = 0;

    #[ORM\Column]
    private int $longestStreak = 0;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastStudyDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCurrentStreak(): int
    {
        return $this->currentStreak;
    }

    public function setCurrentStreak(int $currentStreak): static
    {
        $this->currentStreak = $currentStreak;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLongestStreak(): int
    {
        return $this->longestStreak;
    }

    public function setLongestStreak(int $longestStreak): static
    {
        $this->longestStreak = $longestStreak;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLastStudyDate(): ?\DateTimeImmutable
    {
        return $this->lastStudyDate;
    }

    public function setLastStudyDate(?\DateTimeImmutable $lastStudyDate): static
    {
        $this->lastStudyDate = $lastStudyDate;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
