<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Flash>
     */
    #[ORM\OneToMany(targetEntity: Flash::class, mappedBy: 'categorie')]
    private Collection $flashes;

    public function __construct()
    {
        $this->flashes = new ArrayCollection();
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
            $flash->setCategorie($this);
        }

        return $this;
    }

    public function removeFlash(Flash $flash): static
    {
        if ($this->flashes->removeElement($flash)) {
            // set the owning side to null (unless already changed)
            if ($flash->getCategorie() === $this) {
                $flash->setCategorie(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getNom();
    }
}
