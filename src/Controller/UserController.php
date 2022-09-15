<?php

namespace App\Controller;

use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->security->getUser();

        return $this->render('user/profile.html.twig', compact('user'));
    }

    #[Route('/members', name: 'app_members')]
    public function membersList(UserRepository $user) {
        $users = $user->findAll();


        return $this->render('user/list.html.twig', compact('users'));
    }
}
