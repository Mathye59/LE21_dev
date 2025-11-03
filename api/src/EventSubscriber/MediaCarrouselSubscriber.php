<?php

namespace App\EventSubscriber;

use App\Entity\Media;
use App\Entity\Carrousel;
use App\Repository\CarrouselRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class MediaCarrouselSubscriber
{
    public function __construct(private CarrouselRepository $carrouselRepo) {}

    /** Déclenché après insertion d’un Media en BDD */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->syncCarrousel($args);
    }

    /** Déclenché après update d’un Media (ex. changement de fichier/filename) */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->syncCarrousel($args);
    }

    /**
     *  - Ignore si l’entité n’est pas un Media.
     *  - Si un Carrousel existe déjà pour ce Media → rien à faire.
     *  - Sinon, crée l’entrée Carrousel (inactif, position = max + 1, title = filename sans extension).
     */
    private function syncCarrousel(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Media) {
            return; // on ne traite que les entités Media
        }

        $em = $args->getObjectManager();

        // [IDEMPOTENCE] si déjà lié au carrousel on ne recrée pas de doublon.
        $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $entity]);
        if ($exists) {
            return; // rien à faire, une ligne Carrousel référence déjà ce Media
        }

        //On calcule la position suivante. getMaxPosition() doit renvoyer
        // un int (ou null si aucun élément) — ici on cast et on ajoute 1.
        $pos = (int) $this->carrouselRepo->getMaxPosition() + 1;

        // On prépare la nouvelle entrée de carrousel
        $c = new Carrousel();
        $c->setMedia($entity);

        // Titre par défaut : nom de fichier sans extension, ou string vide si indisponible
        $title = pathinfo((string) $entity->getFilename(), PATHINFO_FILENAME) ?: '';
        $c->setTitle($title);

        // Inactif par défaut 
        $c->setIsActive(false);

        // Position à la fin
        $c->setPosition($pos);

        // On persiste et on flush.
        // on flush ici car on est en postPersist/postUpdate (le flush initial est terminé).
        // Cela lance un second flush uniquement pour cette nouvelle entité.
        $em->persist($c);
        $em->flush();
    }
}
