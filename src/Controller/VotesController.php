<?php


namespace App\Controller;


use App\DTO\VoteSystemTextDto;
use App\Entity\Choice;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VotesController
 * @package App\Controller
 * @Route(path="votes/")
 */
class VotesController extends AbstractController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * ForumController constructor.
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $em)
    {
        $this->formFactory = $formFactory;
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="questions", name="listQuestionsAction")
     */
    public function listQuestionsAction(Request $request) : Response
    {
        $questions = $this->getDoctrine()
            ->getRepository(Question::class)
            ->findAll();

        $dto = new VoteSystemTextDto($this->formFactory, $request);
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $question = new Question($dto->getText());
            $this->em->persist($question);
            $this->em->flush();

            $this->addFlash("notice", "Question successfully added.");
            return $this->redirectToRoute("listQuestionsAction");
        }

        return $this->render("votes/questions.html.twig",
            ["questions" => $questions, "form" => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param int $questionId
     * @return Response
     * @Route(path="votes/questions/{questionId}", name="listChoicesAction", requirements={"id": "\d+"})
     */
    public function listChoicesAction(Request $request, int $questionId) : Response
    {
        $question = $this->getDoctrine()
            ->getRepository(Question::class)
            ->find($questionId);

        if (!$question) throw $this->createNotFoundException();

        $dto = new VoteSystemTextDto($this->formFactory, $request);
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $choice = new Choice();
            $choice
                ->setChoText($dto->getText())
                ->setChoQuestion($question);

            $this->em->persist($choice);
            $this->em->flush();

            $this->addFlash("notice", "Choice successfully added.");
            return $this->redirectToRoute("listChoicesAction",
                ["questionId" => $questionId]);
        }

        return $this->render("votes/choices.html.twig",
            ["choices" => $question->getQuChoices(),
                "form" => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     * @Route(path="vote/{id}", name="voteAction", requirements={"id": "\d+"})
     */
    public function voteAction(Request $request, int $id) : Response
    {
        $choice = $this->getDoctrine()
            ->getRepository(Choice::class)
            ->find($id);

        if (!$choice) throw $this->createNotFoundException();

//        $dql = "UPDATE App:Choice c SET c.cho_numvotes = c.cho_numvotes + 1
//            WHERE c.cho_id = :choiceId";
//        $query =
//            $em->createQuery($dql)->setParameter("choiceId", $choice);

        $query = $this->em->getRepository(Choice::class)
            ->createQueryBuilder("c")
            ->update()
            ->set("c.cho_numvotes", "c.cho_numvotes + 1")
            ->where("c.cho_id = :choiceId")
            ->setParameter("choiceId", $id)
            ->getQuery();

        $rows = $query->execute();

        $choice->updateTimestamps();
        $this->em->persist($choice);
        $this->em->flush();

        $this->addFlash("notice", "VOTED FOR '{$choice}', AFFECTED ROWS: {$rows}");

        return $this->redirectToRoute("listChoicesAction",
            ["questionId" => $choice->getChoQuestion()->getQuId()]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     * @Route(path="choices/delete/{id}", name="deleteChoiceAction", requirements={"id" : "\d+"})
     */
    public function deleteChoiceAction(Request $request, int $id) : Response
    {
        $choice = $this->getDoctrine()
            ->getRepository(Choice::class)
            ->find($id);

        if (!$choice) throw $this->createNotFoundException();

        $this->em->remove($choice);
        $this->em->flush();

        return $this->redirectToRoute("listChoicesAction",
            ["questionId" => $choice->getChoQuestion()->getQuId()]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     * @Route(path="questions/delete/{id}", name="deleteQuestionAction", requirements={"id": "\d+"})
     */
    public function deleteQuestion(Request $request, int $id) : Response
    {
        $question = $this->getDoctrine()
            ->getRepository(Question::class)
            ->find($id);

        foreach ($question->getQuChoices() as $choice)
        {
            $this->em->remove($choice);
        }
        $this->em->remove($question);
        $this->em->flush();

        return $this->redirectToRoute("listQuestionsAction");
    }
}