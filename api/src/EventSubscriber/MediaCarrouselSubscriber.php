<?php

namespace App\EventSubscriber;

use App\Entity\Media;
use App\Entity\Carrousel;
use App\Repository\CarrouselRepository;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * MediaCarrouselSubscriber
 * ------------------------------------------------------------
 * Rôle :
 *  - À chaque création ou mise à jour d’un Media, garantir qu’il existe
 *    une entrée correspondante dans le carrousel (1–1 Media <-> Carrousel).
 *  - Place le nouvel item à la fin (position = max + 1), inactif par défaut.
 *
 * Déclencheurs :
 *  - postPersist(Media) : quand un Media vient d’être inséré en BDD.
 *  - postUpdate(Media)  : quand un Media est modifié (ex. remplacement fichier).
 *
 * Choix d’implémentation :
 *  - On vérifie si le Media a déjà une entrée Carrousel ; si oui, on ne fait rien.
 *  - Sinon, on crée l’entrée Carrousel avec un titre par défaut = nom de fichier (sans extension).
 *  - On calcule la position suivante via CarrouselRepository::getMaxPosition().
 *
 * Points d’attention (à lire !) :
 *  - [FLUSH] On appelle $em->flush() *dans* un listener postPersist/postUpdate.
 *            C’est autorisé, mais ça génère un second cycle de flush.
 *            -> Évite toute boucle (ici OK, on persiste Carrousel, pas Media).
 *  - [CONCURRENCE] Deux uploads simultanés peuvent lire le même max(position) et créer un doublon.
 *                  -> Mitiger avec une contrainte UNIQUE + stratégie "tampon" au besoin (voir notes).
 *  - [UNICITÉ] Si tu garantis un carrousel unique, ajoute une contrainte UNIQUE sur `position`,
 *              ou composite si tu as des "scopes".
 */
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
     * Logique commune :
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

        // [IDEMPOTENCE] Déjà lié au carrousel ? On ne recrée pas de doublon.
        // NB : on utilise l’EM ici ; on pourrait aussi injecter un CarrouselRepository.
        $exists = $em->getRepository(Carrousel::class)->findOneBy(['media' => $entity]);
        if ($exists) {
            return; // rien à faire, une ligne Carrousel référence déjà ce Media
        }

        // [POSITION] On calcule la position suivante. getMaxPosition() doit renvoyer
        // un int (ou null si aucun élément) — ici on cast et on ajoute 1.
        $pos = (int) $this->carrouselRepo->getMaxPosition() + 1;

        // On prépare la nouvelle entrée de carrousel
        $c = new Carrousel();
        $c->setMedia($entity);

        // Titre par défaut : nom de fichier sans extension, ou string vide si indisponible
        $title = pathinfo((string) $entity->getFilename(), PATHINFO_FILENAME) ?: '';
        $c->setTitle($title);

        // Inactif par défaut (l’admin choisit ce qui est visible)
        $c->setIsActive(false);

        // Position à la fin
        $c->setPosition($pos);

        // On persiste et on flush.
        // [FLUSH EN LISTENER] : on flush ici car on est en postPersist/postUpdate (le flush initial est terminé).
        // Cela lance un second flush uniquement pour cette nouvelle entité.
        $em->persist($c);
        $em->flush();
    }
}
