<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/utilisateur')]
class UtilisateurController extends AbstractController
{
    #[Route('/inscription', name: 'app_utilisateur_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Si déjà connecté, rediriger vers l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $nom = trim($request->request->get('nom', ''));
            $prenom = trim($request->request->get('prenom', ''));
            $email = trim($request->request->get('email', ''));
            $pseudo = trim($request->request->get('pseudo', ''));
            $telephone = trim($request->request->get('telephone', ''));
            $password = $request->request->get('password', '');
            $passwordConfirm = $request->request->get('password_confirm', '');

            // Validation
            $errors = [];

            if (empty($nom) || empty($prenom) || empty($email) || empty($pseudo) || empty($telephone) || empty($password)) {
                $errors[] = 'Tous les champs sont obligatoires.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'adresse email n\'est pas valide.';
            }

            if (strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            // Validation mot de passe fort : majuscule, minuscule, chiffre, symbole
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&).';
            }

            if ($password !== $passwordConfirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            // Vérifier que l'email n'existe pas déjà
            $existingUser = $entityManager->getRepository(Utilisateur::class)->findOneBy([
                'emailUtilisateur' => $email
            ]);
            if ($existingUser) {
                $errors[] = 'Un compte avec cet email existe déjà.';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('utilisateur/register.html.twig', [
                    'last_nom' => $nom,
                    'last_prenom' => $prenom,
                    'last_email' => $email,
                    'last_pseudo' => $pseudo,
                    'last_telephone' => $telephone,
                ]);
            }

            // Créer l'utilisateur
            $utilisateur = new Utilisateur();
            $utilisateur->setNomUtilisateur($nom);
            $utilisateur->setPrenomUtilisateur($prenom);
            $utilisateur->setEmailUtilisateur($email);
            $utilisateur->setPseudoUtilisateur($pseudo);
            $utilisateur->setNumUtilisateur((int) $telephone);

            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $password);
            $utilisateur->setPassword($hashedPassword);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        return $this->render('utilisateur/register.html.twig', [
            'last_nom' => '',
            'last_prenom' => '',
            'last_email' => '',
            'last_pseudo' => '',
            'last_telephone' => '',
        ]);
    }

    #[Route('/connexion', name: 'app_utilisateur_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si déjà connecté, rediriger vers l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier email saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('utilisateur/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
