<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="blog")
     */
    public function index(ArticleRepository $repository)
    {
        $articles = $repository->findAll();

        return $this->render('blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_new")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$article) {
            $article = new Article();
            $article->setCreatedAt(new \DateTime());
            $article->setUser($this->getUser());
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('blog_view', ['id' => $article->getId()]);
        }
        if (!$article->getId()) {
            return $this->render('blog/new.html.twig', [
                'form' => $form->createView()
            ]);
        } else {
            return $this->render('blog/edit.html.twig', [
                'article' => $article,
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/blog/{id}", name="blog_view")
     */
    public function view(Article $article, Request $request, EntityManagerInterface $manager)
    {
        $comment = new Comment();
        $comment->setUser($this->getUser())
            ->setArticle($article)
            ->setCreatedAt(new \DateTime());
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('blog_view', ['id' => $article->getId()]);
        } else {
            return $this->render('blog/view.html.twig', [
                'article' => $article,
                'form' => $form->createView()
            ]);
        }
    }
}
