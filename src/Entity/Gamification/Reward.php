<?php

namespace App\Entity\Gamification;

use App\Entity\users\StudentProfile;
use App\Repository\Gamification\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[Assert\NotBlank(message: "Reward name is required")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Name must be at least {{ limit }} characters long",
        maxMessage: "Name cannot be longer than {{ limit }} characters"
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "Description cannot be longer than {{ limit }} characters"
    )]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Reward type is required")]
    #[Assert\Choice(
        choices: ['BADGE', 'ACHIEVEMENT', 'BONUS_XP', 'BONUS_TOKENS'],
        message: 'Choose a valid reward type'
    )]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Value is required")]
    #[Assert\PositiveOrZero(message: "Value must be 0 or positive")]
    private ?int $value;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: "Requirement cannot be longer than {{ limit }} characters"
    )]
    private ?string $requirement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    // Relationship: Rewards can be offered by multiple games
    #[ORM\ManyToMany(targetEntity: Game::class, mappedBy: 'rewards')]
    private Collection $games;

    // Relationship: Track which students earned this reward
    #[ORM\ManyToMany(targetEntity: StudentProfile::class, mappedBy: 'earnedRewards')]
    private Collection $students;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->students = new ArrayCollection();
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
    
    public function setName(?string $name): static
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

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): static
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

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->addReward($this);
        }
        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            $game->removeReward($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, StudentProfile>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(StudentProfile $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->addEarnedReward($this);
        }
        return $this;
    }

    public function removeStudent(StudentProfile $student): static
    {
        if ($this->students->removeElement($student)) {
            $student->removeEarnedReward($this);
        }
        return $this;
    }
}