<?php

namespace App\Entity;

use App\Entity\Carrousel;
use App\Repository\MediaRepository;
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
     * Fichier uploadé (non persisté).
     * Vich mettra à jour $filename via fileNameProperty.
     */
    #[Vich\UploadableField(mapping: 'media_files', fileNameProperty: 'filename')]
    private ?File $file = null;

    /**
     * Lien 1–1 vers la fiche Carrousel correspondante.
     * Ici, Media est le côté inversé (mappedBy='media').
     * Le côté propriétaire doit être: Carrousel::$media (#[ORM\OneToOne(inversedBy: 'carrousel', ...)]).
     */
    #[ORM\OneToOne(mappedBy: 'media', targetEntity: Carrousel::class, cascade: ['remove'])]
    private ?Carrousel $carrousel = null;

    public function __construct()
    {
        // initialise la date d’ajout
        $this->date = new \DateTimeImmutable();
    }

    /* ------------------------- Getters / Setters ------------------------- */

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
     * Setter Vich : assigne le fichier uploadé (non mappé).
     * Met à jour la date pour déclencher le traitement par Vich.
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;

        if (null !== $file) {
            // force une mise à jour pour que Vich traite le fichier
            $this->date = new \DateTimeImmutable();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getCarrousel(): ?Carrousel
    {
        return $this->carrousel;
    }

    public function setCarrousel(?Carrousel $carrousel): self
    {
        // gère la relation bidirectionnelle proprement
        if ($this->carrousel === $carrousel) {
            return $this;
        }

         // détache l’ancien côté inverse proprement
        if ($this->carrousel !== null && $this->carrousel->getMedia() === $this) {
            $this->carrousel->setMedia(null);
        }

        $this->carrousel = $carrousel;


        // rattache le nouveau côté propriétaire
        if ($carrousel && $carrousel->getMedia() !== $this) {
            $carrousel->setMedia($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) ($this->filename ?? 'media#'.$this->id);
    }
}

