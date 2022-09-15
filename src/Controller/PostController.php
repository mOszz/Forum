<?php

namespace App\Controller;

use App\Form\PostType;
use App\Entity\Post;
use App\Repository\PostRepository;

use App\Repository\UserRepository;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;

use App\Entity\Subject;
use App\Repository\SubjectRepository;

use App\Repository\ForumRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Knp\Component\Pager\PaginatorInterface;

class PostController extends AbstractController
{
	private $security;

	public function __construct(Security $security) {
		$this->security = $security;
	}

    #[Route('/', name: 'app_home')]
    public function index(PostRepository $repo, SubjectRepository $subject, ForumRepository $forum): Response
    {
        $forums = $forum->findBy(array(),array('id'=>'ASC'));
        $subjects = $subject->findAll();
    	$posts = $repo->findBy(array(),array('id'=>'DESC'),5,0);
        return $this->render('home/index.html.twig', compact('posts', 'subjects', 'forums'));
    }

    #[Route('/subject/{id}/create', name: 'app_create')]
    public function create(Request $request, EntityManagerInterface $em, Subject $subject): Response 
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    	$user = $this->security->getUser();

    	$post = new Post;
    	$post->setAuthor($user);
        $post->setSubject($subject); 
    	$form = $this->createForm(PostType::class, $post);
    	$form->handleRequest($request);

    	$post->setPostAt(new \DateTime());

    	if ($form->isSubmitted() && $form->isValid()) {
    		$em->persist($post);
    		$em->flush();
            $this->addFlash('success', 'Post created successfully!');
    		return $this->redirectToRoute('app_home');
    	}
    	return $this->render('home/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/show/{id}', name: 'app_show')]
    public function showPost(Post $post, CommentRepository $repo,Request $request, EntityManagerInterface $em) 
    {
    	$user = $this->security->getUser();

    	$comment = new Comment;
    	$comment->setPost($post);
    	$comment->setAuthor($user);
        $comment->setPostAt(new \DateTime());

    	$form = $this->createForm(CommentType::class, $comment);
    	$form->handleRequest($request);

    	if ($form->isSubmitted() && $form->isValid()) {
    		$em->persist($comment);
    		$em->flush(); 
    	}
    	$comments = $repo->findBy(array('post'=>$post->getId()));

        return $this->render('home/show.html.twig', ['post'=> $post, 'form' => $form->createView(), 'comments' => $comments]);
    }

    #[Route('/edit/{id}', name: 'app_edit')]
    public function editPost(Post $post, Request $request, EntityManagerInterface $em) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->security->getUser();

        if ($user == $post->getAuthor() || $this->isGranted('ROLE_ADMIN')) {
            
            $form = $this->createForm(PostType::class, $post);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();

                $this->addFlash('success', 'Post updated successfully!');

                return $this->redirectToRoute('app_show', ['id' => $post->getId()]);
            }


            $this->addFlash('success', 'Post deleted successfully!');
        } 
        else {
            $this->addFlash('danger', 'You can\'t edit this post!');
            return $this->redirectToRoute('app_show', ['id' => $post->getId()]);
        }

        return $this->render('home/edit.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/delete/{id}', name: 'app_delete')]
    public function deletePost(Post $post, Request $request, EntityManagerInterface $em) {
        $user = $this->security->getUser();
        if ($user == $post->getAuthor()) {
            $em->remove($post);
            $em->flush();

            $this->addFlash('success', 'Post deleted successfully!');
        } 
        elseif ($this->isGranted('ROLE_ADMIN')) {
            $em->remove($post);
            $em->flush();

            $this->addFlash('success', 'Post deleted successfully! ADMIN');
        }
        else {
            $this->addFlash('danger', 'You can\'t delete this post!');
            return $this->redirectToRoute('app_show', ['id' => $post->getId()]);
        }

        return $this->redirectToRoute('app_home');
    }
} 
