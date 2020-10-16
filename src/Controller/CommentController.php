<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\EditCommentFormType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/create/{id}", name="comment_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request, Post $post)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $comment->setPost($post);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectToRoute('post_details', [
            'id' => $post->getId()
        ]);
    }

    /**
     * @param $id
     * @param CommentRepository $commentRepository
     * @return Response
     * @Route("/comment/edit/{id}" , name="comment_edit")
     */
    public function edit($id, CommentRepository $commentRepository)
    {
        $comment = $commentRepository->find($id);
        $editForm = $this->createForm(EditCommentFormType::class, $comment);

        return $this->render('comment/edit.html.twig', [
            'editForm'  => $editForm->createView(),
            'comment' => $comment
        ]);
    }

    /**
     * @param Comment $comment
     * @Route("/comment/update/{id}", name="comment_update", methods={"POST"})
     */
    public function update(Comment $comment, Request $request)
    {
        $form = $this->createForm(EditCommentFormType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $newComment = $form->getData()->getContent();
            $comment->setContent($newComment);
            $em->flush();
            $this->addFlash('success', 'Comment has been changed!');
        }

        return $this->redirectToRoute('post_details', [
            'id' => $comment->getPost()->getId()
        ]);
    }

    /**
     * @param Comment $comment
     * @Route("/comment/delete/{id}", name="comment_delete")
     */
    public function delete(Comment $comment)
    {
        $postId = $comment->getPost()->getId();
        if ($comment->getUser()->getId() === $this->getUser()->getId() || $this->getUser()->getId() === $comment->getPost()->getUser()->getId()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($comment);
            $manager->flush();
            $this->addFlash('success', 'You\'ve deleted a comment.');
        } else {
            $this->addFlash('warning', 'You don\'t have permissions to delete this comment.');
        }

        return $this->redirectToRoute('post_details', [
            'id' => $postId
        ]);
    }
}
