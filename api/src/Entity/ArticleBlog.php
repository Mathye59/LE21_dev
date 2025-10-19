<?php

namespace App\Entity;

use App\Repository\ArticleBlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: ArticleBlogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ArticleBlog
{
    // --- Identité -----------------------------------------------------------
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // --- Données de contenu -------------------------------------------------
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    /**
     * Résumé court (affiché dans les listes/teasers).
     * Nullable : s’il est vide, il sera auto-généré à partir de "contenu".
     */
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Résumé court de l’article'])]
    private ?string $resume = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    // --- Relations ----------------------------------------------------------
    #[ORM\ManyToOne(inversedBy: 'articleBlogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tatoueur $auteur = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'article')]
    private Collection $commentaires;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        // $this->date = new \DateTime(); // si tu veux une date par défaut
    }

    // --- Getters / Setters --------------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): static
    {
        $this->resume = $resume;
        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getAuteur(): ?Tatoueur
    {
        return $this->auteur;
    }

    public function setAuteur(?Tatoueur $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(Media $media): static
    {
        $this->media = $media;
        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setArticle($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getArticle() === $this) {
                $commentaire->setArticle(null);
            }
        }
        return $this;
    }

    // --- Hooks : auto-compléter le résumé si vide ---------------------------

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function autofillResumeIfEmpty(): void
    {
        if ($this->resume || !$this->contenu) {
            return;
        }

        // Nettoyage basique du HTML → texte simple
        $plain = strip_tags((string) $this->contenu);
        $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? '';

        // Coupe proprement (≈ 220 caractères)
        $max = 220;
        $this->resume = mb_strlen($plain) > $max
            ? mb_substr($plain, 0, $max) . '…'
            : $plain;
    }
    // --- Hook : définir la date actuelle si vide au moment de la création ---
    #[ORM\PrePersist]
    public function setDateIfEmpty(): void
    {
        if ($this->date === null) {
            // Choisis le fuseau si tu veux, ex. 'Europe/Paris'
            $this->date = new \DateTime(); 
            // $this->date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        }
    }
}
