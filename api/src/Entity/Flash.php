<?php

namespace App\Entity;


use App\Repository\FlashRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\StatutFlash; 
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Categorie;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
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

    #[ORM\Column(type: 'string', enumType: StatutFlash::class)]
    private ?StatutFlash $statut = StatutFlash::DISPONIBLE;

    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'flashes')]
    #[ORM\JoinTable(name: 'flash_categorie')]
    private Collection $categories;

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

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    #[Assert\GreaterThanOrEqual(0)]
    private ?float $prix = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }
    
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

    public function getStatut(): ?StatutFlash 
    { 
        return $this->statut; 
    }

    public function setStatut(StatutFlash $s): self 
    { 
        $this->statut = $s; return $this; 
    }

        /** @return Collection<int, Categorie> */
    public function getCategories(): Collection { return $this->categories; }

    public function addCategory(Categorie $c): self
    {
        if (!$this->categories->contains($c)) {
            $this->categories->add($c);
        }
        return $this;
    }

    public function removeCategory(Categorie $c): self
    {
        $this->categories->removeElement($c);
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }
}
