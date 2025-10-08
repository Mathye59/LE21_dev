<?php
// src/Controller/Admin/CarrouselCrudController.php

namespace App\Controller\Admin;

use App\Entity\Carrousel;
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use Symfony\Component\HttpFoundation\Response;

/**
 * ==========================================================
 *  CarrouselCrudController
 * ----------------------------------------------------------
 *  Rôle :
 *   - Gérer la liste des entrées du carrousel (une entrée = un media + méta).
 *   - Afficher une vignette + nom de fichier du média lié.
 *   - Permettre d'activer/désactiver une entrée (toggle).
 *   - Permettre de réordonner via "Monter" / "Descendre".
 *
 *  Choix techniques :
 *   - On précharge l'association media en INDEX pour éviter le N+1.
 *   - Les actions "toggle" / "moveUp" / "moveDown" sont exposées via EasyAdmin.
 *   - Les actions d'écriture sont configurées en POST (+ CSRF géré par EA).
 *   - Le swap des positions utilise une "valeur tampon" (0) pour éviter
 *     toute collision avec une contrainte d'unicité sur `position`.
 *
 *  Hypothèses côté entité Carrousel :
 *   - Propriétés : media (ManyToOne vers Media), title (string|null),
 *                  isActive (bool), position (int).
 *   - positions contiguës (1..N). Si historique douteux → penser à renuméroter.
 *
 *  À vérifier côté BDD :
 *   - Index (et idéalement unicité) sur `position` (ou `(carrousel_id, position)` si multi-scopes).
 *   - Si unicité active et SGBD MySQL/MariaDB → la variante "tampon" est **indispensable**.
 * ==========================================================
 */
class CarrouselCrudController extends AbstractCrudController
{
    /** FQCN de l'entité gérée */
    public static function getEntityFqcn(): string
    {
        return Carrousel::class;
    }

