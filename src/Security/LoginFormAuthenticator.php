<?php

namespace App\Security;

use App\Entity\EventLogs;
use App\Entity\EventType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventTypeRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;
    private RouterInterface $router;
    private EntityManagerInterface $entityManager;
    private EventTypeRepository $eventTypeRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, RouterInterface $router, EntityManagerInterface $entityManager, EventTypeRepository $eventTypeRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->eventTypeRepository = $eventTypeRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                // optionally pass a callback to load the User manually
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

                if (!$user) {
                    throw new UserNotFoundException();
                }

                return $user;
            }),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new RememberMeBadge(),
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //log event to database
        $user = $token->getUser();
        if($user){
            $eventLog = new EventLogs();
            $eventLog->setEventType($this->entityManager->getReference('App\Entity\EventType', 1));
            $eventLog->setUser($user);
            $this->entityManager->persist($eventLog);
            $this->entityManager->flush();

        }  
        if ( $request->isXmlHttpRequest() ) {

			$response = new Response( json_encode( [ 'result' => 1, 'redirectTo' =>  $this->urlGenerator->generate('dashboard') ] ) );
			$response->headers->set( 'Content-Type', 'application/json' );
			return $response;

		}else{
            if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
                return new RedirectResponse($targetPath);
            }
    
            // For example:
            return new RedirectResponse($this->urlGenerator->generate('dashboard'));
            throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ( $request->isXmlHttpRequest() ) {
            $errorMessage = ($exception instanceof UsernameNotFoundException)? 'User not found.': 'Invalid Password entered.';
			$response = new Response( json_encode( ['result' => 0, 'message' => $errorMessage] ) );
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;

		} else {
			$request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
            return new RedirectResponse(
                $this->router->generate('app_login')
            );
        }
        
        
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
