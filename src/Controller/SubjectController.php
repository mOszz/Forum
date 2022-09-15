<?php

namespace App\Controller;

use App\Repository\UserRepository;

use App\Repository\PostRepository;

use App\Entity\Subject;
use App\Repository\SubjectRepository;
use App\Form\SubjectType;

use App\Entity\Forum;
use App\Repository\ForumRepository;
use App\Form\ForumType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class SubjectController extends AbstractController
{
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

	#[Route('/subject/{id}', name: 'app_subject')]
    public function index(Subject $subject, PostRepository $repo) {

        $subject->getId();
        $posts = $repo->findBySubjectId($subject); 

        return $this->render('subject/subject.html.twig', compact('subject', 'posts'));
    }

    #[Route('/create', name: 'app_subject_create')]
    public function create(Request $request, EntityManagerInterface $em) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $subject = new Subject;
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($subject);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('subject/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/createForum', name: 'app_newForum')]
    public function newForum(Request $request, EntityManagerInterface $em) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $forum = new Forum;
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($forum);
            $em->flush();
            return $this->redirectToRoute('app_home');
        }
        return $this->render('forum/create.html.twig', ['form' => $form->createView()]);
    }

} 
