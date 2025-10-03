<?php

namespace App\Entity;

use App\Repository\TatoueurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: TatoueurRepository::class)]
class Tatoueur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'tatoueurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    /**
     * @var Collection<int, ArticleBlog>
     */
    #[ORM\OneToMany(targetEntity: ArticleBlog::class, mappedBy: 'auteur')]
    private Collection $articleBlogs;

    /**
     * @var Collection<int, Flash>
     */
    #[ORM\OneToMany(targetEntity: Flash::class, mappedBy: 'tatoueur')]
    private Collection $flashes;

    /**
     * @var Collection<int, FormContact>
     */
    #[ORM\OneToMany(targetEntity: FormContact::class, mappedBy: 'tatoueur')]
    private Collection $formContacts;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $pseudo = null;

    public function __construct()
    {
        $this->articleBlogs = new ArrayCollection();
        $this->flashes = new ArrayCollection();
        $this->formContacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    /**
     * @return Collection<int, ArticleBlog>
     */
    public function getArticleBlogs(): Collection
    {
        return $this->articleBlogs;
    }

    public function addArticleBlog(ArticleBlog $articleBlog): static
    {
        if (!$this->articleBlogs->contains($articleBlog)) {
            $this->articleBlogs->add($articleBlog);
            $articleBlog->setAuteur($this);
        }

        return $this;
    }

    public function removeArticleBlog(ArticleBlog $articleBlog): static
    {
        if ($this->articleBlogs->removeElement($articleBlog)) {
            // set the owning side to null (unless already changed)
            if ($articleBlog->getAuteur() === $this) {
                $articleBlog->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Flash>
     */
    public function getFlashes(): Collection
    {
        return $this->flashes;
    }

    public function addFlash(Flash $flash): static
    {
        if (!$this->flashes->contains($flash)) {
            $this->flashes->add($flash);
            $flash->setTatoueur($this);
        }

        return $this;
    }

    public function removeFlash(Flash $flash): static
    {
        if ($this->flashes->removeElement($flash)) {
            // set the owning side to null (unless already changed)
            if ($flash->getTatoueur() === $this) {
                $flash->setTatoueur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FormContact>
     */
    public function getFormContacts(): Collection
    {
        return $this->formContacts;
    }

    public function addFormContact(FormContact $formContact): static
    {
        if (!$this->formContacts->contains($formContact)) {
            $this->formContacts->add($formContact);
            $formContact->setTatoueur($this);
        }

        return $this;
    }

    public function removeFormContact(FormContact $formContact): static
    {
        if ($this->formContacts->removeElement($formContact)) {
            // set the owning side to null (unless already changed)
            if ($formContact->getTatoueur() === $this) {
                $formContact->setTatoueur(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }
}
