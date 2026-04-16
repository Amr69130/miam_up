<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Entity\Restaurant;
use App\Entity\Evenement;
use App\Entity\Paiement;
use App\Entity\Specialite;
use App\Entity\Regime;
use App\Entity\Amenagement;
use App\Entity\Type;
use App\Entity\Amitie; // <--- AJOUTÉ
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- Roles ---
        $roleUser = new Role();
        $roleUser->setNomRole('Utilisateur');
        $manager->persist($roleUser);

        $roleAdmin = new Role();
        $roleAdmin->setNomRole('Admin');
        $manager->persist($roleAdmin);

        // --- Specialités, Régimes, Aménagements, Types (Ton code reste identique) ---
        $specialites = [];
        foreach (['Italien', 'Chinois', 'Français', 'Asiatique', 'Fast Food', 'Japonais', 'Burger'] as $s) {
            $spec = new Specialite();
            $spec->setNomSpecialite($s);
            $manager->persist($spec);
            $specialites[] = $spec;
        }

        $regimes = [];
        foreach (['Vegan', 'Sans gluten', 'Halal', 'Végétarien', 'Cacher'] as $r) {
            $regime = new Regime();
            $regime->setNomRegime($r);
            $manager->persist($regime);
            $regimes[] = $regime;
        }

        $amenagements = [];
        foreach (['Espace jeux enfants', 'Rampe handicap', 'Ascenseur', 'Table à langer'] as $a) {
            $am = new Amenagement();
            $am->setNomAmenagement($a);
            $manager->persist($am);
            $amenagements[] = $am;
        }

        $types = [];
        foreach (['Restaurant', 'Café', 'Bistro', 'Gastro', 'Fast-Food'] as $t) {
            $type = new Type();
            $type->setNomType($t);
            $manager->persist($type);
            $types[] = $type;
        }

        // --- Restaurants ---
        $restaurantsData = [
            ['nom'=>'EcullyTacos', 'adresse'=>'20 Avenue des sources, 69130 Écully', 'lat'=>45.7777, 'lng'=>4.7795],
            ['nom'=>'EcullyPizza', 'adresse'=>'480 Avenue Ben Gourion, 69009 Lyon', 'lat'=>45.78828, 'lng'=>4.78720],
            ['nom'=>'Lyon9Tacos', 'adresse'=>'43 Rue du Sergent Michel Berthet, 69009 Lyon', 'lat'=>45.7809, 'lng'=>4.8049],
            ['nom'=>'Lyon9Pizza', 'adresse'=>'30 Rue du Sergent Michel Berthet, 69009 Lyon', 'lat'=>45.7813, 'lng'=>4.8038],
            ['nom'=>'RilleuxTacos', 'adresse'=>'8 Avenue des Nations, 69140 Rillieux-la-Pape', 'lat'=>45.8270, 'lng'=>4.8990],
            ['nom'=>'RilleuxPizza', 'adresse'=>'20 Avenue des Nations, 69140 Rillieux-la-Pape', 'lat'=>45.8250, 'lng'=>4.9040],
            ['nom'=>'Lyon8Tacos', 'adresse'=>'120 Avenue Paul Santy, 69008 Lyon', 'lat'=>45.7286, 'lng'=>4.8743],
            ['nom'=>'Lyon8Pizza', 'adresse'=>'130 Avenue Paul Santy, 69008 Lyon', 'lat'=>45.7285, 'lng'=>4.8766],
            ['nom'=>'VilletteTacos', 'adresse'=>'30 rue des Serventières, 38200 Villette‑de‑Vienne', 'lat'=>45.5894, 'lng'=>4.9150],
            ['nom'=>'VilettePizza', 'adresse'=>'147 route de Marennes, 38200 Villette‑de‑Vienne', 'lat'=>45.5920, 'lng'=>4.9170],
            ['nom'=>'TEST_ORANGE_BURGERS', 'adresse'=>'Test Today', 'lat'=>45.7785, 'lng'=>4.7800],
            ['nom'=>'TEST_VERT_SUSHI', 'adresse'=>'Test Soon', 'lat'=>45.7820, 'lng'=>4.8060],
        ];

        foreach ($restaurantsData as $data) {
            $resto = new Restaurant();
            $resto->setNomRestaurant($data['nom']);
            $resto->setAdresseRestaurant($data['adresse']);
            $resto->setLatitude($data['lat']);
            $resto->setLongitude($data['lng']);
            $resto->setNumTelRestaurant($faker->phoneNumber);
            $resto->setSiret($faker->numerify('##############'));
            $resto->setDescriptionRestaurant('Restaurant spécialisé en ' . $data['nom']);
            foreach ($faker->randomElements($specialites, rand(1,3)) as $s) $resto->addSpecialite($s);
            foreach ($faker->randomElements($regimes, rand(1,3)) as $r) $resto->addRegime($r);
            foreach ($faker->randomElements($amenagements, rand(1,3)) as $a) $resto->addAmenagement($a);
            foreach ($faker->randomElements($types, rand(1,1)) as $t) $resto->addType($t);
            $manager->persist($resto);
        }

        // --- Utilisateurs (20 classiques) ---
        $prenoms = ['Mohamed','Sofia','Liam','Emma','Mateo','Chloé','Yara','Noah','Aaliyah','Lucas','Jade','Rayan','Fatima','Nathan','Aya','Ethan','Lea','Ibrahim','Mila','Adam'];
        $utilisateurs = [];

        for ($i = 0; $i < 20; $i++) {
            $user = new Utilisateur();
            $user->setNomUtilisateur($faker->lastName);
            $user->setPrenomUtilisateur($prenoms[$i]);
            $user->setNumUtilisateur($faker->numberBetween(1000, 9999));
            $user->setEmailUtilisateur($faker->unique()->email);
            $user->setPseudoUtilisateur(strtolower($user->getPrenomUtilisateur()).$faker->unique()->numberBetween(1,99));
            $user->setLatitude($faker->latitude(45.72, 45.78));
            $user->setLongitude($faker->longitude(4.80, 4.86));
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            $user->addRole($roleUser);
            $manager->persist($user);
            $utilisateurs[] = $user;
        }

        // --- Admin (Sera l'utilisateur de test principal) ---
        $admin = new Utilisateur();
        $admin->setNomUtilisateur('Admin');
        $admin->setPrenomUtilisateur('Super');
        $admin->setNumUtilisateur(0);
        $admin->setEmailUtilisateur('admin@miamup.com');
        $admin->setPseudoUtilisateur('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $admin->addRole($roleAdmin);
        $admin->addRole($roleUser);
        $manager->persist($admin);
        // On n'oublie pas de l'ajouter au tableau pour les amitiés
        $utilisateurs[] = $admin;

        // --- AMITIÉS (Semaine 4) ---

        // 1. On crée 3 amitiés déjà ACCEPTÉES pour l'admin
        // L'admin (dernier du tableau) sera ami avec les 3 premiers users
        for ($i = 0; $i < 3; $i++) {
            $amitie = new Amitie();
            $amitie->setUtilisateurDemandeur($utilisateurs[$i]);
            $amitie->setUtilisateurReceveur($admin);
            $amitie->setStatut('accepte');
            $manager->persist($amitie);
        }

        // 2. On crée 3 demandes EN ATTENTE pour l'admin
        // Les users 4, 5 et 6 demandent l'admin en ami
        for ($i = 4; $i < 7; $i++) {
            $demande = new Amitie();
            $demande->setUtilisateurDemandeur($utilisateurs[$i]);
            $demande->setUtilisateurReceveur($admin);
            $demande->setStatut('en_attente');
            $manager->persist($demande);
        }

        // --- Paiements & Flush Final ---
        foreach ($utilisateurs as $user) {
            $paiement = new Paiement();
            $paiement->setDatePaiement($faker->dateTimeThisYear);
            $paiement->setIdTransaction($faker->uuid);
            $paiement->setMontant($faker->numberBetween(10,200));
            $paiement->setStatusPaiement($faker->randomElement(['En attente','Payé','Annulé']));
            $paiement->setUtilisateur($user);
            $manager->persist($paiement);
        }

        $manager->flush();
    }
}
