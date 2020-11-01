<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MathController
 * @package App\Controller
 * @Route(path="math/")
 */
class MathController extends AbstractController
{
    private function isPrime(int $num) : bool
    {
        if ($num < 2) return false;
        $lim = sqrt($num);
        for ($i = 2; $i <= $lim; $i++)
        {
            if ($num % $i == 0) return false;
        }
        return true;
    }

    private function getTable(int $min, int $max) : string
    {
        $tpl_table = file_get_contents("../templates/primes/table.html");
        $tpl_row_separator = file_get_contents("../templates/primes/rowseparator.html");
        $tpl_normal_cell = file_get_contents("../templates/primes/cellnormal.html");
        $tpl_prime_cell = file_get_contents("../templates/primes/cellprime.html");

        $rows = "";
        $maxValue = 0;
        for ($i = 1; $i <= 100; $i++)
        {
            $num = rand($min, $max);
            if ($this->isPrime($num))
                $rows .= str_replace("{{ number }}", $num, $tpl_prime_cell);
            else
                $rows .= str_replace("{{ number }}", $num, $tpl_normal_cell);

            if ($i % 10 == 0) $rows .= $tpl_row_separator;
            if ($num > $maxValue) $maxValue = $num;
        }

        $output = $tpl_table;
        $output = str_replace("{{ rows }}", $rows, $output);
        $output = str_replace("{{ maximum }}", $maxValue, $output);

        return $output;
    }

    /**
     * @param Request $request
     * @param int $minValue
     * @param int $maxValue
     * @return Response
     * @Route(name="primesAction", path="primes/{minValue}/{maxValue}", requirements={ "minValue": "\d+", "maxValue": "\d+" })
     */
    public function getNumberTable(Request $request, int $minValue = 0, int $maxValue = 999) : Response
    {
        $str = $this->getTable($minValue, $maxValue);
        return new Response($str);
    }

    /**
     * @param Request $request
     * @param int $upperBound
     * @return Response
     * @Route(name="fibonacciAction", path="fibonacci/{upperBound}", requirements={"upperBound": "-?\d+"})
     */
    public function getFibonacciNumbers(Request $request, int $upperBound) : Response
    {
        return new Response($this->getFibonacciLessThan($upperBound));
    }

    /**
     * @param Request $requests
     * @param int $n
     * @return Response
     * @Route(name="firstNFibonacci", path="fibonacci/first/{n}", requirements={"n": "-?\d+"})
     */
    public function getFirstNFibonacci(Request $requests, int $n) : Response
    {
        return new Response($this->getFirstNFibonacciPrivate($n));
    }

    private function getFirstNFibonacciPrivate(int $n)
    {
        if ($n < 1) return "I'm gonna need a integer that is greater than 0.";

        $table = file_get_contents("../templates/primes/table.html");
        $cell = file_get_contents("../templates/primes/cellnormal.html");
        $rowSeparator = file_get_contents("../templates/primes/rowseparator.html");

        $initialCell = str_replace("{{ number }}", 1, $cell);
        $content = "";

        if (0 < $n) $content .= $initialCell;

        if (1 < $n) $content .= $initialCell;

        if (2 < $n)
        {
            $previous = 1;
            $current = 2;

            for ($i = 2; $i < $n; $i++)
            {
                $next = $previous + $current;
                $previous = $current;
                $current = $next;

                if ($i % 10 == 0) $content .= $rowSeparator;

                $content .= str_replace("{{ number }}", $current, $cell);
            }
        }

        return str_replace("{{ rows }}", $content, $table);
    }

    private function getFibonacciLessThan(int $upperBound) : String
    {
        if ($upperBound < 1)
            return "I'm gonna need a positive number.";

        $table = file_get_contents("../templates/primes/table.html");
        $cell = file_get_contents("../templates/primes/cellnormal.html");
        $rowSeparator = file_get_contents("../templates/primes/rowseparator.html");

        $initialCell = str_replace("{{ number }}", 1, $cell);
        $content = $initialCell . $initialCell;

        $previous = 1;
        $current = 2;
        $count = 2;

        while ($current < $upperBound)
        {
            $content .= str_replace("{{ number }}", $current, $cell);

            if (++$count % 10 == 0)
                $content .= $rowSeparator;

            $next = $previous + $current;
            $previous = $current;
            $current = $next;
        }

        return str_replace("{{ rows }}", $content, $table);
    }
}