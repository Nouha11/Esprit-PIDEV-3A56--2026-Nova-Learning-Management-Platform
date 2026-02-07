<?php

namespace App\Entity\Gamification;

use App\Entity\users\StudentProfile;
use App\Repository\Gamification\StudentGameProgressRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: StudentGameProgressRepository::class)]
#[ORM\HasLifecycleCallbacks]
class StudentGameProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: StudentProfile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?StudentProfile $student = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column]
    private ?int $timesPlayed = 0;

    #[ORM\Column]
    private ?int $timesWon = 0;

    #[ORM\Column]
    private ?int $totalXPEarned = 0;

    #[ORM\Column]
    private ?int $totalTokensEarned = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastPlayedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?StudentProfile
    {
        return $this->student;
    }

    public function setStudent(?StudentProfile $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;
        return $this;
    }

    public function getTimesPlayed(): ?int
    {
        return $this->timesPlayed;
    }

    public function setTimesPlayed(int $timesPlayed): static
    {
        $this->timesPlayed = $timesPlayed;
        return $this;
    }

    public function incrementTimesPlayed(): static
    {
        $this->timesPlayed++;
        return $this;
    }

    public function getTimesWon(): ?int
    {
        return $this->timesWon;
    }

    public function setTimesWon(int $timesWon): static
    {
        $this->timesWon = $timesWon;
        return $this;
    }

    public function incrementTimesWon(): static
    {
        $this->timesWon++;
        return $this;
    }

    public function getTotalXPEarned(): ?int
    {
        return $this->totalXPEarned;
    }

    public function setTotalXPEarned(int $totalXPEarned): static
    {
        $this->totalXPEarned = $totalXPEarned;
        return $this;
    }

    public function addXP(int $xp): static
    {
        $this->totalXPEarned += $xp;
        return $this;
    }

    public function getTotalTokensEarned(): ?int
    {
        return $this->totalTokensEarned;
    }

    public function setTotalTokensEarned(int $totalTokensEarned): static
    {
        $this->totalTokensEarned = $totalTokensEarned;
        return $this;
    }

    public function addTokens(int $tokens): static
    {
        $this->totalTokensEarned += $tokens;
        return $this;
    }

    public function getLastPlayedAt(): ?\DateTimeInterface
    {
        return $this->lastPlayedAt;
    }

    public function setLastPlayedAt(?\DateTimeInterface $lastPlayedAt): static
    {
        $this->lastPlayedAt = $lastPlayedAt;
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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getWinRate(): float
    {
        if ($this->timesPlayed === 0) {
            return 0.0;
        }
        return round(($this->timesWon / $this->timesPlayed) * 100, 2);
    }
}
