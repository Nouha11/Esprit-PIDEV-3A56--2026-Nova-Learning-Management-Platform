<?php

namespace App\Entity\users;

use App\Repository\TutorProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: TutorProfileRepository::class)]
#[Vich\Uploadable]
class TutorProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'First name is required')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'First name must be at least {{ limit }} characters',
        maxMessage: 'First name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/u',
        message: 'First name can only contain letters, spaces and hyphens'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Last name is required')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Last name must be at least {{ limit }} characters',
        maxMessage: 'Last name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/u',
        message: 'Last name can only contain letters, spaces and hyphens'
    )]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Bio cannot be longer than {{ limit }} characters'
    )]
    private ?string $bio = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Assert\NotBlank(message: 'Expertise is required')]
    private ?array $expertise = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'Qualifications cannot be longer than {{ limit }} characters'
    )]
    private ?string $qualifications = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Years of experience is required')]
    #[Assert\Type(type: 'integer', message: 'Years of experience must be a number')]
    #[Assert\Range(
        min: 0,
        max: 50,
        notInRangeMessage: 'Years of experience must be between {{ min }} and {{ max }}'
    )]
    private ?int $yearsOfExperience = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Hourly rate must be a positive number')]
    #[Assert\Range(
        min: 0,
        max: 1000,
        notInRangeMessage: 'Hourly rate must be between {{ min }} and {{ max }}'
    )]
    private ?string $hourlyRate = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Availability status is required')]
    #[Assert\Type(type: 'bool', message: 'Availability must be true or false')]
    private ?bool $isAvailable = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Profile picture path cannot be longer than {{ limit }} characters'
    )]
    private ?string $profilePicture = null;

    #[Vich\UploadableField(mapping: 'user_avatars', fileNameProperty: 'profilePicture')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, GIF, or WebP)'
    )]
    private ?File $avatarFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getExpertise(): ?array
    {
        return $this->expertise;
    }

    public function setExpertise(?array $expertise): static
    {
        $this->expertise = $expertise;
        return $this;
    }

    public function getQualifications(): ?string
    {
        return $this->qualifications;
    }

    public function setQualifications(?string $qualifications): static
    {
        $this->qualifications = $qualifications;

        return $this;
    }

    public function getYearsOfExperience(): ?int
    {
        return $this->yearsOfExperience;
    }

    public function setYearsOfExperience(int $yearsOfExperience): static
    {
        $this->yearsOfExperience = $yearsOfExperience;

        return $this;
    }

    public function getHourlyRate(): ?string
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?string $hourlyRate): static
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function setAvatarFile(?File $avatarFile = null): void
    {
        $this->avatarFile = $avatarFile;

        if (null !== $avatarFile) {
            // Update the updatedAt property to force Doctrine to update
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
