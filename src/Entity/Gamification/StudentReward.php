<?php

namespace App\Entity\Gamification;

use App\Entity\users\StudentProfile;
use App\Repository\Gamification\StudentRewardRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: StudentRewardRepository::class)]
#[ORM\HasLifecycleCallbacks]
class StudentReward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: StudentProfile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?StudentProfile $student = null;

    #[ORM\ManyToOne(targetEntity: Reward::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reward $reward = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Game $earnedFromGame = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $earnedAt = null;

    #[ORM\Column]
    private ?bool $isViewed = false;

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

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): static
    {
        $this->reward = $reward;
        return $this;
    }

    public function getEarnedFromGame(): ?Game
    {
        return $this->earnedFromGame;
    }

    public function setEarnedFromGame(?Game $earnedFromGame): static
    {
        $this->earnedFromGame = $earnedFromGame;
        return $this;
    }

    public function getEarnedAt(): ?\DateTimeInterface
    {
        return $this->earnedAt;
    }

    public function setEarnedAt(\DateTimeInterface $earnedAt): static
    {
        $this->earnedAt = $earnedAt;
        return $this;
    }

    public function isViewed(): ?bool
    {
        return $this->isViewed;
    }

    public function setIsViewed(bool $isViewed): static
    {
        $this->isViewed = $isViewed;
        return $this;
    }

    #[ORM\PrePersist]
    public function setEarnedAtValue(): void
    {
        if ($this->earnedAt === null) {
            $this->earnedAt = new \DateTime();
        }
    }
}
