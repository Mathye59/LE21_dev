<?php

namespace App\Controller\Admin;

use App\Entity\ArticleAccueil;
use App\Entity\ArticleBlog;
use App\Entity\Carrousel;
use App\Entity\CarrouselSlide;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Entreprise;
use App\Entity\Flash;
use App\Entity\Media;
use App\Entity\Tatoueur;
// use App\Entity\User; // décommente si tu as créé User

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Redirige par défaut vers le carrousel (modifiable)
        $url = $this->adminUrlGenerator
            ->setController(CarrouselCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Site Le 21 — Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // 1) PAGE D’ACCUEIL
        yield MenuItem::section('Page d’accueil');
        yield MenuItem::linkToCrud('Carrousel', 'fa fa-images', Carrousel::class);
        // Si tu veux éditer les slides via un CRUD séparé, laisse visible ; sinon masque-le
        // yield MenuItem::linkToCrud('Slides', 'fa fa-image', CarrouselSlide::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Articles d’accueil', 'fa fa-newspaper', ArticleAccueil::class);

        // 2) PAGE FLASH
        yield MenuItem::section('Page Flash');
        yield MenuItem::linkToCrud('Flashes', 'fa fa-bolt', Flash::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Categorie::class);
        yield MenuItem::linkToCrud('Médias', 'fa fa-image', Media::class);

        // 3) BLOG
        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Articles de blog', 'fa fa-pen', ArticleBlog::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', Commentaire::class);

        // 4) ADMIN
        yield MenuItem::section('Administration')->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Entreprise', 'fa fa-building', Entreprise::class)->setPermission('ROLE_ADMIN');
        // if (class_exists(User::class)) {
        //     yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user-shield', User::class)->setPermission('ROLE_ADMIN');
        // }
        yield MenuItem::linkToCrud('Tatoueurs', 'fa fa-user', Tatoueur::class)->setPermission('ROLE_ADMIN');
    }
}
