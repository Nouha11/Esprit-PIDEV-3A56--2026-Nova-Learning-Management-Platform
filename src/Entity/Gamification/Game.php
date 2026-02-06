<?php

namespace App\Entity\Gamification;

use App\Repository\Gamification\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Game name is required")]
    #[Assert\Length(
    min: 3,
    max: 255,
    minMessage: "Name must be at least 3 characters",
    maxMessage: "Name cannot exceed 255 characters"
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Description is required")]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
    choices: ['PUZZLE', 'MEMORY', 'TRIVIA', 'ARCADE'],
    message: 'Choose a valid game type'
    )]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
    choices: ['EASY', 'MEDIUM', 'HARD'],
    message: 'Choose a valid difficulty'
    )]
    private ?string $difficulty = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: "Token cost must be 0 or positive")]
    private ?int $tokenCost = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $rewardTokens = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $rewardXP = 0;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and Setters
    public function getId(): ?int
    {   
        return $this->id;
    }   

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getTokenCost(): ?int
    {
        return $this->tokenCost;
    }

    public function setTokenCost(int $tokenCost): static
    {
        $this->tokenCost = $tokenCost;
        return $this;
    }

    public function getRewardTokens(): ?int
    {
        return $this->rewardTokens;
    }

    public function setRewardTokens(int $rewardTokens): static
    {
        $this->rewardTokens = $rewardTokens;
        return $this;
    }

    public function getRewardXP(): ?int
    {
        return $this->rewardXP;
    }

    public function setRewardXP(int $rewardXP): static
    {
        $this->rewardXP = $rewardXP;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
