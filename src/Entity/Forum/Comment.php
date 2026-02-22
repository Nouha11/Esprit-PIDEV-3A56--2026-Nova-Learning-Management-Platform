<?php

namespace App\Entity\Forum;

use App\Entity\users\User;
use App\Repository\Forum\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; 

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "You cannot post an empty answer.")]
    #[Assert\Length(min: 5, minMessage: "Your answer is too short.")]
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

    // --- NEW: NESTED REPLIES (THREADS) ---
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $replies;

    public function __construct()
    {
        $this->upvoters = new ArrayCollection();
        $this->downvoters = new ArrayCollection();
        $this->replies = new ArrayCollection(); // Initialize the replies collection
    }

    // --- LOGIC METHODS (For Twig & Controller) ---

    public function getScore(): int
    {
        return $this->upvoters->count() - $this->downvoters->count();
    }

    public function isUpvotedBy(User $user): bool
    {
        return $this->upvoters->contains($user);
    }

    public function isDownvotedBy(User $user): bool
    {
        return $this->downvoters->contains($user);
    }

    // --- STANDARD GETTERS & SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isSolution(): ?bool
    {
        return $this->isSolution;
    }

    public function setIsSolution(bool $isSolution): static
    {
        $this->isSolution = $isSolution;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
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
        }
        return $this;
    }

    public function removeUpvoter(User $upvoter): static
    {
        $this->upvoters->removeElement($upvoter);
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getDownvoters(): Collection
    {
        return $this->downvoters;
    }

    public function addDownvoter(User $downvoter): static
    {
        if (!$this->downvoters->contains($downvoter)) {
            $this->downvoters->add($downvoter);
        }
        return $this;
    }

    public function removeDownvoter(User $downvoter): static
    {
        $this->downvoters->removeElement($downvoter);
        return $this;
    }

    // --- NEW: PARENT / REPLY GETTERS AND SETTERS ---
    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setParent($this);
        }
        return $this;
    }

    public function removeReply(self $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }
        return $this;
    }
}