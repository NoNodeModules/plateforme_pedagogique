<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Form\QuestionnaireType;
use App\Form\QuestionType;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher")
 */
class TeacherController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="teacher_index")
     * @param QuestionnaireRepository $repository
     * @return ResponseAlias
     */
    public function index(QuestionnaireRepository $repository): ResponseAlias
    {
        $questionnaires = $repository->findAll();
        $user = $this->getUser();

        return $this->render(
            'teacher/index.html.twig',
            [
                'questionnaires' => $questionnaires,
                'teacher' => $user,
            ]
        );
    }

    /**
     * @Route("/questionnaire/create", name="questionnaire_create")
     * @param Request $request
     * @return RedirectResponse|ResponseAlias
     */
    public function createQuestionnaire(Questionnaire $questionnaire = null, Request $request)
    {
        if (!$questionnaire) {
            $questionnaire = new Questionnaire();
        }

        $questionnaire->setTeacher($this->getUser());
        $form = $this->createForm(QuestionnaireType::class, $questionnaire);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($questionnaire);
            $this->em->flush();

            $this->addFlash('success', 'questionnaire ajouté avec succès');

            return $this->redirectToRoute(
                'question_create',
                [
                    'id' => $questionnaire->getId(),
                ]
            );
        }

        return $this->render(
            'questionnaire/new.html.twig',
            [
                'questionnaire' => $questionnaire,
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * @Route ("/questionnaire/{id}", name="questionnaire_edit", methods={"GET","POST"})
     */
    public function editQuestionnaire(Questionnaire $questionnaire, Request $request)
    {
        $questionnaire->setTeacher($this->getUser());
        $form = $this->createForm(QuestionnaireType::class, $questionnaire);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($questionnaire);
            $this->em->flush();

            $this->addFlash('success', 'questionnaire modifié avec succès');

            return $this->redirectToRoute(
                'teacher_index',
                [
                    'id' => $questionnaire->getId(),
                ]
            );
        }

        return $this->render(
            'questionnaire/edit.html.twig',
            [
                'questionnaire' => $questionnaire,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route ("/questionnaire/{id}", name="questionnaire_delete")
     * @param Questionnaire $questionnaire
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteQuestionnaire(Questionnaire $questionnaire, Request $request)
    {
        if ($this->isCsrfTokenValid('delete'.$questionnaire->getId(), $request->get('_token'))) {
            $this->em->remove($questionnaire);
            $this->em->flush();
            $this->addFlash('succes', 'questionnaire supprimé avec succès');
        }

        return $this->redirectToRoute('teacher_index');
    }


    /**
     * @Route("/questionnaire/{id}/question/create", name="question_create")
     * @param Question|null $question
     * @param Request $request
     * @return RedirectResponse|ResponseAlias
     */
    public function createQuestion(Question $question = null, Request $request)
    {
        $questionnaire_id = $request->attributes->get("id");
        $em = $this->getDoctrine()->getManager();
        $questionnaire = $em->getRepository(Questionnaire::class)->findOneById($questionnaire_id);

        if (!$question) {
            $question = new Question();
        }

        $question->setQuestionnaire($questionnaire);
        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($question);
            $this->em->flush();

            $this->addFlash('success', 'question ajouté avec succès');

            return $this->redirectToRoute('question_create', [
                'id' => $questionnaire_id
            ]);
        }

        return $this->render(
            'question/new.html.twig',
            [
                'question' => $question,
                'form' => $form->createView(),
            ]
        );
    }
}
