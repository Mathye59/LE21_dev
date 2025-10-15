<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[ORM\Table(name: 'entreprise')]
#[ORM\UniqueConstraint(name: 'uniq_entreprise_singleton', columns: ['singleton_key'])]
#[Vich\Uploadable]

class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['entreprise:read'])]
    private ?int $id = null;

    // ——— CLÉ SINGLETON (impose 1 seule ligne en base) ———
    #[ORM\Column(name: 'singleton_key', length: 1, options: ['default' => 'X'])]
    private string $singletonKey = 'X';

    // ——— Champs métier ———
    #[ORM\Column(length: 50)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $facebook = null;

    #[ORM\Column(length: 250, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $horairesOuverture = null;

    #[ORM\Column(length: 255)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $horairesFermeture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $horairePlus = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $email = null;

    // ——— Vich Uploader (logo) ———
    // Nom de fichier stocké en BDD
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read'])]
    private ?string $logoName = null;

    // Fichier uploadé (clé multipart: logoFile) — non persisté
    #[Vich\UploadableField(mapping: 'company_logos', fileNameProperty: 'logoName')]
    #[Groups(['entreprise:write'])]
    private ?File $logoFile = null;

   

    // Timestamp pour déclencher Vich en cas d’upload
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Tatoueur>
     */
    #[ORM\OneToMany(targetEntity: Tatoueur::class, mappedBy: 'entreprise')]
    private Collection $tatoueurs;

    public function __construct()
    {
        $this->tatoueurs = new ArrayCollection();
    }

    // ——— Helpers front : URL publique du logo ———
    #[Groups(['entreprise:read'])]
    #[SerializedName('logoUrl')]
    public function getLogoUrl(): ?string
    {
        if (!$this->logoName) {
            return null;
        }
        // Chemin public standard (à adapter si tu utilises /uploads)
        return '/uploads/logos/' . ltrim($this->logoName, '/');
    }

    // ——— Getters/Setters ———

    public function getId(): ?int { return $this->id; }

    public function getSingletonKey(): string { return $this->singletonKey; }
    public function setSingletonKey(string $k): self { $this->singletonKey = $k; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(string $adresse): self { $this->adresse = $adresse; return $this; }

    public function getFacebook(): ?string { return $this->facebook; }
    public function setFacebook(?string $facebook): self { $this->facebook = $facebook; return $this; }

    public function getInstagram(): ?string { return $this->instagram; }
    public function setInstagram(?string $instagram): self { $this->instagram = $instagram; return $this; }

    public function getHorairesOuverture(): ?string { return $this->horairesOuverture; }
    public function setHorairesOuverture(?string $horairesOuverture): self { $this->horairesOuverture = $horairesOuverture; return $this; }

    public function getHorairesFermeture(): ?string { return $this->horairesFermeture; }
    public function setHorairesFermeture(string $horairesFermeture): self { $this->horairesFermeture = $horairesFermeture; return $this; }

    public function getHorairePlus(): ?string { return $this->horairePlus; }
    public function setHorairePlus(?string $horairePlus): self { $this->horairePlus = $horairePlus; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getLogoName(): ?string { return $this->logoName; }
    public function setLogoName(?string $name): self { $this->logoName = $name; return $this; }

    public function setLogoFile(?File $file): void
    {
        $this->logoFile = $file;
        if ($file !== null) {
            // indispensable pour déclencher l’upload Vich
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getLogoFile(): ?File { return $this->logoFile; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $t): self { $this->updatedAt = $t; return $this; }

    /**
     * @return Collection<int, Tatoueur>
     */
    public function getTatoueurs(): Collection { return $this->tatoueurs; }

    public function addTatoueur(Tatoueur $tatoueur): self
    {
        if (!$this->tatoueurs->contains($tatoueur)) {
            $this->tatoueurs->add($tatoueur);
            $tatoueur->setEntreprise($this);
        }
        return $this;
    }

    public function removeTatoueur(Tatoueur $tatoueur): self
    {
        if ($this->tatoueurs->removeElement($tatoueur)) {
            if ($tatoueur->getEntreprise() === $this) {
                $tatoueur->setEntreprise(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return (string) ($this->nom ?? '');
    }
}
