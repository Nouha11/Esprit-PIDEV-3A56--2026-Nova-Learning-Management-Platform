<?php

namespace App\Entity\Gamification;

use App\Repository\Gamification\GameContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameContentRepository::class)]
#[ORM\Table(name: 'game_content')]
class GameContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'content', targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Game $game = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $data = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(Game $game): static
    {
        $this->game = $game;
        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;
        return $this;
    }

    // Helper methods for specific game types
    
    // PUZZLE - Word Scramble
    public function getWord(): ?string
    {
        return $this->data['word'] ?? null;
    }

    public function setWord(?string $word): static
    {
        $this->data['word'] = $word;
        return $this;
    }

    public function getHint(): ?string
    {
        return $this->data['hint'] ?? null;
    }

    public function setHint(?string $hint): static
    {
        $this->data['hint'] = $hint;
        return $this;
    }

    // MEMORY - Card Flip
    public function getWords(): ?array
    {
        return $this->data['words'] ?? null;
    }

    public function setWords(?array $words): static
    {
        $this->data['words'] = $words;
        return $this;
    }

    // TRIVIA - Quiz Questions
    public function getQuestions(): ?array
    {
        return $this->data['questions'] ?? null;
    }

    public function setQuestions(?array $questions): static
    {
        $this->data['questions'] = $questions;
        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->data['topic'] ?? null;
    }

    public function setTopic(?string $topic): static
    {
        $this->data['topic'] = $topic;
        return $this;
    }

    // ARCADE - Typing Challenge
    public function getSentences(): ?array
    {
        return $this->data['sentences'] ?? null;
    }

    public function setSentences(?array $sentences): static
    {
        $this->data['sentences'] = $sentences;
        return $this;
    }
}
