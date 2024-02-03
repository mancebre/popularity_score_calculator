<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SearchResultRepository;

#[ORM\Entity(repositoryClass: SearchResultRepository::class)]
#[ORM\Table(name: 'search_result')]
class SearchResult
{
    #[ORM\Id(), ORM\GeneratedValue(strategy: "SEQUENCE"), ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    public $term;


    #[ORM\Column(type: 'float')]
    public $score;


    #[ORM\Column(type: 'datetime')]
    public $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTerm(): ?string
    {
        return $this->term;
    }

    public function setTerm(string $term): self
    {
        $this->term = $term;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAll()
    {
        return [
            'id' => $this->getId(),
            'term' => $this->getTerm(),
            'score' => $this->getScore(),
            'created_at' => $this->getCreatedAt()
        ];
    }
}
