<?php


namespace App\Controller;

use App\DTO\ChangePasswordDto;
use App\DTO\DtoBase;
use App\DTO\LoginDto;
use App\DTO\RegisterDto;
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

        $twigParams["sessiontext"] = $this->get("session")->get("sessiontext");
        if (file_exists($this->dataFile))
            $twigParams["filetext"] = file_get_contents($this->dataFile);

        $sessionUser = $this->get("session")->get("userName");
        if ($sessionUser)
            $dto = new TextDto($this->get("form.factory"), $request);
        else
            $dto = new LoginDto($this->get("form.factory"), $request);

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
            $this->addFlash("notice", "Saved to session");
        }
        else {
            $this->addFlash("notice", "Saved to file");
            file_put_contents($this->dataFile, $text);
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

    /**
     * @param Request $request
     * @return Response
     * @Route(name="registerAction", path="editor/register")
     */
    public function registerAction(Request $request) : Response {

        $dto = new RegisterDto($this->formFactory, $request);

        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $successful_registration = $this->processRegisterInput($dto);
            if ($successful_registration) {
                $this->addFlash('notice', "Successful registration as {$dto->getUsername()}");
            }
            else {
                $this->addFlash('notice', "Registration failed");
            }
            return $this->redirectToRoute('editor');
        }

        return $this->render("editor/register.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @param RegisterDto $dto
     * @return bool
     */
    private function processRegisterInput(RegisterDto $dto) : bool {
        $username = $dto->getUsername();
        $password = $dto->getPassword();

        $users = file($this->usersFile, FILE_IGNORE_NEW_LINES);
        foreach ($users as $user) {
            $user_data = explode("\t", $user);
            if ($username == $user_data[0])
                return false;
        }

        // if we made it here it means that the required username is not used yet
        $user_to_register = "{$username}\t".password_hash($password, PASSWORD_DEFAULT)."\n";
        file_put_contents($this->usersFile, $user_to_register, FILE_APPEND);

        $this->get('session')->set("userName", $username);
        return true;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(name="changePassword", path="editor/changepassword")
     */
    public function changePasswordAction(Request $request) : Response {
        $dto = new ChangePasswordDto($this->formFactory, $request);
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $this->get('session')->get('userName');
            $password_successfully_changed = $this->processChangePassword(
                $username,
                $dto->getOldPassword(),
                $dto->getNewPassword());
            if ($password_successfully_changed) {
                return $this->redirectToRoute('editor');
            }
            else {
                return $this->redirectToRoute('changePassword');
            }
        }


        return $this->render('editor/changepassword.html.twig', ["form" => $form->createView()]);
    }

    private function processChangePassword(string $username, string $old_password, string $new_password) : bool {
        $users = file($this->usersFile, FILE_IGNORE_NEW_LINES);

        $logged_in_user = "";
        $logged_in_user_data = array();

        foreach ($users as $user) {
            if (str_starts_with($user,$username)) {
                $logged_in_user = $user;
                break;
            }
        }
        if ($logged_in_user) {
            $logged_in_user_data = explode("\t", $logged_in_user);
            if (password_verify($old_password, $logged_in_user_data[1])) {
                $logged_in_user_data[1] = password_hash($new_password, PASSWORD_DEFAULT);
                $this->addFlash('notice', "Password has successfully been changed.");
                $users_as_string = file_get_contents($this->usersFile);
                $updated_logged_in_user = $username."\t".$logged_in_user_data[1];
                $updated_users_as_string = str_replace($logged_in_user,$updated_logged_in_user,$users_as_string);
                file_put_contents($this->usersFile, $updated_users_as_string);
                return true;
            }
            else {
                $this->addFlash('notice', "The current password is not correct.");
                return false;
            }
        }
        else {
            // logged in user not found

        }
        return false;
    }
}