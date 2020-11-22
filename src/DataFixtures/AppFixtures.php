<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\Choice;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppFixtures extends Fixture implements ContainerAwareInterface
{
    /** @var string */
    private $environment; // DEV, TEST

    /** @var EntityManager */
    private $em;

    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $kernel = $this->container->get("kernel");
        if ($kernel) $this->environment = $kernel->getEnvironment();
    }

    public function load(ObjectManager $manager)
    {
        echo "Hello, Fixtures!\n";

        $this->em = $manager;
        $stackLogger = new DebugStack(); // logs into string array
        $echoLogger = new EchoSQLLogger(); // logs to the console
        $this->em->getConnection()->getConfiguration()->setSQLLogger($stackLogger);

        $qu1 = new Question();
        $qu1->setQuText("Do you like PHP?");
        $this->em->persist($qu1);

        $qu2 = new Question();
        $qu2->setQuText("What's the weather like today?");
        $this->em->persist($qu2);

        $this->em->flush();

        echo "QUESTIONS OK. QUERIES: ".count($stackLogger->queries)."\n";

        $cho1 = new Choice();
        $cho1->setChoVisible(true)
            ->setChoNumvotes(0)
            ->setChoText("YES")
            ->setChoQuestion($qu1);
        $this->em->persist($cho1);

        $cho2 = new Choice();
        $cho2->setChoVisible(true)
            ->setChoNumvotes(0)
            ->setChoText("NO")
            ->setChoQuestion($qu1);
        $this->em->persist($cho2);

        $cho3 = new Choice();
        $cho3->setChoVisible(true)
            ->setChoNumvotes(0)
            ->setChoText("Sunny")
            ->setChoQuestion($qu2);
        $this->em->persist($cho3);

        $cho4 = new Choice();
        $cho4->setChoVisible(true)
            ->setChoNumvotes(0)
            ->setChoText("Cloudy")
            ->setChoQuestion($qu2);
        $this->em->persist($cho4);

        $this->em->flush();
        echo "CHOICES OK. QUERIES: ".count($stackLogger->queries)."\n";

        $oneChoice = $this->em->getRepository(Choice::class)->findOneBy(["cho_text" => "NO"]);
        $oneChoiceId = $oneChoice->getChoId();
        echo "CHOICE #{$oneChoiceId} FETCHED\n";

        $oneChoice->setChoNumvotes(42);
        $this->em->persist($oneChoice);
        $this->em->flush();

        // this one won't increase the number of queries as it fetches the
        // data from the cache
        $numVotes = $this->em->getRepository(Choice::class)
            ->find($oneChoiceId)
            ->getChoNumvotes();

        echo "MODIFICATIONS OK. VOTES: {$numVotes}. QUERIES: ".count($stackLogger->queries)."\n";
        echo "\n\n";

        // Identity Map
        echo "NUMBER OF CHOICES FOR QUESTION #1\n";
        echo $qu1->getQuChoices()->count()." BEFORE 1\n";
        $questionId = $qu1->getQuId();
        echo $this->em->getRepository(Question::class)->find($questionId)
            ->getQuChoices()->count()." BEFORE 2\n";
        $this->em->clear();
        echo $this->em->getRepository(Question::class)->find($questionId)
                ->getQuChoices()->count()." AFTER\n";
        echo "\n\n";

        $oneChoice = $this->em->getRepository(Choice::class)
            ->find($oneChoiceId);
        $this->em->remove($oneChoice);
        $this->em->flush();
        echo "DELETE OK. QUERIES: ".count($stackLogger->queries)."\n";
        echo "\n\n";

        $bmw = new Brand();
        $bmw->setBrandName("BMW");
        $this->em->persist($bmw);

        $audi = new Brand();
        $audi->setBrandName("Audi");
        $this->em->persist($audi);

        $nissan = new Brand();
        $nissan->setBrandName("Nissan");
        $this->em->persist($nissan);

        $this->em->flush();
        echo "BRANDS OK. QUERIES: ".count($stackLogger->queries)."\n";

        $gtr = new Car();
        $gtr
            ->setCarBrand($nissan)
            ->setCarModel("GT-R")
            ->setCarPrice(100000)
            ->setCarVisible(true);

        $this->em->persist($gtr);
        $this->em->flush();
        echo "CAR OK. QUERIES: ".count($stackLogger->queries)."\n";
    }
}

/*
 * CREATE DATABASE: php bin/console doctrine:database:create
 *
 * CREATE TABLES PREVIEW: php bin/console doctrine:schema:update --dump-sql
 *
 * CREATE TABLES: php bin/console doctrine:schema:update --force
 *
 * CLEAR DATABASE (the database itself won't be deleted): php bin/console doctrine:schema:drop --force --full-database
 *
 * TO RUN THE LOAD METHOD ABOVE: php bin/console doctrine:fixtures:load --no-interaction -vvv
 */