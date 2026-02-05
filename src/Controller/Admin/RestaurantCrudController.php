<?php

namespace App\Controller\Admin;

use App\Entity\Restaurant;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // On cache l'ID dans le formulaire de création
            TextField::new('nom_restaurant', 'Nom de l\'établissement'),
            TextField::new('adresse_restaurant', 'Adresse'),
            TelephoneField::new('num_tel_restaurant', 'Téléphone'),
            
            // Pour le logo, on lui dit d'aller chercher dans le dossier public
            ImageField::new('logo')
                ->setBasePath('uploads/logos')
                ->setUploadDir('public/uploads/logos')
                ->setRequired(false),

            UrlField::new('site_web', 'Site internet'),
            
            // La magie d'EasyAdmin : affiche les spécialités sous forme de badges
            AssociationField::new('specialite', 'Spécialités')
                ->setFormTypeOptions(['by_reference' => false]),
        ];
    }
}