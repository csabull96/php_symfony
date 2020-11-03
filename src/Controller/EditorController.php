<?php


namespace App\Controller;

use App\DTO\DtoBase;
use App\DTO\LoginDto;
use App\DTO\TextDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditorController extends AbstractController
{
    private $usersFile = "../templates/editor/users.txt";
    private $dataFile = "../templates/editor/data.txt";

    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * EditorController constructor.
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="editorCreate", path="editor/create")
     */
    public function createUsersFileAction(Request $request) : Response {
        $users = "";
        $users .= "csabull96\t".password_hash("password", PASSWORD_DEFAULT)."\n";
        $users .= "admin\t".password_hash("password", PASSWORD_DEFAULT)."\n";
        file_put_contents($this->usersFile, $users);
        return new Response(nl2br($users));
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="editorLogout", path="editor/logout")
     */
    public function logoutAction(Request $request) : Response {
        // $this->get() - get a service from IoC container!
        $this->get("session")->clear();
        $this->addFlash("notice", "Logged Out");
        return $this->redirectToRoute("editor");
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="editor", path="editor")
     */
    public function editorAction(Request $request) : Response {
        $twigParams = ["form" => null, "filetext" => "", "sessiontext" => ""];
        //return $this->render("editor/editor.html.twig", $twigParams);

        $twigParams["sessiontext"] = $this->get("session")->get("sessiontext");
        if (file_exists($this->dataFile))
            $twigParams["filetext"] = file_get_contents($this->dataFile);

        $sessionUser = $this->get("session")->get("userName");
        if ($sessionUser)
            $dto = new TextDto($this->get("form.factory"), $request);
        else
            $dto = new LoginDto($this->get("form.factory"), $request);

        // q1: different scenarios
        //      1) rendering view depending whether user is logged in or not
        //      2) form is submitted (how does the form know which action to execute?) then logs in
        //      then redirected to editor again
        //      3) render with text input this time
        // q2: motivation behind the form builders

        /** @var DtoBase $dto */
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($sessionUser)
                $this->processTextInput($dto, $form);
            else
                $this->processLoginInput($dto);

            return $this->redirectToRoute("editor");
        }

        $twigParams["form"] = $form->createView();
        return $this->render("editor/editor.html.twig", $twigParams);
    }

    private function processTextInput(TextDto $dto, FormInterface $form)
    {
        $text = $dto->getTextContent();
        if ($form->get("saveToSession")->isClicked()) {
            $this->get("session")->set("sessiontext", $text);
            $this->addFlash("notice", "Saved to file");
        }
        else {
            file_put_contents($this->dataFile, $text);
            $this->addFlash("notice", "Saved to session");
        }
    }

    private function processLoginInput(LoginDto $dto)
    {
        $userName = $dto->getUserName();
        $password = $dto->getPassword();

        $users = file($this->usersFile, FILE_IGNORE_NEW_LINES);
        foreach ($users as $user) {
            $user_details = explode("\t", $user);
            if ($userName == $user_details[0] && password_verify($password, $user_details[1])) {
                $this->get("session")->set("userName", $user_details[0]);
                $this->addFlash("notice", "Successful login");
                return;
            }
        }
        $this->addFlash("notice", "Login failed");
    }
}