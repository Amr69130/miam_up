<?php

namespace App\Command;

use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update-restaurant-5',
    description: 'Met à jour les données du restaurant 5',
)]
class UpdateRestaurantCommand extends Command
{
    private EntityManagerInterface $em;
    private RestaurantRepository $repo;

    public function __construct(EntityManagerInterface $em, RestaurantRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $restaurant = $this->repo->find(5);

        if (!$restaurant) {
            $output->writeln("Le restaurant avec l'ID 5 n'existe pas.");
            return Command::FAILURE;
        }

        $restaurant->setNomRestaurant('La Belle Époque');
        $restaurant->setAdresseRestaurant('30 Rue de la République, 69002 Lyon');
        $restaurant->setNumTelRestaurant('04 78 42 56 89');
        $restaurant->setSiret('12345678900012');
        $restaurant->setSiteWeb('https://www.labelleepoque-lyon.fr');
        $restaurant->setHoraires('Ouvert · Ferme à 23:00 · Plus d\'heures');
        $restaurant->setNoteConsommateurs(4.8);
        $restaurant->setDescriptionRestaurant(
            "La Belle Époque est une véritable institution située au cœur de la Presqu'île de Lyon.\n\n" .
            "Nous vous proposons une cuisine bistronomique française raffinée, élaborée avec des produits locaux et de saison. " .
            "Notre chef revisite les grands classiques régionaux dans un cadre chic et chaleureux, idéal pour vos repas d'affaires " .
            "ou vos dîners en amoureux.\n\n" .
            "Venez profiter de notre terrasse ombragée en été ou de l'ambiance cosy près de la cheminée en hiver."
        );
        $restaurant->setLogo('https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?q=80&w=2070&auto=format&fit=crop');

        $this->em->flush();

        $output->writeln("Le restaurant avec l'ID 5 a bien été mis à jour !");

        return Command::SUCCESS;
    }
}