<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class UsersController extends AbstractController
{
    #[Route('/users', name: 'admin_users')]
    public function index(UserRepository $userRepository , Request  $request): Response
    {

        $searchQuery = $request->query->get('q');
        $orderBy = $request->query->get('orderBy', 'name'); // Default sorting by name

        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('a')->from(User::class, 'a');

        // Apply search query if provided
        if ($searchQuery) {
            $queryBuilder->andWhere('a.name LIKE :searchQuery')
                ->setParameter('searchQuery', '%'.$searchQuery.'%');
        }

          // Apply sorting
          $queryBuilder->orderBy('a.'.$orderBy);

          $query = $queryBuilder->getQuery();

        $users = $query->getResult();

        return $this->render('admin/user/admin.html.twig', [
            'users' => $users,
            'orderBy' => $orderBy,
            'searchQuery' => $searchQuery
        ]);
    }

    #[Route('/edituser/{id}', name: 'app_edit_user')]
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
    
        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(), // Pass the 'form' variable to the template
            'user' => $user,
        ]);
    }

    #[Route('/admin/deleteuser/{id}', name: 'app_delete_user')]
public function delete(int $id, UserRepository $repository): Response
{
    $user = $repository->find($id);

    if ($user) {
        // Delete the author from the database.
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    return $this->redirectToRoute('admin_users');
}
#[Route('/admin/adduser', name: 'app_add_user', methods: ['GET', 'POST'])]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
{
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
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

        return $this->redirectToRoute('admin_users');
    }

    return $this->render('admin/user/add_user.html.twig', [
        'userForm' => $form->createView(),
    ]);
}

    
}