<?php


namespace App\Services;


use App\DTO\LoginDto;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait; // for redirect...

    /** @var RouterInterface  */
    private $router;
    /** @var FormFactoryInterface  */
    private $formFactory;
    /** @var SecurityService  */
    private $securityService;

    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        SecurityService $securityService)
    {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->securityService = $securityService;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate("app_login");
    }

    public function supports(Request $request)
    {
        return $request->attributes->get("_route") === "app_login"
            && $request->isMethod("POST");
    }

    public function getCredentials(Request $request)
    {
        $dto = new LoginDto($this->formFactory, $request);
        $form = $dto->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $request->getSession()->set(Security::LAST_USERNAME, $dto->getUserName());
            return $dto;
        }
        throw new InvalidCsrfTokenException("Invalid form.");
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var LoginDto $credentials */

        $user = $this->securityService
            ->findUserByEmail($credentials->getUserName());

        if ($user) return $user;

        throw new CustomUserMessageAuthenticationException("bad email");
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        /** @var LoginDto $credentials */
        return $this->securityService
            ->isPasswordValid($user, $credentials->getPassword());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        if ($targetPath)
        {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->router->generate("app_login"));
    }

// https://symfony.com/doc/current/security.html
// https://symfony.com/doc/current/security/form_login_setup.html
// https://symfony.com/doc/4.4/security/guard_authentication.html
// https://ourcodeworld.com/articles/read/1057/how-to-implement-your-own-user-authentication-system-in-symfony-4-3-part-1-creating-a-custom-user-class
// https://github.com/FriendsOfSymfony/FOSUserBundle
}