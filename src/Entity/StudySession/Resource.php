<?php

namespace App\Entity\StudySession;

use App\Repository\StudySession\ResourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\Table(name: 'resource')]
#[ORM\Index(name: 'idx_study_session', columns: ['study_session_id'])]
class Resource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: StudySession::class, inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: true)]
    private ?StudySession $studySession = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Course $course = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $storedFilename = null;

    #[ORM\Column]
    private ?int $fileSize = null;

    #[ORM\Column(length: 100)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudySession(): ?StudySession
    {
        return $this->studySession;
    }

    public function setStudySession(?StudySession $studySession): static
    {
        $this->studySession = $studySession;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getStoredFilename(): ?string
    {
        return $this->storedFilename;
    }

    public function setStoredFilename(string $storedFilename): static
    {
        $this->storedFilename = $storedFilename;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }
}
