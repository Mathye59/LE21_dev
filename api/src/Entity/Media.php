<?php

namespace App\Entity;

use App\Entity\Carrousel;
use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MediaRepository::class)]
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
     * @var Collection<int, Carrousel>
     */
    #[ORM\OneToMany(targetEntity: Carrousel::class, mappedBy: 'media')]
    private Collection $carrousel;

    /**
     * Fichier uploadé (non persisté). 
     */
    #[Vich\UploadableField(mapping: 'media_files', fileNameProperty: 'filename')]
    private ?File $file = null;

    public function __construct()
    {
        $this->carrousel = new ArrayCollection();
        $this->date = new \DateTimeImmutable(); // initialise la date d’ajout
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return Collection<int, Carrousel>
     */
    public function getCarrousel(): Collection
    {
        return $this->carrousel;
    }

    public function addCarrousel(Carrousel $carrousel): self
    {
        if (!$this->carrousel->contains($carrousel)) {
            $this->carrousel->add($carrousel);
            $carrousel->setMedia($this);
        }
        return $this;
    }

    public function removeCarrousel(Carrousel $carrousel): self
    {
        if ($this->carrousel->removeElement($carrousel)) {
            if ($carrousel->getMedia() === $this) {
                $carrousel->setMedia(null);
            }
        }
        return $this;
    }

    /** Vich: setter du fichier uploadé */
    public function setFile(?File $file): void
    {
        $this->file = $file;

        if ($file !== null) {
            // important: déclenche une mise à jour pour que Vich traite le fichier
            $this->date = new \DateTimeImmutable();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }
    public function __toString(): string
    {
        return (string) $this->getFilename();
    }

}

