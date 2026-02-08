<?php

namespace App\Entity\Library;

use App\Entity\users\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Book - Représente un livre dans la bibliothèque
 * 
 * Cette classe gère toutes les informations relatives à un livre :
 * - Informations de base (titre, auteur, description)
 * - Informations commerciales (prix, format)
 * - Métadonnées (dates, uploader)
 * 
 * @author Votre Nom
 */
#[ORM\Entity]
#[ORM\Table(name: 'books')]
class Book
{
    /**
     * Identifiant unique du livre (clé primaire)
     * Généré automatiquement par la base de données
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    // Validation: Le titre est obligatoire (NotBlank)
    // Le titre doit contenir entre 3 et 255 caractères
    #[Assert\NotBlank(message: 'Le titre du livre est obligatoire')]
    #[Assert\Length(
        min: 3, 
        max: 255, 
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères', 
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    // Validation: La description est optionnelle mais limitée à 5000 caractères
    #[Assert\Length(
        max: 5000, 
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column]
    // Validation: Le champ isDigital doit être un booléen (true/false)
    #[Assert\Type(type: 'bool', message: 'Le format du livre doit être valide')]
    private ?bool $isDigital = true;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    // Validation: Le prix doit être positif ou zéro
    // Le prix doit avoir maximum 2 chiffres après la virgule (ex: 19.99)
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif ou zéro')]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/', 
        message: 'Le prix doit être un nombre valide avec maximum 2 décimales'
    )]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    // Validation: Le chemin de l'image ne peut pas dépasser 255 caractères
    #[Assert\Length(
        max: 255, 
        maxMessage: 'Le chemin de l\'image ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $coverImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    // Validation: Le nom de l'auteur est optionnel
    // S'il est fourni, il doit contenir entre 2 et 255 caractères
    #[Assert\Length(
        min: 2, 
        max: 255, 
        minMessage: 'Le nom de l\'auteur doit contenir au moins {{ limit }} caractères', 
        maxMessage: 'Le nom de l\'auteur ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $author = null;

    #[ORM\Column(length: 20, nullable: true)]
    // Validation: L'ISBN est optionnel mais doit être valide (ISBN-10 ou ISBN-13)
    #[Assert\Regex(
        pattern: '/^(?:ISBN(?:-1[03])?:? )?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]$/', 
        message: 'L\'ISBN doit être un ISBN-10 ou ISBN-13 valide'
    )]
    private ?string $isbn = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    // Validation: La date de publication ne peut pas être dans le futur
    #[Assert\LessThanOrEqual(
        'now', 
        message: 'La date de publication ne peut pas être dans le futur'
    )]
    private ?\DateTimeImmutable $publishedAt = null;

    /**
     * ID de l'utilisateur qui a uploadé ce livre
     * Permet de tracer qui a ajouté le livre dans le système
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $uploaderId = null;

    /**
     * Date de création de l'enregistrement
     * Automatiquement définie lors de l'ajout du livre
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière modification
     * Mise à jour automatiquement lors de chaque modification
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Bibliothèques où se trouve le livre physique
     * Un livre peut être disponible dans plusieurs bibliothèques
     */
    #[ORM\ManyToMany(targetEntity: Library::class)]
    #[ORM\JoinTable(name: 'book_library')]
    #[ORM\JoinColumn(name: 'book_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'library_id', referencedColumnName: 'id')]
    private Collection $libraries;

    public function __construct()
    {
        $this->libraries = new ArrayCollection();
    }

    // ==================== GETTERS ET SETTERS ====================

    /**
     * Retourne l'ID du livre
     * @return int|null L'identifiant unique du livre
     */public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isDigital(): ?bool
    {
        return $this->isDigital;
    }

    public function setIsDigital(bool $isDigital): static
    {
        $this->isDigital = $isDigital;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getUploaderId(): ?int
    {
        return $this->uploaderId;
    }

    public function setUploaderId(?int $uploaderId): static
    {
        $this->uploaderId = $uploaderId;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Library>
     */
    public function getLibraries(): Collection
    {
        return $this->libraries;
    }

    public function addLibrary(Library $library): static
    {
        if (!$this->libraries->contains($library)) {
            $this->libraries->add($library);
        }

        return $this;
    }

    public function removeLibrary(Library $library): static
    {
        $this->libraries->removeElement($library);

        return $this;
    }
}
