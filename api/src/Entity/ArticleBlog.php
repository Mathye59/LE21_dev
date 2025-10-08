<?php

namespace App\Entity;

use App\Repository\ArticleBlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource] // [API] Expose l’entité en REST (API Platform) avec opérations par défaut (GET/POST/PUT/PATCH/DELETE).
#[ORM\Entity(repositoryClass: ArticleBlogRepository::class)]
class ArticleBlog
{
    // --- Identité -----------------------------------------------------------
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // [DB] PK auto-incrément.

    // --- Données de contenu -------------------------------------------------
    #[ORM\Column(length: 255)]
    private ?string $titre = null; // [UX] 255 caractères ; prévoir Assert\Length côté validation.

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null; // [SECURITY] Si HTML : penser à sanitzer côté service/contrôleur.

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null; // [TIME] stocke une date (sans heure). Voir notes pour DateTimeImmutable.

    // --- Relations ----------------------------------------------------------
    #[ORM\ManyToOne(inversedBy: 'articleBlogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tatoueur $auteur = null; 
    // [RELATION] Plusieurs articles peuvent avoir le même auteur (Tatoueur).
    // [DB] NOT NULL → chaque article DOIT avoir un auteur.
    // [PERF] Index auto par Doctrine sur FK ; utile pour filtres par auteur.

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;
    // [RELATION] OneToOne strict → un Media ne peut être lié qu’à UN article (exclusif).
    // [DB] NOT NULL → l’article doit toujours avoir un media (image d’illustration).
    // [CASCADE] persist/remove : persiste/efface automatiquement le Media via l’Article.
    // [POLITIQUE] si tu veux réutiliser un Media pour plusieurs articles, remplacer par ManyToOne.

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'article')]
    private Collection $commentaires;
    // [RELATION] Un article a plusieurs commentaires (OneToMany).
    // [OWNING SIDE] C’est Commentaire (propriété "article") qui possède la FK.
    // [ORPHAN] Pas d’orphanRemoval : supprimer un article ne supprime pas les commentaires automatiquement (à décider).

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        // [DX] Tu peux initialiser $date ici si souhaité :
        // $this->date = new \DateTime(); // date du jour par défaut (optionnel)
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
            $commentaire->setArticle($this); // [RELATION] met à jour la FK côté propriétaire.
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // [RELATION] Si le commentaire pointait vers cet article, on le détache.
            if ($commentaire->getArticle() === $this) {
                $commentaire->setArticle(null);
                // [ORPHAN] Si tu mets orphanRemoval=true sur la relation (et nullable=false côté Commentaire.article),
                // ce set null échouera. À décider selon ta politique de suppression.
            }
        }
        return $this;
    }
}
