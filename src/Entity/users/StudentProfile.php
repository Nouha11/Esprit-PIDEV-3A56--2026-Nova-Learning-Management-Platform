<?php

namespace App\Entity\users;

use App\Repository\StudentProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentProfileRepository::class)]
class StudentProfile
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

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'University is required')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'University name must be at least {{ limit }} characters',
        maxMessage: 'University name cannot be longer than {{ limit }} characters'
    )]
    private ?string $university = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Major cannot be longer than {{ limit }} characters'
    )]
    private ?string $major = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate', 'PhD'],
        message: 'Please select a valid academic level'
    )]
    private ?string $academicLevel = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Profile picture path cannot be longer than {{ limit }} characters'
    )]
    private ?string $profilePicture = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'Interests cannot be longer than {{ limit }} characters'
    )]
    private ?string $interests = null;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getUniversity(): ?string
    {
        return $this->university;
    }

    public function setUniversity(string $university): static
    {
        $this->university = $university;

        return $this;
    }

    public function getMajor(): ?string
    {
        return $this->major;
    }

    public function setMajor(?string $major): static
    {
        $this->major = $major;

        return $this;
    }

    public function getAcademicLevel(): ?string
    {
        return $this->academicLevel;
    }

    public function setAcademicLevel(?string $academicLevel): static
    {
        $this->academicLevel = $academicLevel;

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

    public function getInterests(): ?string
    {
        return $this->interests;
    }

    public function setInterests(?string $interests): static
    {
        $this->interests = $interests;

        return $this;
    }
}
