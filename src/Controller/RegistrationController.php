<?php

namespace App\Controller;

use App\Entity\EventLogs;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\SecurityAuthenticator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\UserType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class RegistrationController extends AbstractController
{
    
    /**
     * @Route("/registration", name="registration")
     */
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ( $request->isXmlHttpRequest() ) {
            if (!$form->isValid()) {
                return $this->json(
                    ['result' => 0,
                    'message' => 'Invalid form',
                    'data' => $this->getErrorMessages($form)]
                );
            }
         
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['profileFile']->getData();
            if ($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads/profiles';
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $user->setProfileImage($newFilename);
            }
            
            // Encode the new users password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

            // Set their role
            $user->setRoles(['ROLE_USER']);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $lastId = $user->getId();

            if($lastId){
                $em1 = $this->getDoctrine()->getManager();
                $eventLog = new EventLogs();
                $eventLog->setEventType($em1->getReference('App\Entity\EventType', 2));
                $eventLog->setUser($user);
                $em1->persist($eventLog);
                $em1->flush();

            }
            
            $token = new UsernamePasswordToken(
                $user,
                $form->get('password')->getData(),
                'main',
                $user->getRoles()
            );

            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            return $this->json(
                ['result' => 1,
                'message' => 'ok',
                'data' => '',
                'redirectTo' =>  $this->generateUrl('dashboard') ]);
    
            return $this->redirectToRoute('app_login');
            
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/check_email", name="check_email")
     */
    public function actionCheckEmailExists(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            //check if the email exists
            $email = $request->get('email');
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $repository = $this->getDoctrine()->getRepository(User::class);
            $exists = $repository->checkEmailExists($email);
            if($exists){
                return $this->json(['result' => 0, 'message' => 'This email is already in use.']);
            }
            return $this->json(['result' => 1]);
        }
    }

    // Generate an array contains a key -> value with the errors where the key is the name of the form field
    protected function getErrorMessages(\Symfony\Component\Form\Form $form) 
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}