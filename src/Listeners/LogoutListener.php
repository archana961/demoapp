<?php

namespace App\Listeners;

use App\Entity\EventLogs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutListener implements LogoutHandlerInterface {
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logout(Request $request, Response $response, TokenInterface $token) {

        $user = $token->getUser();
        if($user){
            
            $eventLog = new EventLogs();
            $eventLog->setEventType($this->entityManager->getReference('App\Entity\EventType', 3));
            $eventLog->setUser($user);
            $this->entityManager->persist($eventLog);
            $this->entityManager->flush();

        }  

    }
}