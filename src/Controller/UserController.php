<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/edituser/{id}', name: 'app_edit')]
    public function editUser(int $id, Request $request, UserRepository $repository): Response
    {
        $user = $repository->find($id);
    
        if ($user === null) {
            throw $this->createNotFoundException('Book not found.');
        }
    
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
    
            return $this->redirectToRoute('admin_users'); // Redirect to the author list page after editing.
        }
    
        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(), // Pass the 'form' variable to the template
            'user' => $user,
        ]);
    }
}