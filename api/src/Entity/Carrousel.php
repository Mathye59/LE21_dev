<?php

namespace App\Entity;

use App\Repository\CarrouselRepository;
use App\Entity\Media;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CarrouselRepository::class)]
#[ORM\Table(name: 'carrousel')]
#[ORM\UniqueConstraint(name: 'uniq_carrousel_position', columns: ['position'])]
class Carrousel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Média lié (création possible inline grâce au cascade persist)
    #[ORM\ManyToOne(targetEntity: Media::class, inversedBy: 'carrousel', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    // Ordre dans le carrousel (1..N)
    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    public function getId(): ?int { return $this->id; }

    public function getMedia(): ?Media { return $this->media; }
    public function setMedia(?Media $media): self { $this->media = $media; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): self { $this->position = $position; return $this; }
}
