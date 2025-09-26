<?php

namespace App\Entity;

use App\Repository\CarrouselRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: CarrouselRepository::class)]
class Carrousel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(nullable: true)]
    private ?bool $autoplay = null;

    #[ORM\Column(nullable: true)]
    private ?int $intervalMs = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, CarrouselSlide>
     */
    #[ORM\OneToMany(
        mappedBy: 'carrousel',
        targetEntity: CarrouselSlide::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $carrouselSlides;

    public function __construct()
    {
        $this->carrouselSlides = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->titre ?: 'Carrousel #'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function isAutoplay(): ?bool
    {
        return $this->autoplay;
    }

    public function setAutoplay(?bool $autoplay): self
    {
        $this->autoplay = $autoplay;

        return $this;
    }

    public function getIntervalMs(): ?int
    {
        return $this->intervalMs;
    }

    public function setIntervalMs(?int $intervalMs): self
    {
        $this->intervalMs = $intervalMs;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, CarrouselSlide>
     */
    public function getCarrouselSlides(): Collection
    {
        return $this->carrouselSlides;
    }

    public function addCarrouselSlide(CarrouselSlide $carrouselSlide): self
    {
        if (!$this->carrouselSlides->contains($carrouselSlide)) {
            $this->carrouselSlides->add($carrouselSlide);
            $carrouselSlide->setCarrousel($this);
        }

        return $this;
    }

    public function removeCarrouselSlide(CarrouselSlide $carrouselSlide): self
    {
        if ($this->carrouselSlides->removeElement($carrouselSlide)) {
            // ⚠️ Si la JoinColumn dans CarrouselSlide est nullable=false,
            // NE PAS mettre à null : compte sur orphanRemoval pour supprimer l’orphelin.
            if ($carrouselSlide->getCarrousel() === $this) {
                $carrouselSlide->setCarrousel(null);
            }
        }

        return $this;
    }
}

