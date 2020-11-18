<?php


namespace App\Controller;


use App\Entity\Choice;
use App\Entity\Question;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VotesController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     * @Route(path="votes", name="voteListQuestions")
     */
    public function listQuestionsAction(Request $request) : Response
    {
        $questions = $this->getDoctrine()
            ->getRepository(Question::class)
            ->findAll();

        return $this->render("votes/questions.html.twig",
            ["questions" => $questions]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="votes/question/{question}", name="voteListChoices", requirements={"question": "\d+"})
     */
    public function listChoicesAction(Request $request, int $question) : Response
    {
        $questionInstance = $this->getDoctrine()
            ->getRepository(Question::class)
            ->find($question);

        if (!$questionInstance) throw $this->createNotFoundException();

        return $this->render("votes/choices.html.twig",
            ["choices" => $questionInstance->getQuChoices()]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="votes/vote/{choice}", name="voteVote", requirements={"question": "\d+"})
     */
    public function voteAction(Request $request, int $choice) : Response
    {
        $choiceInstance = $this->getDoctrine()
            ->getRepository(Choice::class)
            ->find($choice);

        if (!$choiceInstance) throw $this->createNotFoundException();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

//        $dql = "UPDATE App:Choice c SET c.cho_numvotes = c.cho_numvotes + 1
//            WHERE c.cho_id = :choiceId";
//        $query =
//            $em->createQuery($dql)->setParameter("choiceId", $choice);

        $query = $em->getRepository(Choice::class)
            ->createQueryBuilder("c")
            ->update()
            ->set("c.cho_numvotes", "c.cho_numvotes + 1")
            ->where("c.cho_id = :choiceId")
            ->setParameter("choiceId", $choice)
            ->getQuery();

        $rows = $query->execute();
        $this->addFlash("notice", "VOTED FOR '{$choiceInstance}', AFFECTED ROWS: {$rows}");

        return $this->redirectToRoute("voteListChoices",
            ["question" => $choiceInstance->getChoQuestion()->getQuId()]);

        
    }
}