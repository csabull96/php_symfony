<?php


namespace App\Services;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormFactoryInterface;

abstract class CrudService
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var  FormFactoryInterface*/
    protected $formFactory;

    /**
     * CrudService constructor.
     * @param EntityManagerInterface $em
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManagerInterface $em, FormFactoryInterface $formFactory)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
    }

    /**
     * @return EntityRepository
     */
    public abstract function getRepo() : EntityRepository;
}