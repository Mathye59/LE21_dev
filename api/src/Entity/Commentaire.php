<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $pseudoClient = null;

    #[ORM\Column(length: 200)]
    private ?string $texte = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ArticleBlog $article = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudoClient(): ?string
    {
        return $this->pseudoClient;
    }

    public function setPseudoClient(string $pseudoClient): static
    {
        $this->pseudoClient = $pseudoClient;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getArticle(): ?ArticleBlog
    {
        return $this->article;
    }

    public function setArticle(?ArticleBlog $article): static
    {
        $this->article = $article;

        return $this;
    }
}
