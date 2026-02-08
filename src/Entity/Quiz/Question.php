<?php

namespace App\Entity\Quiz;

use App\Entity\Quiz;
use App\Repository\Quiz\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as MyAssert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "You must write a question!")]
    #[Assert\Length(min: 5, minMessage: "The question is too short (min 5 characters).")]
    private ?string $text = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Please define the XP value.")]
    #[Assert\Positive(message: "XP cannot be negative.")]
    #[Assert\Range(min: 10, max: 1000, notInRangeMessage: "XP must be between 10 and 1000.")]
    private ?int $xpValue = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Please select a difficulty level.")]
    #[Assert\Choice(choices: ['Easy', 'Medium', 'Hard'], message: "Choose a valid difficulty: Easy, Medium, or Hard.")]
    private ?string $difficulty = null;

    /**
     * @var Collection<int, Choice>
     */
    #[ORM\OneToMany(targetEntity: Choice::class, mappedBy: 'question', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Count(min: 2, minMessage: "You need at least 2 choices.")]
    #[Assert\Valid] // to help validate the choices inside
    #[MyAssert\SingleCorrectAnswer] // <--- for the validator
    private Collection $choices;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\users\User::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?\App\Entity\users\User $user = null;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getXpValue(): ?int
    {
        return $this->xpValue;
    }

    public function setXpValue(int $xpValue): static
    {
        $this->xpValue = $xpValue;

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

    /**
     * @return Collection<int, Choice>
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice): static
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setQuestion($this);
        }

        return $this;
    }

    public function removeChoice(Choice $choice): static
    {
        if ($this->choices->removeElement($choice)) {
            // set the owning side to null (unless already changed)
            if ($choice->getQuestion() === $this) {
                $choice->setQuestion(null);
            }
        }

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getUser(): ?\App\Entity\users\User
    {
        return $this->user;
    }

    public function setUser(?\App\Entity\users\User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