    /**
     * INDEX QueryBuilder custom :
     * - On left-join l'association "media" et on l'ajoute au SELECT
     *   pour éviter un effet N+1 lorsque l'INDEX affiche media.filename.
     */
    public function createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters): \Doctrine\ORM\QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb
            ->addSelect('m')                  // hydrate également le média
            ->leftJoin('entity.media', 'm');  // jointure sur l'association
    }

    /**
     * Réglages globaux du CRUD :
     * - Labels, titres, tri par position ASC, pagination.
     * - (Option) Permission stricte par rôle si besoin via setEntityPermission('ROLE_ADMIN')
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Carrousel')
            ->setEntityLabelInSingular('Image du carrousel')
            ->setPageTitle(Crud::PAGE_INDEX, 'Carrousel')
            ->setDefaultSort(['position' => 'ASC'])
            ->setPaginatorPageSize(15);
            // ->setEntityPermission('ROLE_ADMIN'); // décommenter si réservé aux admins
    }

    /**
     * Déclaration des champs :
     * - En INDEX : vignette (ImageField) + nom du fichier (TextField),
     *              "Actif" (Boolean), "Position" (Integer lecture seule).
     * - En FORM : titre (optionnel), actif (si souhaité), etc.
     */
    public function configureFields(string $pageName): iterable
    {
        // Vignette en INDEX (lecture seule). Nécessite que media ne soit jamais null.
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media') // doit matcher le uri_prefix du mapping Vich des Media
            ->onlyOnIndex()
            ->setSortable(false);

        // Nom de fichier du média en INDEX (lecture seule)
        yield TextField::new('media.filename', 'Média')
            ->onlyOnIndex();

        // Titre éditable (si tu le veux en base)
        yield TextField::new('title', 'Titre')
            ->hideOnIndex(); // uniquement en formulaire

        // Actif / Inactif
        yield BooleanField::new('isActive', 'Actif');

        // Position affichée uniquement en INDEX (on gère l’ordre avec actions)
        yield IntegerField::new('position', 'Position')
            ->onlyOnIndex();
    }

    /**
     * Actions personnalisées :
     * - toggle : active/désactive une entrée
     * - moveUp : échange avec la position précédente
     * - moveDown : échange avec la position suivante
     *
     * Les actions d'écriture sont configurées en POST (évite de muter en GET).
     */
    public function configureActions(Actions $actions): Actions
    {
        $toggle = Action::new('toggle', 'Activer/Désactiver', 'fa fa-toggle-on')
            ->linkToCrudAction('toggle')
            ->setHtmlAttributes(['data-action' => 'post']); // POST + CSRF par EA

        $moveUp = Action::new('moveUp', 'Monter', 'fa fa-arrow-up')
            ->linkToCrudAction('moveUp')
            ->setHtmlAttributes(['data-action' => 'post']);

        $moveDown = Action::new('moveDown', 'Descendre', 'fa fa-arrow-down')
            ->linkToCrudAction('moveDown')
            ->setHtmlAttributes(['data-action' => 'post']);

        return $actions
            ->add(Crud::PAGE_INDEX, $toggle)
            ->add(Crud::PAGE_INDEX, $moveUp)
            ->add(Crud::PAGE_INDEX, $moveDown)
            ->disable(Action::NEW)     // création pilotée par ailleurs (import Media + subscriber)
            ->disable(Action::DELETE); // garder cohérence d’ordre (active/désactive au lieu de supprimer)
    }

    /**
     * Action : toggle Actif/Inactif
     * - Bascule le booléen isActive puis flush.
     */
    public function toggle(EntityManagerInterface $em): Response
    {
        /** @var Carrousel $item */
        $item = $this->getContext()->getEntity()->getInstance();

        $item->setIsActive(!$item->isActive());
        $em->flush();

        $this->addFlash('success', sprintf(
            'L’entrée "%s" est désormais %s.',
            $item->getTitle() ?: ('#'.$item->getId()),
            $item->isActive() ? 'active' : 'inactive'
        ));

        return $this->redirect($this->getContext()->getReferrer());
    }

    /**
     * Action : déplacer vers le HAUT (position - 1)
     * - Hypothèse : positions contiguës (1..N).
     * - On va chercher l’élément à position-1 ; si absent → contiguïté cassée.
     * - On swap via méthode sécurisée (tampon) pour éviter collision d’unicité.
     */
    public function moveUp(EntityManagerInterface $em): Response
    {
        /** @var Carrousel $current */
        $current = $this->getContext()->getEntity()->getInstance();
        $pos = (int) $current->getPosition();

        if ($pos <= 1) {
            $this->addFlash('info', 'Cet élément est déjà en première position.');
            return $this->redirect($this->getContext()->getReferrer());
        }

        $repo = $em->getRepository(Carrousel::class);
        $prev = $repo->findOneBy(['position' => $pos - 1]);

        if (!$prev) {
            $this->addFlash('warning', 'Incohérence détectée dans les positions (trou au-dessus). Pense à renuméroter 1..N.');
            return $this->redirect($this->getContext()->getReferrer());
        }

        $this->swapPositionsWithBuffer($em, $current, $prev, 0);
        $this->addFlash('success', 'Élément déplacé vers le haut.');
        return $this->redirect($this->getContext()->getReferrer());
    }

    /**
     * Action : déplacer vers le BAS (position + 1)
     * - Hypothèse : positions contiguës (1..N).
     * - On va chercher l’élément à position+1 ; si absent → déjà dernier.
     */
    public function moveDown(EntityManagerInterface $em): Response
    {
        /** @var Carrousel $current */
        $current = $this->getContext()->getEntity()->getInstance();
        $pos = (int) $current->getPosition();

        $repo = $em->getRepository(Carrousel::class);
        $next = $repo->findOneBy(['position' => $pos + 1]);

        if (!$next) {
            $this->addFlash('info', 'Cet élément est déjà en dernière position.');
            return $this->redirect($this->getContext()->getReferrer());
        }

        $this->swapPositionsWithBuffer($em, $current, $next, 0);
        $this->addFlash('success', 'Élément déplacé vers le bas.');
        return $this->redirect($this->getContext()->getReferrer());
    }

    /**
     * Swap des positions avec "valeur tampon" (safe avec unicité non différable).
     *
     * Principe :
     *  1) Bascule $b sur une valeur tampon (ici 0) → libère sa position "réelle".
     *  2) Déplace $a sur l’ancienne position de $b.
     *  3) Replace $b sur l’ancienne position de $a.
     *
     * Pourquoi pas un simple double set + flush ?
     *  - Si tu as une contrainte UNIQUE, un UPDATE peut entrer en collision avant l’autre.
     *    Le tampon évite toute collision car 0 est "hors domaine" de 1..N.
     *
     * Remarques :
     *  - Transaction pour garantir l’atomicité.
     *  - Pas besoin de persist() (entités déjà "managed").
     *  - Si tu as plusieurs carrousels (scope), conserve le tampon **dans le même scope**
     *    et utilise une unicité composite `(scope_id, position)`.
     */
    private function swapPositionsWithBuffer(
        EntityManagerInterface $em,
        Carrousel $a,
        Carrousel $b,
        int $buffer = 0
    ): void {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $posA = (int) $a->getPosition();
            $posB = (int) $b->getPosition();

            // 1) Push $b sur la valeur tampon (0) → libère posB
            $b->setPosition($buffer);
            $em->flush();

            // 2) Place $a sur posB
            $a->setPosition($posB);
            $em->flush();

            // 3) Replace $b sur posA
            $b->setPosition($posA);
            $em->flush();

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    // ======================================================================
    // (Optionnel) Si tu préfères la version "sans tampon" ET sans unicité :
    // dé-commente et remplace les appels par swapPositionsTransactional(...)
    // ======================================================================
    /*
    private function swapPositionsTransactional(EntityManagerInterface $em, Carrousel $a, Carrousel $b): void
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $posA = (int) $a->getPosition();
            $posB = (int) $b->getPosition();

            $a->setPosition($posB);
            $b->setPosition($posA);

            $em->flush();
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    */

    // ======================================================================
    // (Optionnel) Action "Renuméroter 1..N" si tu veux réparer des trous :
    //  - Ajoute un bouton EA et appelle renumeroterPositions($em).
    // ======================================================================
    /*
    private function renumeroterPositions(EntityManagerInterface $em): void
    {
        $items = $em->getRepository(Carrousel::class)
            ->createQueryBuilder('c')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        $i = 1;
        foreach ($items as $it) {
            $it->setPosition($i++);
        }
        $em->flush();
    }
    */
}
