<?php

namespace App\Entity\Forum;

use App\Entity\users\User;
use App\Repository\Forum\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; 
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[Vich\Uploadable] // <-- NEW: Tells Vich this entity handles uploads
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: Types::TEXT)]
    // Assertions removed here. Handled in Controller/FormType now.
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isSolution = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;
    
    #[ORM\ManyToOne(targetEntity: \App\Entity\users\User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    // --- VOTING FIELDS ---
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'comment_upvoters')]
    private Collection $upvoters;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'comment_downvoters')]
    private Collection $downvoters;

    // --- NESTED REPLIES (THREADS) ---
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $replies;

    // --- NEW: IMAGE FIELDS ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'comment_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    public function __construct()
    {
        $this->upvoters = new ArrayCollection();
        $this->downvoters = new ArrayCollection();
        $this->replies = new ArrayCollection();
    }

    // --- LOGIC METHODS ---
    public function getScore(): int { return $this->upvoters->count() - $this->downvoters->count(); }
    public function isUpvotedBy(User $user): bool { return $this->upvoters->contains($user); }
    public function isDownvotedBy(User $user): bool { return $this->downvoters->contains($user); }

    // --- STANDARD GETTERS & SETTERS ---
    public function getId(): ?int { return $this->id; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): static { $this->content = $content; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
    public function isSolution(): ?bool { return $this->isSolution; }
    public function setIsSolution(bool $isSolution): static { $this->isSolution = $isSolution; return $this; }
    public function getPost(): ?Post { return $this->post; }
    public function setPost(?Post $post): static { $this->post = $post; return $this; }
    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function getUpvoters(): Collection { return $this->upvoters; }
    public function addUpvoter(User $upvoter): static { if (!$this->upvoters->contains($upvoter)) { $this->upvoters->add($upvoter); } return $this; }
    public function removeUpvoter(User $upvoter): static { $this->upvoters->removeElement($upvoter); return $this; }

    public function getDownvoters(): Collection { return $this->downvoters; }
    public function addDownvoter(User $downvoter): static { if (!$this->downvoters->contains($downvoter)) { $this->downvoters->add($downvoter); } return $this; }
    public function removeDownvoter(User $downvoter): static { $this->downvoters->removeElement($downvoter); return $this; }

    public function getParent(): ?self { return $this->parent; }
    public function setParent(?self $parent): static { $this->parent = $parent; return $this; }
    public function getReplies(): Collection { return $this->replies; }
    public function addReply(self $reply): static { if (!$this->replies->contains($reply)) { $this->replies->add($reply); $reply->setParent($this); } return $this; }
    public function removeReply(self $reply): static { if ($this->replies->removeElement($reply)) { if ($reply->getParent() === $this) { $reply->setParent(null); } } return $this; }

    // --- NEW: IMAGE GETTERS & SETTERS ---
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getImageFile(): ?File { return $this->imageFile; }
    public function setImageName(?string $imageName): static { $this->imageName = $imageName; return $this; }
    public function getImageName(): ?string { return $this->imageName; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}