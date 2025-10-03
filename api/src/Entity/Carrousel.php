<?php
// src/Entity/Carrousel.php
namespace App\Entity;

use App\Repository\CarrouselRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: CarrouselRepository::class)]
#[ORM\Table(name: 'carrousel')]
#[ORM\UniqueConstraint(name: 'uniq_carrousel_media', columns: ['media_id'])] // ← 1 seule ligne par média
class Carrousel
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Media::class, inversedBy: 'carrousel')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Media $media = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function getId(): ?int { return $this->id; }
   public function getMedia(): ?Media
    {
        return $this->media;
    }
    public function setMedia(?Media $media): self // <- accepte null
    {
        $this->media = $media;

        // maintient la bidirection
        if ($media && $media->getCarrousel() !== $this) {
            $media->setCarrousel($this);
        }

        return $this;
    }

    public function isActive(): bool 
    {
         return $this->isActive; 
    }

    public function setIsActive(bool $active): self
    {
         $this->isActive = $active; return $this;
    }

    public function getPosition(): ?int
    {
         return $this->position; 
    }

    public function setPosition(?int $p): self 
    { 
        $this->position = $p; return $this; 
    }

    public function getTitle(): ?string 
    { 
        return $this->title; 
    }

    public function setTitle(?string $t): self 
    { 
        $this->title = $t; return $this;
    }

    public function getMediaFilename(): ?string
    {
        return $this->media?->getFilename();
    }

    // (facultatif si tu veux parfois le chemin complet ailleurs)
    public function getMediaPublicPath(): ?string
    {
        return $this->media ? '/uploads/media/'.$this->media->getFilename() : null;
    }
}
