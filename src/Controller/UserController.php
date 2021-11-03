<?php
/**
 * @author Lukas Rynt
 */

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserRepository $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/users", name="users_index")
     * @return Response
     */
    public function index(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->render('users/index.html.twig', ['users' => $users]);
    }
}