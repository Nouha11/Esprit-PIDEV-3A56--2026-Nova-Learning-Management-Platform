<?php

namespace App\Entity\Gamification;

use App\Repository\Gamification\RewardRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: RewardRepository::class)]
class Reward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
    choices: ['BADGE', 'ACHIEVEMENT', 'BONUS_XP', 'BONUS_TOKENS'],
    message: 'Choose a valid reward type'
    )]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?int $value = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requirement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private ?bool $isActive = true;

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

    public function setDescription(?string $description): static
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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getRequirement(): ?string
    {
        return $this->requirement;
    }

    public function setRequirement(?string $requirement): static
    {
        $this->requirement = $requirement;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
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
}
