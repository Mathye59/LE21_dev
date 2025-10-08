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

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->syncCarrousel($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->syncCarrousel($args);
    }

    private function syncCarrousel(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Media) {
            return;
        }

        $em = $args->getObjectManager();

        // Déjà lié au carrousel ?
        $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $entity]);
        if ($exists) {
            return;
        }

        // Position suivante
        $pos = (int) $this->carrouselRepo->getMaxPosition() + 1;

        $c = new Carrousel();
        $c->setMedia($entity);
        $c->setTitle(pathinfo((string) $entity->getFilename(), PATHINFO_FILENAME) ?: '');
        $c->setIsActive(false);         // Inactif par défaut comme avant
        $c->setPosition($pos);

        $em->persist($c);
        $em->flush();                   // flush ici car on est en postPersist/postUpdate
    }
}
