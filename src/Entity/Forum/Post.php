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
#[Vich\Uploadable] 
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
    private ?int $upvotes = 0; 

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\ManyToOne(targetEntity: \App\Entity\users\User::class, inversedBy: 'posts')] 
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null; 

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['remove'])]
    private Collection $comments;

    #[ORM\Column]
    private ?bool $isLocked = false;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_upvoters')]  
    private Collection $upvoters;

    // --- NEW: DOWNVOTERS TRACKING ---
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'post_downvoters')]  
    private Collection $downvoters;

    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'post')]
    private Collection $reports;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'post_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Space $space = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $linkDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $linkImage = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachmentName = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $hotScore = 0.0;

    #[Vich\UploadableField(mapping: 'forum_attachments', fileNameProperty: 'attachmentName')]
    private ?File $attachmentFile = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->upvoters = new ArrayCollection(); 
        $this->downvoters = new ArrayCollection(); // Initialize downvoters
        $this->reports = new ArrayCollection();
        $this->upvotes = 0;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): static { $this->title = $title; return $this; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): static { $this->content = $content; return $this; }
    public function getUpvotes(): ?int { return $this->upvotes; }
    public function setUpvotes(int $upvotes): static { $this->upvotes = $upvotes; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        $this->updateHotScore(); // Calculate initial hotness
        return $this;
    }    public function getAuthor(): ?User { return $this->author; }


    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function getComments(): Collection { return $this->comments; }
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
            if ($comment->getPost() === $this) { $comment->setPost(null); }
        }
        return $this;
    }

    public function isLocked(): ?bool { return $this->isLocked; }
    public function setIsLocked(bool $isLocked): static { $this->isLocked = $isLocked; return $this; }

    // --- UPVOTER METHODS ---
    public function getUpvoters(): Collection { return $this->upvoters; }
    public function addUpvoter(User $upvoter): static
    {
        if (!$this->upvoters->contains($upvoter)) {
            $this->upvoters->add($upvoter);
            $this->upvotes++; 
            $this->updateHotScore(); // Recalibrate!
        }
        return $this;
    }

   public function removeUpvoter(User $upvoter): static
    {
        if ($this->upvoters->removeElement($upvoter)) {
            $this->upvotes--; 
            $this->updateHotScore(); // Recalibrate!
        }
        return $this;
    }

    public function isUpvotedBy(User $user): bool { return $this->upvoters->contains($user); }

    // --- DOWNVOTER METHODS ---
    public function getDownvoters(): Collection { return $this->downvoters; }
    public function addDownvoter(User $downvoter): static
    {
        if (!$this->downvoters->contains($downvoter)) {
            $this->downvoters->add($downvoter);
            $this->upvotes--; 
            $this->updateHotScore(); // Recalibrate!
        }
        return $this;
    }
   public function removeDownvoter(User $downvoter): static
    {
        if ($this->downvoters->removeElement($downvoter)) {
            $this->upvotes++; 
            $this->updateHotScore(); // Recalibrate!
        }
        return $this;
    }
    public function isDownvotedBy(User $user): bool { return $this->downvoters->contains($user); }

    public function getReports(): Collection { return $this->reports; }
    public function addReport(Report $report): static {
        if (!$this->reports->contains($report)) { $this->reports->add($report); $report->setPost($this); }
        return $this;
    }
    public function removeReport(Report $report): static {
        if ($this->reports->removeElement($report)) { if ($report->getPost() === $this) { $report->setPost(null); } }
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) { $this->updatedAt = new \DateTimeImmutable(); }
    }
    public function getImageFile(): ?File { return $this->imageFile; }
    public function setImageName(?string $imageName): static { $this->imageName = $imageName; return $this; }
    public function getImageName(): ?string { return $this->imageName; }
    
    public function getLink(): ?string { return $this->link; }
    public function setLink(?string $link): static { $this->link = $link; return $this; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
    public function getSpace(): ?Space { return $this->space; }
    public function setSpace(?Space $space): static { $this->space = $space; return $this; }
    public function getLinkTitle(): ?string { return $this->linkTitle; }
    public function setLinkTitle(?string $linkTitle): static { $this->linkTitle = $linkTitle; return $this; }
    public function getLinkDescription(): ?string { return $this->linkDescription; }
    public function setLinkDescription(?string $linkDescription): static { $this->linkDescription = $linkDescription; return $this; }
    public function getLinkImage(): ?string { return $this->linkImage; }
    public function setLinkImage(?string $linkImage): static { $this->linkImage = $linkImage; return $this; }
    public function getAttachmentName(): ?string { return $this->attachmentName; }
    public function setAttachmentName(?string $attachmentName): static { $this->attachmentName = $attachmentName; return $this; }
    public function getAttachmentFile(): ?File { return $this->attachmentFile; }
    public function setAttachmentFile(?File $attachmentFile = null): void {
        $this->attachmentFile = $attachmentFile;
        if (null !== $attachmentFile && property_exists($this, 'updatedAt')) { $this->updatedAt = new \DateTimeImmutable(); }
    }

    public function getHotScore(): float
    {
        return $this->hotScore;
    }

    public function setHotScore(float $hotScore): static
    {
        $this->hotScore = $hotScore;
        return $this;
    }

    public function updateHotScore(): void
    {
        $score = $this->upvotes; // Note: upvotes represents the Net Score (Up - Down)
        
        // 1. Logarithmic scale for votes
        $order = log10(max(abs($score), 1));
        
        // 2. Sign (1 for positive, -1 for negative, 0 for zero)
        $sign = $score > 0 ? 1 : ($score < 0 ? -1 : 0);
        
        // 3. Seconds since epoch (Use post creation time, or current time if brand new)
        $seconds = $this->createdAt ? $this->createdAt->getTimestamp() : time();
        
        // 4. Reddit Formula (45000 seconds = 12.5 hours)
        $this->hotScore = round($order + ($sign * $seconds) / 45000, 7);
    }

}