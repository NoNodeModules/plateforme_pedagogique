<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use App\Repository\ClassroomRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\QuestionnaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class LessonController
 * This class manage the lessons
 * @Route("/lesson")
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TEACHER')")
 * @package App\Controller
 */
class LessonController extends AbstractController
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
     * @Route("/{id}", name="lesson_index", requirements={"id":"\d+"})
     * @param \App\Repository\ClassroomRepository $repository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Lesson $lesson
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ClassroomRepository $repository, Request $request, Lesson $lesson): Response
    {
        $classroom_id = $request->query->get('classroom');
        $classroom = $repository->findOneById($classroom_id);


        return $this->render('lesson/index.html.twig', [
            'lesson' => $lesson,
            'questionnaires' => $lesson->getQuestionnaires(),
            'classroom' => $classroom
        ]);
    }

    /**
     * @Route("/list", name="list_lessons")
     * @param \App\Repository\LessonRepository $repository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listLessons(LessonRepository $repository, Request $request): Response
    {
        $classroom_id = $request->query->get('classroom');
        $lessons = $repository->findAll();

        return $this->render('lesson/list.html.twig', [
            'lessons' => $lessons,
            'classroom_id' => $classroom_id,
        ]);
    }

    /**
     * Add questionnaire direct to a lesson
     * @Route("/add", name="add_questionnaire_lesson")
     * @ParamConverter("questionnaire", class="\App\Entity\Questionnaire")
     * @param \App\Repository\LessonRepository $lessonRepo
     * @param \App\Repository\ClassroomRepository $classroomRepo
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addQuestionnaireToLesson(
        LessonRepository $lessonRepo,
        QuestionnaireRepository $questionnaireRepo,
        ClassroomRepository $classroomRepo,
        Request $request
    ): RedirectResponse {
        // find lesson
        $lesson_id = $request->query->get('lesson');
        $lesson = $lessonRepo->findOneById($lesson_id);

        // find questionnaire
        $questionnaire_id = $request->query->get('questionnaire');
        $questionnaire = $questionnaireRepo->findOneById($questionnaire_id);

        // find classroom
        $classroom_id = $request->query->get('classroom');
        $classroom = $classroomRepo->findOneById($classroom_id);

        $lesson->addQuestionnaire($questionnaire);
        $this->em->persist($lesson);
        $this->em->flush();
        $this->addFlash('success', 'Module ajouté avec succès.');

        return $this->redirectToRoute(
            'lesson_index',
            [
                'id' => $lesson_id,
                'classroom' => $classroom
            ]
        );
    }

    /**
     * @Route("/create", name="lesson_create")
     * @ParamConverter("lesson", class="\App\Entity\Lesson")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createLesson(Request $request, ClassroomRepository $repository): Response
    {
        $lesson = new Lesson();
        $classroom = $repository->findOneById($request->query->get('classroom'));
        if ($classroom) {
            $lesson->addClassroom($classroom);
        }

        // Add actual date and the user
        $lesson->setDateCreation(new \DateTime());
        $lesson->addUser($this->getUser());
        $lesson->setCreator($this->getUser()->getUsername());
        $form = $this->createForm(LessonType::class, $lesson);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($lesson);
            $this->em->flush();
            $this->addFlash('success', 'Module ajouté avec succès.');

            return $this->redirectToRoute(
                'lesson_index',
                [
                    'id' => $lesson->getId(),
                ]
            );
        }

        return $this->render(
            'lesson/new.html.twig',
            [
                'classroom' => $classroom,
                'lesson' => $lesson,
                'form' => $form->createView(),
                'user' => $this->getUser(),
            ]
        );
    }

    /**
     * @Route ("/edit/{id}", name="lesson_edit", methods={"GET","POST"})
     * @param \App\Entity\Lesson|null $lesson
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editLesson(Lesson $lesson, Request $request): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Module modifiée avec succès.');

            return $this->redirectToRoute(
                'classroom_index',
                [
                    'id' => $request->query->get('classroom')
                ]
            );
        }

        return $this->render(
            'lesson/edit.html.twig',
            [
                'lesson' => $lesson,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route ("/delete/{id}", name="lesson_delete", methods={"DELETE"})
     * @param \App\Entity\Lesson $lesson
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteLesson(Lesson $lesson, Request $request): RedirectResponse
    {
        // Check the token
        if ($this->isCsrfTokenValid(
            'delete' . $lesson->getId(),
            $request->get('_token')
        )) {
            $this->em->remove($lesson);
            $this->em->flush();
            $this->addFlash('success', 'Module supprimée avec succès.');
        }

        return $this->redirectToRoute(
            'classroom_index',
            [
                'id' => $request->query->get('classroom')
            ]
        );
    }
}
