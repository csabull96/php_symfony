<?php


namespace App\Controller;


use App\Entity\Car;
use App\Services\ICarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CarsController
 * @package App\Controller
 * @Route(path="/cars/")
 */
class CarsController extends AbstractController
{
    /** @var ICarService */
    private $carService;

    /**
     * CarsController constructor.
     * @param ICarService $carService
     */
    public function __construct(ICarService $carService)
    {
        $this->carService = $carService;
    }

    /**
     * @param Request $request
     * @param int $brandId
     * @return Response
     * @Route(name="listCars", path="list/{brandId}", requirements={"brandId": "\d+"})
     */
    public function listCarsAction(Request $request, int $brandId = 0) : Response
    {
        if ($brandId)
        {
            $cars = $this->carService->getCarsByBrand($brandId);
        }
        else
        {
            $isVisible = $request->query->getBoolean("isVisible");
            if ($isVisible == null)
            {
                $cars = $this->carService->getAllCars();
            }
            else
            {
                $cars = $this->carService->getCarsByVisibility($isVisible);
            }
        }

        return $this->render("cars/carlist.html.twig",
        ["cars" => $cars]);
    }

    /**
     * @param Request $request
     * @param int $carId
     * @return Response
     * @Route(name="carShow", path="show/{carId}", requirements={"carId": "\d+"})
     */
    public function showCarAction(Request $request, int $carId) : Response
    {
        $oneCar = $this->carService->getCarById($carId);
        return $this->render("cars/carshow.html.twig",
        ["car" => $oneCar]);
    }

    /**
     * @param Request $request
     * @param int $carId
     * @return Response
     * @Route(name="carDelete", path="delete/{carId}", requirements={"carId": "\d+"})
     */
    public function deleteCarAction(Request $request, int $carId) : Response
    {
        $this->carService->removeCar($carId);
        $this->addFlash("notice", "CAR REMOVED");
        return $this->redirectToRoute("listCars");
    }

    /**
     * @param Request $request
     * @param int $carId
     * @return Response
     * @Route(name="carEdit", path="edit/{carId}", requirements={"carId": "\d+"})
     */
    public function editCarAction(Request $request, int $carId = 0) : Response
    {
        // TODO convert DB entity into form DTO
        if ($carId)
        {
            $oneCar = $this->carService->getCarById($carId);
        }
        else
        {
            $oneCar = new Car();
        }

        $form = $this->carService->getCarForm($oneCar);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->carService->saveCar($oneCar);
            $this->addFlash("notice", "CAR SAVED");
            return $this->redirectToRoute("listCars");
        }

        return $this->render("cars/caredit.html.twig",
        ["form" => $form->createView()]);

    }
}