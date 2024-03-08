<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {

        $user = $this->getUser();
        return $this->render('security/profil.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user,
        ]);
    }
}
