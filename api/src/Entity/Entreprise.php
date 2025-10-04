<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horairesOuverture = null;

    // ajout Vich pour image
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoName = null;   // nom de fichier STOCKÉ en BDD

    #[Vich\UploadableField(mapping: 'company_logos', fileNameProperty: 'logoName')]
    private ?File $logoFile = null;     // fichier NON persisté (clé multipart: logoFile)

    /**
     * @var Collection<int, Tatoueur>
     */
    #[ORM\OneToMany(targetEntity: Tatoueur::class, mappedBy: 'entreprise')]
    private Collection $tatoueurs;

    #[ORM\Column(nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $horairesFermeture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horairePlus = null;

    public function __construct()
    {
        $this->tatoueurs = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getHorairesOuverture(): ?string
    {
        return $this->horairesOuverture;
    }

    public function setHorairesOuverture(?string $horairesOuverture): static
    {
        $this->horairesOuverture = $horairesOuverture;

        return $this;
    }

    /**
     * @return Collection<int, Tatoueur>
     */
    public function getTatoueurs(): Collection
    {
        return $this->tatoueurs;
    }

    public function addTatoueur(Tatoueur $tatoueur): static
    {
        if (!$this->tatoueurs->contains($tatoueur)) {
            $this->tatoueurs->add($tatoueur);
            $tatoueur->setEntreprise($this);
        }

        return $this;
    }

    public function removeTatoueur(Tatoueur $tatoueur): static
    {
        if ($this->tatoueurs->removeElement($tatoueur)) {
            // set the owning side to null (unless already changed)
            if ($tatoueur->getEntreprise() === $this) {
                $tatoueur->setEntreprise(null);
            }
        }

        return $this;
    }
    // ajout Vich pour image
    public function getLogoName(): ?string 
    { 
        return $this->logoName;
    }

    public function setLogoName(?string $name): static 
    { 
        $this->logoName = $name; return $this; 
    }

    public function setLogoFile(?File $file): void
    {
        $this->logoFile = $file;
    }

    public function getLogoFile(): ?File 
    { 
        return $this->logoFile;
     }

     public function __toString(): string
    {
        return (string) ($this->getNom() ?? '');
    }

     public function getTelephone(): ?string
     {
         return $this->telephone;
     }

     public function setTelephone(?string $telephone): static
     {
         $this->telephone = $telephone;

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

     public function getHorairesFermeture(): ?string
     {
         return $this->horairesFermeture;
     }

     public function setHorairesFermeture(string $horairesFermeture): static
     {
         $this->horairesFermeture = $horairesFermeture;

         return $this;
     }

     public function getHorairePlus(): ?string
     {
         return $this->horairePlus;
     }

     public function setHorairePlus(?string $horairePlus): static
     {
         $this->horairePlus = $horairePlus;

         return $this;
     }
}
