<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class UtilisateurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nomUtilisateur', 'Nom de famille'),
            TextField::new('prenomUtilisateur', 'Prénom'),
            TextField::new('pseudoUtilisateur', 'Pseudo'),
            EmailField::new('emailUtilisateur', 'Email professionnel'),
            
            // Pour les rôles, ton entité utilise la propriété $role
            AssociationField::new('role', 'Permissions / Rôles')
                ->setFormTypeOptions(['by_reference' => false]),
        ];
    }
}
