<?php

namespace App\Controller\Admin;

use App\Entity\Restaurant;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Identifiant (caché dans le formulaire)
            IdField::new('id')->hideOnForm(),

            // CHAMP OBLIGATOIRE (Le seul en 'NO' dans ton Workbench)
            TextField::new('nomRestaurant', 'Nom du restaurant'),

            // CHAMPS FACULTATIFS (En 'YES' dans ton Workbench)
            TextField::new('adresseRestaurant', 'Adresse postale'),
            TextField::new('siret', 'Numéro SIRET'),
            TextField::new('numTelRestaurant', 'Téléphone'),
            TextField::new('siteWeb', 'Site internet'),
            
            // CONFIGURATION DU LOGO
            // Assure-toi que le dossier public/uploads/logos existe !
            ImageField::new('logo', 'Logo du restaurant')
                ->setBasePath('uploads/logos')
                ->setUploadDir('public/uploads/logos')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            // DESCRIPTION (Format texte riche)
            TextEditorField::new('descriptionRestaurant', 'Description'),

            // RELATIONS (ManyToMany)
            // Note : On utilise 'specialite' sans le 's' car c'est le nom de la propriété dans ton entité
            AssociationField::new('specialite', 'Spécialités culinaires'),
            AssociationField::new('type', 'Types d\'établissement'),
            AssociationField::new('regimes', 'Régimes alimentaires'),
            AssociationField::new('amenagements', 'Aménagements dispo'),

            // GÉOLOCALISATION
            NumberField::new('latitude')->hideOnIndex(),
            NumberField::new('longitude')->hideOnIndex(),

            // PROPRIÉTAIRE
            AssociationField::new('proprietaire', 'Propriétaire')->hideOnIndex(),
        ];
    }
}