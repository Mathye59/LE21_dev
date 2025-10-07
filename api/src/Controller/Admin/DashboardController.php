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
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
            ->setTitle('Site Le 21 — Administration')
            ->setLocales(['fr']);
            
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // 1) PAGE D’ACCUEIL
        yield MenuItem::section('Page d’accueil');
        yield MenuItem::linkToCrud('Carrousel', 'fa fa-images', Carrousel::class); //gestion des slides inclus
        yield MenuItem::linkToCrud('Articles d’accueil', 'fa fa-newspaper', ArticleAccueil::class); //gestion des articles de l'accueil

        // 2) PAGE FLASH
        yield MenuItem::section('Page Flash');
        yield MenuItem::linkToCrud('Flashes', 'fa fa-bolt', Flash::class); //gestion Flashs
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Categorie::class); //gestion catégories

        // 3) MÉDIAS 
        yield MenuItem::section('Médias');
        yield MenuItem::linkToCrud('Médias', 'fa fa-image', Media::class); //gestion médias

        // 4) BLOG
        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Articles de blog', 'fa fa-pen', ArticleBlog::class);    //gestion articles de blog
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', Commentaire::class);   //gestion commentaires des articles (validation)

        // 5) ADMIN
        if ( $this->isGranted('ROLE_ADMIN') ) 
        {   //seulement si ROLE_ADMIN
             yield MenuItem::section('Administration');//->setPermission('ROLE_ADMIN')
            yield MenuItem::linkToCrud('Entreprise', 'fa fa-building', Entreprise::class);//->setPermission('ROLE_ADMIN')
            yield MenuItem::linkToCrud('Tatoueurs', 'fa fa-user', Tatoueur::class);//->setPermission('ROLE_ADMIN')
        }
       
    }
    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('styles/admin.css');
    }
}
