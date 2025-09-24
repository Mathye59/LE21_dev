<?php

namespace App\Entity;

use App\Repository\FlashRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: FlashRepository::class)]
class Flash
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $temps = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'flashes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'flashes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tatoueur $tatoueur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    // ajout Vich pour image
    #[Vich\UploadableField(mapping: 'flash_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    //mettre à jour la date de modification à chaque upload
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemps(): ?string
    {
        return $this->temps;
    }

    public function setTemps(?string $temps): static
    {
        $this->temps = $temps;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getTatoueur(): ?Tatoueur
    {
        return $this->tatoueur;
    }

    public function setTatoueur(?Tatoueur $tatoueur): static
    {
        $this->tatoueur = $tatoueur;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    // ajout Vich pour image
        public function setImageFile(?File $file): void
    {
        $this->imageFile = $file;
        // Important: si on remplace l'image d'un flash existant, on touche updatedAt
        if ($file !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
