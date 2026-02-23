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
use App\Entity\Conversation;
use App\Entity\Message;
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

        // --- Specialités ---
        $specialites = [];
        foreach (['Italien', 'Chinois', 'Français', 'Asiatique', 'Fast Food'] as $s) {
            $spec = new Specialite();
            $spec->setNomSpecialite($s);
            $manager->persist($spec);
            $specialites[] = $spec;
        }

        // --- Régimes ---
        $regimes = [];
        foreach (['Vegan', 'Sans gluten', 'Halal', 'Végétarien', 'Cacher'] as $r) {
            $regime = new Regime();
            $regime->setNomRegime($r);
            $manager->persist($regime);
            $regimes[] = $regime;
        }

        // --- Aménagements ---
        $amenagements = [];
        foreach (['Espace jeux enfants', 'Rampe handicap', 'Ascenseur', 'Table à langer'] as $a) {
            $am = new Amenagement();
            $am->setNomAmenagement($a);
            $manager->persist($am);
            $amenagements[] = $am;
        }

        // --- Types ---
        $types = [];
        foreach (['Restaurant', 'Café', 'Bar', 'Bistro', 'Gastro'] as $t) {
            $type = new Type();
            $type->setNomType($t);
            $manager->persist($type);
            $types[] = $type;
        }

        // --- Restaurants fixes pour tests AUTOUR DE MOI (2 par zone) ---
        $restaurantsData = [
            // ECULLY - Sauvegarde
            ['nom'=>'EcullyTacos', 'adresse'=>'20 Avenue des sources, 69130 Écully', 'lat'=>45.7777, 'lng'=>4.7795],
            ['nom'=>'EcullyPizza', 'adresse'=>'480 Avenue Ben Gourion, 69009 Lyon', 'lat'=>45.78828, 'lng'=>4.78720],

            // LYON 9 - École
            ['nom'=>'Lyon9Tacos', 'adresse'=>'43 Rue du Sergent Michel Berthet, 69009 Lyon', 'lat'=>45.7809, 'lng'=>4.8049],
            ['nom'=>'Lyon9Pizza', 'adresse'=>'30 Rue du Sergent Michel Berthet, 69009 Lyon', 'lat'=>45.7813, 'lng'=>4.8038],

            // RILLIEUX - Les Alagniers
            ['nom'=>'RilleuxTacos', 'adresse'=>'8 Avenue des Nations, 69140 Rillieux-la-Pape', 'lat'=>45.8270, 'lng'=>4.8990],
            ['nom'=>'RilleuxPizza', 'adresse'=>'20 Avenue des Nations, 69140 Rillieux-la-Pape', 'lat'=>45.8250, 'lng'=>4.9040],

            // LYON 8 - Mermoz / Etats-Unis
            ['nom'=>'Lyon8Tacos', 'adresse'=>'120 Avenue Paul Santy, 69008 Lyon', 'lat'=>45.7286, 'lng'=>4.8743],
            ['nom'=>'Lyon8Pizza', 'adresse'=>'130 Avenue Paul Santy, 69008 Lyon', 'lat'=>45.7285, 'lng'=>4.8766],

            // Villette-de-Vienne
            ['nom'=>'VilletteTacos', 'adresse'=>'30 rue des Serventières, 38200 Villette‑de‑Vienne', 'lat'=>45.5894, 'lng'=>4.9150],
            ['nom'=>'VilettePizza', 'adresse'=>'147 route de Marennes, 38200 Villette‑de‑Vienne', 'lat'=>45.5920, 'lng'=>4.9170],
        ];

        $restaurants = [];
        foreach ($restaurantsData as $data) {
            $resto = new Restaurant();
            $resto->setNomRestaurant($data['nom']);
            $resto->setAdresseRestaurant($data['adresse']);
            $resto->setLatitude($data['lat']);
            $resto->setLongitude($data['lng']);
            $resto->setNumTelRestaurant($faker->phoneNumber);
            $resto->setSiret($faker->numerify('##############'));
            $resto->setDescriptionRestaurant('Restaurant test pour ' . $data['nom']);
            // Relations aléatoires
            foreach ($faker->randomElements($specialites, rand(1,3)) as $s) $resto->addSpecialite($s);
            foreach ($faker->randomElements($regimes, rand(1,3)) as $r) $resto->addRegime($r);
            foreach ($faker->randomElements($amenagements, rand(1,3)) as $a) $resto->addAmenagement($a);
            foreach ($faker->randomElements($types, rand(1,3)) as $t) $resto->addType($t);

            $manager->persist($resto);
            $restaurants[] = $resto;
        }

        // --- Utilisateurs ---
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

        // --- Admin ---
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
        $utilisateurs[] = $admin;

        // --- Événements ---
        for ($i = 0; $i < 8; $i++) {
            $evenement = new Evenement();
            $evenement->setDateEvenement($faker->dateTimeThisYear);
            $evenement->setDescriptionEvenement($faker->sentence);
            $evenement->setNombrePlaces($faker->numberBetween(10,50));
            $evenement->setRestaurant($restaurants[array_rand($restaurants)]);
            foreach ($faker->randomElements($utilisateurs, rand(2,6)) as $u) $evenement->addUtilisateur($u);
            $manager->persist($evenement);
        }

        // --- Paiements ---
        foreach ($utilisateurs as $user) {
            $paiement = new Paiement();
            $paiement->setDatePaiement($faker->dateTimeThisYear);
            $paiement->setIdTransaction($faker->uuid);
            $paiement->setMontant($faker->numberBetween(10,200));
            $paiement->setStatusPaiement($faker->randomElement(['En attente','Payé','Annulé']));
            $paiement->setUtilisateur($user);
            $manager->persist($paiement);
        }

        // --- Conversations & Messages ---
        for ($i = 0; $i < 5; $i++) {
            $conv = new Conversation();
            $conv->setNomConversation('Discussion '.$i);
            $conv->setDateCreation($faker->dateTimeThisYear);
            foreach ($faker->randomElements($utilisateurs, rand(3,6)) as $u) $conv->addMembre($u);
            $manager->persist($conv);
            for ($j = 0; $j < rand(3,7); $j++) {
                $msg = new Message();
                $msg->setConversation($conv);
                $msg->setExpediteur($utilisateurs[array_rand($utilisateurs)]);
                $msg->setDateMessage($faker->dateTimeThisYear);
                $msg->setContenu($faker->text(50));
                $msg->setStatusMessage($faker->randomElement(['lu','non lu']));
                $manager->persist($msg);
            }
        }

        $manager->flush();
    }
}
