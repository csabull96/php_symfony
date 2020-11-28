<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Brand
 * @package App\Entity
 * @ORM\Entity
 * @ORM\Table(name="brands")
 */
class Brand implements \JsonSerializable
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $brand_id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $brand_name;

    /**
     * @var ArrayCollection|null
     * @ORM\OneToMany(targetEntity="Car", mappedBy="car_brand")
     */
    private $brand_cars;

    /**
     * Brand constructor.
     */
    public function __construct()
    {
        $this->brand_cars = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getBrandId(): int
    {
        return $this->brand_id;
    }

    /**
     * @return string|null
     */
    public function getBrandName(): ?string
    {
        return $this->brand_name;
    }

    /**
     * @param string|null $brand_name
     * @return Brand
     */
    public function setBrandName(?string $brand_name): Brand
    {
        $this->brand_name = $brand_name;
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getBrandCars(): ?ArrayCollection
    {
        return $this->brand_cars;
    }

    /**
     * @param ArrayCollection|null $brand_cars
     * @return Brand
     */
    public function setBrandCars(?ArrayCollection $brand_cars): Brand
    {
        $this->brand_cars = $brand_cars;
        return $this;
    }

    public function __toString()
    {
        return $this->brand_name;
    }

    public function jsonSerialize()
    {
        $array = get_object_vars($this);
        foreach ($array as $key => $value)
        {
            if (str_starts_with($key, "__"))
            {
                unset($array[$key]);
            }
        }
        return $array;
    }
}