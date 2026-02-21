<?php

namespace App\Entity\Quiz;

// Quiz.php

use App\Entity\Quiz;
use App\Repository\Quiz\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Validator as MyAssert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[Vich\Uploadable]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Text is required")]
    #[Assert\Length(
        min: 1,
        max: 5000,
        minMessage: "Text must be at least {{ limit }} character long",
        maxMessage: "Text cannot be longer than {{ limit }} characters"
    )]
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

    #[Vich\UploadableField(mapping: 'question_images', fileNameProperty: 'imageName')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, GIF, or WebP)'
    )]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // Update the updatedAt property to force Doctrine to update
            $this->updatedAt = new \DateTime();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
