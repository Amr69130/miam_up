<?php

namespace App\Security;

use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * Détermine si cet authenticator doit traiter la requête
     * On ne traite que les requêtes vers /connect/google/check
     */
    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    /**
     * Crée le "passeport" d'authentification
     * Récupère le profil Google et trouve/crée l'utilisateur correspondant
     */
    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // 1. Chercher si l'utilisateur existe déjà avec cet email
                $existingUser = $this->entityManager
                    ->getRepository(Utilisateur::class)
                    ->findOneBy(['emailUtilisateur' => $email]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2. Si l'utilisateur n'existe pas, on le crée automatiquement
                $user = new Utilisateur();
                $user->setEmailUtilisateur($email);
                $user->setNomUtilisateur($googleUser->getLastName() ?? 'Google');
                $user->setPrenomUtilisateur($googleUser->getFirstName() ?? 'User');
                $user->setPseudoUtilisateur($this->generatePseudo($googleUser));
                $user->setNumUtilisateur(0);

                // Générer un mot de passe aléatoire (l'utilisateur ne l'utilisera pas, il se connecte via Google)
                $randomPassword = bin2hex(random_bytes(16));
                $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                $user->setPassword($hashedPassword);

                // Ajouter le rôle Utilisateur
                $roleUser = $this->entityManager
                    ->getRepository(Role::class)
                    ->findOneBy(['nomRole' => 'Utilisateur']);

                if ($roleUser) {
                    $user->addRole($roleUser);
                }

                // Photo de profil Google
                $avatar = $googleUser->getAvatar();
                if ($avatar) {
                    $user->setPhotoProfil($avatar);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    /**
     * Que faire après une connexion réussie
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }

    /**
     * Que faire après un échec de connexion
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', 'La connexion avec Google a échoué : ' . $exception->getMessageKey());
        return new RedirectResponse($this->router->generate('app_utilisateur_login'));
    }

    /**
     * Point d'entrée : où rediriger si l'utilisateur n'est pas connecté
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_utilisateur_login'));
    }

    /**
     * Génère un pseudo à partir du profil Google
     */
    private function generatePseudo(GoogleUser $googleUser): string
    {
        $firstName = $googleUser->getFirstName() ?? 'user';
        $pseudo = strtolower($firstName) . random_int(1, 999);

        // Vérifier que le pseudo n'existe pas déjà
        $existing = $this->entityManager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['pseudoUtilisateur' => $pseudo]);

        if ($existing) {
            $pseudo .= random_int(1000, 9999);
        }

        return $pseudo;
    }
}
