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

        // --- Restaurants ---
        $restaurants = [];
        for ($i = 0; $i < 7; $i++) {
            $resto = new Restaurant();
            $resto->setNomRestaurant($faker->company);
            $resto->setAdresseRestaurant($faker->address);
            $resto->setNumTelRestaurant($faker->phoneNumber);
            $resto->setSiret($faker->numerify('##############')); // 14 chiffres pour un SIRET
            $resto->setDescriptionRestaurant($faker->text(200));

            // AJOUT DES COORDONNÉES GPS (Générées par Faker)
            $resto->setLatitude($faker->latitude(42.0, 51.0));  // Latitude moyenne France
            $resto->setLongitude($faker->longitude(-4.0, 8.0)); // Longitude moyenne France

            // Plusieurs spécialités, régimes, aménagements, types
            foreach ($faker->randomElements($specialites, rand(1, 3)) as $s) $resto->addSpecialite($s);
            foreach ($faker->randomElements($regimes, rand(1, 3)) as $r) $resto->addRegime($r);
            foreach ($faker->randomElements($amenagements, rand(1, 3)) as $a) $resto->addAmenagement($a);
            foreach ($faker->randomElements($types, rand(1, 3)) as $t) $resto->addType($t);

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
            $user->setEmailUtilisateur($faker->email);
            $user->setPseudoUtilisateur(strtolower($user->getPrenomUtilisateur()).$faker->numberBetween(1,99));
            $user->setLatitude($faker->latitude(42.0, 51.0));
            $user->setLongitude($faker->longitude(-4.0, 8.0));
            
            // Hasher le mot de passe (mot de passe par défaut : "password")
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            
            $user->addRole($roleUser);
            $manager->persist($user);
            $utilisateurs[] = $user;
        }

        // --- Admin unique ---
        $admin = new Utilisateur();
        $admin->setNomUtilisateur('Admin');
        $admin->setPrenomUtilisateur('Super');
        $admin->setNumUtilisateur(0);
        $admin->setEmailUtilisateur('admin@miamup.com');
        $admin->setPseudoUtilisateur('admin');
        
        // Hasher le mot de passe admin (mot de passe : "admin123")
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);
        
        $admin->addRole($roleAdmin);
        $admin->addRole($roleUser);
        $manager->persist($admin);
        $utilisateurs[] = $admin;

        // --- Événements ---
        for ($i = 0; $i < 8; $i++) {
            $evenement = new Evenement();
            $evenement->setDateEvenement($faker->dateTimeThisYear);
            $evenement->setDescriptionEvenement($faker->sentence);
            $evenement->setNombrePlaces($faker->numberBetween(10, 50));
            $evenement->setRestaurant($restaurants[array_rand($restaurants)]);

            // Participants multiples
            foreach ($faker->randomElements($utilisateurs, rand(2, 6)) as $u) {
                $evenement->addUtilisateur($u);
            }

            $manager->persist($evenement);
        }

        // --- Paiements ---
        foreach ($utilisateurs as $user) {
            $paiement = new Paiement();
            $paiement->setDatePaiement($faker->dateTimeThisYear);
            $paiement->setIdTransaction($faker->uuid);
            $paiement->setMontant($faker->numberBetween(10, 200));
            $paiement->setStatusPaiement($faker->randomElement(['En attente','Payé','Annulé']));
            $paiement->setUtilisateur($user);
            $manager->persist($paiement);
        }

        // --- Conversations & Messages ---
        for ($i = 0; $i < 5; $i++) {
            $conv = new Conversation();
            $conv->setNomConversation('Discussion '.$i);
            $conv->setDateCreation($faker->dateTimeThisYear);

            // 3 à 6 membres aléatoires
            foreach ($faker->randomElements($utilisateurs, rand(3,6)) as $u) {
                $conv->addMembre($u);
            }

            $manager->persist($conv);

            // 3 à 7 messages par conversation
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
