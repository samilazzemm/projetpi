<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager , SendMailService $mail , JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();


            // genreate jwt for user 
            // create header 

            $header = ['typ' =>'jwt', 'alg' =>'HS256'];

            //create payload 
            $payload = ['user_id' => $user->getId()];

            // genrate token 
            $token = $jwt->generate($header , $payload , $this->getParameter('app.jwtsecret'));

            

            // Send Mail
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte sur le site Traveling',
                'register',
                compact('user' , 'token')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/verification/{token}', name: 'app_verification')]
    public function verifyUser($token, JWTService $jwt , UserRepository $userRepository, EntityManagerInterface $em) : Response
    { 
        // verification of token 
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret')))
        {
            //nrecuperi l payload 
            $payload = $jwt->getPayload($token);

            // nrecuperi user of this token 
            $user = $userRepository->find($payload['user_id']);

            //verification of user existance 

            if($user && !$user->getIsVerified())
            {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'user verifed ');
                return $this->redirectToRoute('app_profil');

            }

        }
        $this->addFlash('danger', 'token not valide or expired');
        return $this->redirectToRoute('app_login');
    }
    #[Route('/resendverif' , name: 'app_resend')]
    public function resendVerif(JWTService $jwt ,SendMailService $mail, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if(!$user)
        {
            $this->addFlash('danger', 'you have to conenct first');
            return $this->redirectToRoute('app_login');
        }

        if($user->getIsVerified())
        {
            $this->addFlash('warning', 'this user already verified');
            return $this->redirectToRoute('app_profil');
        }

        // genreate jwt for user 
            // create header 

            $header = ['typ' =>'jwt', 'alg' =>'HS256'];

            //create payload 
            $payload = ['user_id' => $user->getId()];

            // genrate token 
            $token = $jwt->generate($header , $payload , $this->getParameter('app.jwtsecret'));

            

            // Send Mail
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte sur le site Traveling',
                'register',
                compact('user' , 'token')
            );
            $this->addFlash('succes', 'Email send');
            return $this->redirectToRoute('app_login');
    }
}