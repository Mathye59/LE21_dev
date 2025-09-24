<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[Vich\Uploadable]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    /**
     * @var Collection<int, CarrouselSlide>
     */
    #[ORM\OneToMany(targetEntity: CarrouselSlide::class, mappedBy: 'media')]
    private Collection $carrouselSlides;
    
    // ajout Vich pour image
     #[Vich\UploadableField(mapping: 'media_files', fileNameProperty: 'filename')]
    private ?File $file = null; // <— NON persisté, juste pour l’upload

    public function __construct()
    {
        $this->carrouselSlides = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

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

    /**
     * @return Collection<int, CarrouselSlide>
     */
    public function getCarrouselSlides(): Collection
    {
        return $this->carrouselSlides;
    }

    public function addCarrouselSlide(CarrouselSlide $carrouselSlide): static
    {
        if (!$this->carrouselSlides->contains($carrouselSlide)) {
            $this->carrouselSlides->add($carrouselSlide);
            $carrouselSlide->setMedia($this);
        }

        return $this;
    }

    public function removeCarrouselSlide(CarrouselSlide $carrouselSlide): static
    {
        if ($this->carrouselSlides->removeElement($carrouselSlide)) {
            // set the owning side to null (unless already changed)
            if ($carrouselSlide->getMedia() === $this) {
                $carrouselSlide->setMedia(null);
            }
        }

        return $this;
    }
    // ajout Vich pour image
    public function setFile(?File $file): void 
    { $this->file = $file; 
    }

    public function getFile(): ?File 
    { return $this->file; 
    }
}
