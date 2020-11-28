<?php


namespace App\Services;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

class MyListenerService // vs. Subscriber (the subscribers know what event they subscribe to)
{
    /** @var ParameterBag */
    private $parameterBag;
    /** @var Environment */
    private $twig;

    /**
     * MyListenerService constructor.
     * @param ParameterBagInterface $parameterBag
     * @param Environment $twig
     */
    public function __construct(ParameterBagInterface $parameterBag, Environment $twig)
    {
        $this->parameterBag = $parameterBag;
        $this->twig = $twig;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) return;

        // handle CORS pre-flight request
        $request = $event->getRequest();
        $method = $request->getRealMethod();

        if ($method == "OPTIONS")
        {
            $event->setResponse(new Response(""));
        }

        $this->twig->addGlobal("menu", $this->parameterBag->get("menu"));
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) return;

        $response = $event->getResponse();
        // TODO: allow only custom endpoints/origins to access my API
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Methods", "GET,POST,OPTIONS");
        $response->headers->set("Access-Control-Allow-Headers", "Content-Type,Authorization");
    }
}