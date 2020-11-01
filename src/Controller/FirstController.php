<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/first")
 */
class FirstController extends AbstractController
{
    /**
     * @Route(path="/demo/{id}/{lang}", name="demoRoute", requirements={ "id" : "\d+" })
     */
    public function MyFirstRequest(Request $request, int $id, string $lang="hu") : Response
    {
        $str = "Hello, symfony! - ";
        $str .= "ID = {$id} - ";
        $str .= "LANG = {$lang}";
        return new Response($str);
    }
}
