<?php

namespace App\Controller\Admin;

use App\Entity\Restaurant;
use App\Entity\Specialite;
use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Role;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Paiement;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // Cette méthode va chercher ton fichier twig personnalisé
        return $this->render('admin/my_dashboard.html.twig', [
            'total_restaurants' => 7, 
            'total_events' => 12,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        // On garde la version "stylée" avec l'icône et le mode plein écran
        return Dashboard::new()
            ->setTitle('<img src="https://em-content.zobj.net/source/microsoft-teams/363/hamburger_1f354.png" width="30"> Miam Up Admin')
            ->renderContentMaximized()
            ->setLocales(['fr', 'en'])
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Miam Up Core');
        yield MenuItem::linkToCrud('Restaurants', 'fas fa-utensils', Restaurant::class);
        yield MenuItem::linkToCrud('Spécialités', 'fas fa-pizza-slice', Specialite::class);

        yield MenuItem::section('Utilisateurs & Droits');
        yield MenuItem::linkToCrud('Membres', 'fas fa-user', Utilisateur::class);
        yield MenuItem::linkToCrud('Rôles système', 'fas fa-user-shield', Role::class);

        yield MenuItem::section('Social & Modération');
        yield MenuItem::linkToCrud('Conversations', 'fas fa-comments', Conversation::class);
        yield MenuItem::linkToCrud('Messages', 'fas fa-envelope', Message::class);
        yield MenuItem::linkToCrud('Événements', 'fas fa-calendar-alt', Evenement::class);

        yield MenuItem::section('Business');
        yield MenuItem::linkToCrud('Paiements', 'fas fa-credit-card', Paiement::class);
    }
}