<?php


namespace App\Controller;


use App\DTO\ForumDto;
use App\Model\MessageListModel;
use App\Model\MessageModel;
use App\Model\TopicListModel;
use App\Model\TopicModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * ForumController constructor.
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    private function checkLogin() : void
    {
        if (!$this->get("session")->has("userName"))
        {
            throw $this->createAccessDeniedException();
        }
    }

    private function dtoToString(ForumDto $dto) : string
    {
        $username = $this->get("session")->get("userName");
        $now = date("Y-m-d H:i:s");
        return "{$username}|{$now}|{$dto->getTextContent()}\n";
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="forum_topiclist", path="forum/topics")
     */
    public function topicListAction(Request $request) : Response
    {
        $this->checkLogin();
        $fname = "../templates/forum/topics.txt";
        $dto = new ForumDto($this->formFactory, $request, "topic");
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // TODO: nl characters as input
            file_put_contents($fname, $dto->getTextContent()."\n", FILE_APPEND);
            $this->addFlash("notice", "Topic added");
            return $this->redirectToRoute("forum_topiclist");
        }

        $topiclist = array();
        if (file_exists($fname))
        {
            $lines = file($fname, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $key => $line)
            {
                $topic = new TopicModel();
                $topic->setId($key)
                    ->setName($line);

                array_unshift($topiclist, $topic);
            }
        }

        $model = new TopicListModel();
        $model->setTopicList($topiclist)->setTopicForm($form->createView());
        return $this->render("forum/topiclist.html.twig", ["model" => $model]);
    }

    /**
     * @param Request $request
     * @param int $topic
     * @return Response
     * @Route(name="forum_messagelist", path="forum/messages/{topic}", requirements={"topic": "\d+"})
     */
    public function messageListAction(Request $request, int $topic) : Response
    {
        $this->checkLogin();

        $fname = "../templates/forum/messages_{$topic}.txt";
        $dto = new ForumDto($this->formFactory, $request, "message");
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            file_put_contents($fname, $this->dtoToString($dto), FILE_APPEND);
            $this->addFlash("notice", "Message Added");
            return $this->redirectToRoute("forum_messagelist", ["topic" => $topic]);
        }

        $messages = array();
        if (file_exists($fname))
        {
            $lines = file($fname, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line)
            {
                $data = explode("|", $line);
                $message = new MessageModel();
                $message->setUserName($data[0])
                    ->setTimeStamp($data[1])
                    ->setText($data[2]);

                array_unshift($messages, $message);
            }
        }

        $model = new MessageListModel();
        $model->setMessageList($messages)
            ->setMessageForm($form->createView());

        return $this->render("forum/messagelist.html.twig", ["model" => $model]);
    }
}