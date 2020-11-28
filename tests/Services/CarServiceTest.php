<?php

// php bin/console doctrine:fixture:load --no-interaction -vvv
// composer require --dev symfony/phpunit-bridge
// php bin/phpunit

// php bin/phpunit --colors=never --testdox
namespace App\Tests\Services;


use App\Entity\Brand;
use App\Entity\Car;
use App\Services\CarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CarServiceTest extends WebTestCase
{
    public function testIsTrue()
    {
        // AAA >>> Arrange + Act + Assert
        $this->assertEquals(42, 42);
        self::assertEquals(15, 14 + 1);
    }

    /** @var EntityManagerInterface */
    private static $em;
    /** @var CarService */
    private static $carService;
    /** @var Brand */
    private $nissan;

    // executed only once before the class is instantiated
    public static function setUpBeforeClass()
    {
        exec("php bin/console doctrine:fixture:load --no-interaction");
        self::bootKernel();
        self::$em = self::$kernel->getContainer()->get("doctrine")->getManager();
        self::$carService = self::$kernel->getContainer()->get("test.carservice");
        self::ensureKernelShutdown();
    }

    public function setUp(): void
    {
        $this->nissan =
            self::$em->getRepository(Brand::class)
            ->findOneBy(["brand_name" => "Nissan"]);
    }

    public function testNissanMustExist()
    {
        self::assertNotNull($this->nissan);
    }

    public function testCanFetchAllCars()
    {
        $cars = self::$carService->getAllCars();
        $this->assertEquals(1, count($cars));
    }

    public function createCar() : Car
    {
        $car = new Car();
        $car->setCarBrand($this->nissan)
            ->setCarModel("GT-R34")
            ->setCarPrice("987654321")
            ->setCarVisible(true);
        return $car;
    }

    public function testCanAddCar() : Car
    {
        $car = $this->createCar();
        self::$carService->saveCar($car);
        $cars = self::$carService->getAllCars();
        self::assertEquals(2, count($cars));

        $fetchedCar = self::$em->getRepository(Car::class)
            ->findOneBy(["car_model" => "GT-R34"]);

        $this->assertNotNull($fetchedCar);

        return $car;
    }

    /**
     * @param Car $car
     * @depends testCanAddCar
     */
    public function testCanRemoveAfterAdd(Car $car)
    {
        self::$carService->removeCar($car->getCarId());
        // clearing database cache
        self::$em->clear();
        $fetchedCar = self::$em->getRepository(Car::class)
            ->findOneBy(["car_model" => "GT-R34"]);

        $this->assertNull($fetchedCar);
    }

    public function testApiEndpoint()
    {
        // we need a web client
        $client = self::createClient();
        $crawler = $client->request("GET", "/cars/api/cars");
        // $this->assertContains("SomeContent",$crawler->filter("#testDiv p")->text());
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $array = json_decode($response->getContent(), true);
        $this->assertNotEmpty($array);
        $this->assertTrue(isset($array[0]["car_model"]));
        $this->assertEquals("GT-R", $array[0]["car_model"]);

    }

    // IMPORTANT
    // git clone https://user@git.address.com/some.git
    // cd someDir
    // composer install (this installs the things that were included in the .gitignore)
    // should be able to access the website
}