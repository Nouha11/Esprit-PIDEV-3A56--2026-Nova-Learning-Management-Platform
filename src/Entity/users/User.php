<?php

namespace App\Entity\users;

use App\Entity\Forum\Report;
use App\Entity\StudySession\StudySession;
use App\Entity\Forum\Comment;
use App\Entity\Forum\Post;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'This email is already registered')]
#[UniqueEntity(fields: ['username'], message: 'This username is already taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Please enter a valid email address')]
    #[Assert\Length(max: 180, maxMessage: 'Email cannot be longer than {{ limit }} characters')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(min: 8, max: 255, minMessage: 'Password must be at least {{ limit }} characters', maxMessage: 'Password cannot be longer than {{ limit }} characters')]
    private ?string $password = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Username is required')]
    #[Assert\Length(min: 3, max: 100, minMessage: 'Username must be at least {{ limit }} characters', maxMessage: 'Username cannot be longer than {{ limit }} characters')]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Username can only contain letters, numbers and underscores')]
    private ?string $username = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Role is required')]
    #[Assert\Choice(choices: ['ROLE_ADMIN', 'ROLE_STUDENT', 'ROLE_TUTOR', 'ROLE_USER'], message: 'Please select a valid role')]
    private ?string $role = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Active status is required')]
    #[Assert\Type(type: 'bool', message: 'Active status must be true or false')]
    private ?bool $isActive = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isVerified = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationTokenExpiresAt = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetPasswordTokenExpiresAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(targetEntity: StudentProfile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'student_profile_id', referencedColumnName: 'id', nullable: true)]
    private ?StudentProfile $studentProfile = null;

    #[ORM\OneToOne(targetEntity: TutorProfile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'tutor_profile_id', referencedColumnName: 'id', nullable: true)]
    private ?TutorProfile $tutorProfile = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $xp = 0;

    #[ORM\OneToMany(targetEntity: StudySession::class, mappedBy: 'user')]
    private Collection $studySessions;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    private Collection $comments;

    #[ORM\OneToMany(targetEntity: \App\Entity\Library\Book::class, mappedBy: 'user')]
    private Collection $books;

    #[ORM\OneToMany(targetEntity: \App\Entity\Gamification\Game::class, mappedBy: 'user')]
    private Collection $games;

    #[ORM\OneToMany(targetEntity: \App\Entity\Quiz\Question::class, mappedBy: 'user')]
    private Collection $questions;

    // --- FIXED: mappedBy changed from 'instructor' to 'createdBy' ---
    #[ORM\OneToMany(targetEntity: \App\Entity\StudySession\Course::class, mappedBy: 'createdBy')]
    private Collection $courses;

    // Many-to-many: User can favorite many games
    #[ORM\ManyToMany(targetEntity: \App\Entity\Gamification\Game::class, inversedBy: 'favoritedBy')]
    #[ORM\JoinTable(name: 'user_favorite_games')]
    private Collection $favoriteGames;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'reporter')]
    private Collection $reports;

    public function __construct()
    {
        $this->studySessions = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->books = new ArrayCollection();
        $this->games = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->favoriteGames = new ArrayCollection();
        $this->xp = 0;
        $this->reports = new ArrayCollection();
    }

    // ... (Getters/Setters unchanged) ...
    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function getUsername(): ?string { return $this->username; }
    public function setUsername(string $username): static { $this->username = $username; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }
    public function isActive(): ?bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    
    // ... (Collections unchanged) ...
    public function getStudySessions(): Collection { return $this->studySessions; }
    public function addStudySession(StudySession $studySession): static {
        if (!$this->studySessions->contains($studySession)) { $this->studySessions->add($studySession); $studySession->setUser($this); }
        return $this;
    }
    public function removeStudySession(StudySession $studySession): static {
        if ($this->studySessions->removeElement($studySession)) { if ($studySession->getUser() === $this) { $studySession->setUser(null); } }
        return $this;
    }
    public function getPosts(): Collection { return $this->posts; }
    public function addPost(Post $post): static {
        if (!$this->posts->contains($post)) { $this->posts->add($post); $post->setAuthor($this); }
        return $this;
    }
    public function removePost(Post $post): static {
        if ($this->posts->removeElement($post)) { if ($post->getAuthor() === $this) { $post->setAuthor(null); } }
        return $this;
    }
    public function getComments(): Collection { return $this->comments; }
    public function addComment(Comment $comment): static {
        if (!$this->comments->contains($comment)) { $this->comments->add($comment); $comment->setAuthor($this); }
        return $this;
    }
    public function removeComment(Comment $comment): static {
        if ($this->comments->removeElement($comment)) { if ($comment->getAuthor() === $this) { $comment->setAuthor(null); } }
        return $this;
    }
    public function getBooks(): Collection { return $this->books; }
    public function addBook(\App\Entity\Library\Book $book): static {
        if (!$this->books->contains($book)) { $this->books->add($book); $book->setUser($this); }
        return $this;
    }
    public function removeBook(\App\Entity\Library\Book $book): static {
        if ($this->books->removeElement($book)) { if ($book->getUser() === $this) { $book->setUser(null); } }
        return $this;
    }
    public function getGames(): Collection { return $this->games; }
    public function addGame(\App\Entity\Gamification\Game $game): static {
        if (!$this->games->contains($game)) { $this->games->add($game); $game->setUser($this); }
        return $this;
    }
    public function removeGame(\App\Entity\Gamification\Game $game): static {
        if ($this->games->removeElement($game)) { if ($game->getUser() === $this) { $game->setUser(null); } }
        return $this;
    }
    public function getQuestions(): Collection { return $this->questions; }
    public function addQuestion(\App\Entity\Quiz\Question $question): static {
        if (!$this->questions->contains($question)) { $this->questions->add($question); $question->setUser($this); }
        return $this;
    }
    public function removeQuestion(\App\Entity\Quiz\Question $question): static {
        if ($this->questions->removeElement($question)) { if ($question->getUser() === $this) { $question->setUser(null); } }
        return $this;
    }

    // --- FIXED: Course Collection Methods ---
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(\App\Entity\StudySession\Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            // FIXED: Using setCreatedBy instead of setInstructor
            $course->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCourse(\App\Entity\StudySession\Course $course): static
    {
        if ($this->courses->removeElement($course)) {
            // FIXED: Using getCreatedBy instead of getInstructor
            if ($course->getCreatedBy() === $this) {
                $course->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getRoles(): array { return [$this->role]; }
    public function eraseCredentials(): void { }
    public function getUserIdentifier(): string { return $this->username; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
    public function getStudentProfile(): ?StudentProfile { return $this->studentProfile; }
    public function setStudentProfile(?StudentProfile $studentProfile): static { $this->studentProfile = $studentProfile; return $this; }
    public function getTutorProfile(): ?TutorProfile { return $this->tutorProfile; }
    public function setTutorProfile(?TutorProfile $tutorProfile): static { $this->tutorProfile = $tutorProfile; return $this; }
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void { $this->createdAt = new \DateTime(); $this->updatedAt = new \DateTime(); }
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void { $this->updatedAt = new \DateTime(); }
    public function getXp(): ?int { return $this->xp; }
    public function setXp(int $xp): static { $this->xp = $xp; return $this; }

    /**
     * @return Collection<int, \App\Entity\Gamification\Game>
     */
    public function getFavoriteGames(): Collection
    {
        return $this->favoriteGames;
    }

    public function addFavoriteGame(\App\Entity\Gamification\Game $game): static
    {
        if (!$this->favoriteGames->contains($game)) {
            $this->favoriteGames->add($game);
        }
        return $this;
    }

    public function removeFavoriteGame(\App\Entity\Gamification\Game $game): static
    {
        $this->favoriteGames->removeElement($game);
        return $this;
    }

    public function hasFavoriteGame(\App\Entity\Gamification\Game $game): bool
    {
        return $this->favoriteGames->contains($game);
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;
        return $this;
    }

    public function getVerificationTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->verificationTokenExpiresAt;
    }

    public function setVerificationTokenExpiresAt(?\DateTimeInterface $verificationTokenExpiresAt): static
    {
        $this->verificationTokenExpiresAt = $verificationTokenExpiresAt;
        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): static
    {
        $this->resetPasswordToken = $resetPasswordToken;
        return $this;
    }

    public function getResetPasswordTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->resetPasswordTokenExpiresAt;
    }

    public function setResetPasswordTokenExpiresAt(?\DateTimeInterface $resetPasswordTokenExpiresAt): static
    {
        $this->resetPasswordTokenExpiresAt = $resetPasswordTokenExpiresAt;
        return $this;
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
            $report->setReporter($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getReporter() === $this) {
                $report->setReporter(null);
            }
        }

        return $this;
    }
}