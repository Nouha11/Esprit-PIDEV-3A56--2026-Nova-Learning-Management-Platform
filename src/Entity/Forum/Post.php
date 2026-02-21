<?php

namespace App\Entity\Forum;

use App\Entity\users\User; 
use App\Repository\Forum\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: PostRepository::class)]
#[Vich\Uploadable] // --- NEW: Tells Vich this entity handles uploads ---
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title cannot be empty!")]
    #[Assert\Length(min: 5, max: 255, minMessage: "Title must be at least 5 characters long")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Please write a question!")]
    #[Assert\Length(min: 10, minMessage: "Your question is too short! Describe it more.")]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $upvotes = 0; // Default to 0

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\ManyToOne(targetEntity: \App\Entity\users\User::class, inversedBy: 'posts')] 
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null; 

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['remove'])]
    private Collection $comments;

    #[ORM\Column]
    private ?bool $isLocked = false;

    // --- NEW: Store the list of users who upvoted ---
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_upvoters')]  
    private Collection $upvoters;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'post')]
    private Collection $reports;

    // ==========================================
    // --- NEW: Image and Link Properties ---
    // ==========================================
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'post_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;
    // ==========================================

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->upvoters = new ArrayCollection(); // Initialize the new collection
        $this->upvotes = 0;
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getUpvotes(): ?int
    {
        return $this->upvotes;
    }

    public function setUpvotes(int $upvotes): static
    {
        $this->upvotes = $upvotes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }
        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    // --- Methods to manage upvoters ---

    /**
     * @return Collection<int, User>
     */
    public function getUpvoters(): Collection
    {
        return $this->upvoters;
    }

    public function addUpvoter(User $upvoter): static
    {
        if (!$this->upvoters->contains($upvoter)) {
            $this->upvoters->add($upvoter);
            $this->upvotes++; // Automatically increase count
        }
        return $this;
    }

    public function removeUpvoter(User $upvoter): static
    {
        if ($this->upvoters->removeElement($upvoter)) {
            $this->upvotes--; // Automatically decrease count
        }
        return $this;
    }

    public function isUpvotedBy(User $user): bool
    {
        return $this->upvoters->contains($user);
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setPost($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getPost() === $this) {
                $report->setPost(null);
            }
        }

        return $this;
    }

    // ==========================================
    // --- NEW: Image and Link Methods ---
    // ==========================================
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}