<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PlayerReviewerController
 * @package App\Controller
 */
class PlayerReviewerController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @param Request $request
     * @return Response
     * @Route(path="players", name="playersAction")
     */
    public function GetPlayers(Request $request): Response
    {
        $main_template = file_get_contents("../templates/PlayerReviewerTemplates/MainTemplate.html");
        $database = file("../templates/PlayerReviewerTemplates/players.csv", FILE_IGNORE_NEW_LINES);

        foreach ($database as $key => $value)
            $database[$key] = explode(",", $value);

        $headers = $database[0];
        $players = array_slice($database, 1, count($database) - 1);

        $table_of_players = $this->generateTable($headers, $players);
        $response = str_replace("{{table_of_players}}", $table_of_players, $main_template);

        // simple question #1
        $oldest_player = $this->getOldestPlayer($players);
        $unordered_list_items =
            $this->generateUnorderedList(array_map(array($this, "getFullNameOfPlayer"), $oldest_player));
        $response = str_replace("{{oldest_player}}", $unordered_list_items, $response);

        // simple question #2
        $players_single_digit_jersey_number = $this->getPlayersSingleDigitJerseyNumber($players);
        $unordered_list_items =
            $this->generateUnorderedList(array_map(array($this, "getFullNameOfPlayer"), $players_single_digit_jersey_number));
        $response =
            str_replace("{{players_single_digit_jersey_number}}", $unordered_list_items, $response);

        // simple question #3
        $players_located_in_los_angeles = $this->getPlayersLocatedInLosAngeles($players);
        $unordered_list_items =
            $this->generateUnorderedList(array_map(array($this, "getFullNameOfPlayer"), $players_located_in_los_angeles));
        $response =
            str_replace("{{players_located_in_los_angeles}}", $unordered_list_items, $response);

        // simple question #4
        $players_born_90s = $this->getPlayersBorn90s($database);
        $unordered_list_items =
            $this->generateUnorderedList(array_map(array($this, "getFullNameOfPlayer"), $players_born_90s));
        $response =
            str_replace("{{players_born_90s}}", $unordered_list_items, $response);

        // simple question #5
        $nba_players = $this->getProfessionalBasketballPlayers($database);
        $unordered_list_items =
            $this->generateUnorderedList(array_map(array($this, "getFullNameOfPlayer"), $nba_players));
        $response =
            str_replace("{{nba_players}}", $unordered_list_items, $response);

        // generated players
        $generated_players = $this->generatePlayers($players, 20);
        $table_of_players = $this->generateTable($headers, $generated_players);
        $response = str_replace("{{table_of_generated_players}}", $table_of_players, $response);

        // complex question #1
        $number_of_players_per_league =
            $this->getNumberOfPlayersPerLeague(array_merge_recursive($players, $generated_players));
        $unordered_list_items =
            $this->generateUnorderedList(array_map(function ($league_info) {
                return "{$league_info[0]} {$league_info[1]}";
            }, $number_of_players_per_league));
        $response =
            str_replace("{{number_of_players_per_league}}", $unordered_list_items, $response);

        // complex question #2
        $most_common_first_name =
            $this->getMostCommonFirstNames(array_merge_recursive($players, $generated_players), 3);
        $unordered_list_items =
            $this->generateUnorderedList(array_map(function ($most_common_name_info) {
                return "{$most_common_name_info[0]} {$most_common_name_info[1]}";
            }, $most_common_first_name));
        $response =
            str_replace("{{most_common_first_name}}", $unordered_list_items, $response);

        //complex question #3
        $avg_age = $this->getAverageAgeOfPlayers(array_merge_recursive($players, $generated_players));
        $response = str_replace("{{average_age}}", $avg_age, $response);

        return new Response($response);
    }

    private function getFullNameOfPlayer(array $player): string {
        return "{$player[0]} {$player[1]}";
    }

    private function generateUnorderedList(array $items): string {
        $unordered_list_item_template =
            file_get_contents("../templates/PlayerReviewerTemplates/UnorderedListItemTemplate.html");
        $unordered_list_items = "";
        foreach ($items as $item)
            $unordered_list_items .=
                str_replace("{{unordered_list_item}}", $item, $unordered_list_item_template);
        return $unordered_list_items;
    }

    private function getOldestPlayer(array $players): array {
        $oldest[0] = $players[0];
        for ($i = 1; $i < count($players); $i++)
            if ($oldest[0][2] > $players[$i][2])
                $oldest[0] = $players[$i];
        return $oldest;
    }

    private function getPlayersSingleDigitJerseyNumber(array $players): array {
        $queried_players = array();
        for ($i = 1; $i < count($players); $i++) {
            $player = $players[$i];
            if (intval($player[5]) < 10)
                $queried_players [] = $player;
        }
        return $queried_players;
    }

    private function getPlayersLocatedInLosAngeles(array $players): array {
        $queried_players = array();
        foreach ($players as $player)
            if (str_contains($player[4], "Los Angeles"))
                $queried_players [] = $player;
        return $queried_players;
    }

    private function getPlayersBorn90s(array $database): array {
        $players_born_90s = array();
        foreach ($database as $player) {
            $year = substr($player[2], 0, 4);
            if ("1989" < $year && $year < "2000") {
                $players_born_90s [] = $player;
            }

        }
        return $players_born_90s;
    }

    private function getProfessionalBasketballPlayers(array $players): array {
        $nba_players = array();
        foreach ($players as $player) {
            if (str_contains($player[3], "NBA")) {
                $nba_players [] = $player;
            }
        }
        return $nba_players;
    }

    private function generatePlayers(array $players, int $pcs): array {
        $generated_players = array();
        for ($i = 0; $i < $pcs; $i++) {
            $generated_player = array();
            for ($j = 0; $j < 6; $j++) {
                $rnd_index = rand(1, count($players) - 1);
                $generated_player[$j] = $players[$rnd_index][$j];
            }
            $generated_players [] = $generated_player;
        }
        return $generated_players;
    }

    private function generateTable(array $headers, array $content): string {
        $table_template = file_get_contents("../templates/PlayerReviewerTemplates/TableTemplate.html");
        $header_template = file_get_contents("../templates/PlayerReviewerTemplates/HeaderTemplate.html");
        $table_row_template = file_get_contents("../templates/PlayerReviewerTemplates/TableRowTemplate.html");
        $cell_template = file_get_contents("../templates/PlayerReviewerTemplates/CellTemplate.html");

        $table_headers = "";
        foreach ($headers as $header) {
            $formatted_header = ucwords(str_replace("_", " ", $header));
            $table_headers .= str_replace("{{header}}", $formatted_header, $header_template);
        }
        $response = str_replace("{{headers}}", $table_headers, $table_template);

        $table_content = "";
        foreach ($content as $row) {
            $table_row = "";
            foreach ($row as $cell)
                $table_row .= str_replace("{{cell}}", $cell, $cell_template);
            $table_content .= str_replace("{{table_row}}", $table_row, $table_row_template);
        }
        $response = str_replace("{{content}}", $table_content, $response);

        return $response;
    }

    private function getNumberOfPlayersPerLeague(array $players): array {
        $groups = array();
        foreach ($players as $player) {
            if (array_key_exists($player[3], $groups))
                $groups[$player[3]][1]++;
            else
                $groups[$player[3]] = array($player[3], 1);
        }

        usort($groups, function ($a, $b) {
            if ($a[1] == $b[1])
                return 0;
            return $a[1] > $b[1] ? -1 : 1;
        });

        return $groups;
    }

    private function getMostCommonFirstNames(array $players, int $pcs): array {
        $first_names = array();

        foreach ($players as $player) {
            if (!str_contains($player[0], "first_name")) {
                if (array_key_exists($player[0], $first_names))
                    $first_names[$player[0]][1]++;
                else
                    $first_names[$player[0]] = array($player[0], 1);
            }
        }

        usort($first_names, function ($a, $b) {
            if ($a[1] == $b[1])
                return 0;
            return $a[1] > $b[1] ? -1 : 1;
        });

        return array_slice($first_names, 0, $pcs);
    }

    private function getAverageAgeOfPlayers($players): float {
        $total_age = 0;
        $number_of_players = 0;
        foreach ($players as $player) {
            if (strcmp($player[2], "date_of_birth")) {
                $total_age += $this->getAge($player[2]);
                $number_of_players++;
            }
        }
        return $total_age / $number_of_players;
    }

    private function getAge(string $dob_string): int {
        $date = explode("-", $dob_string);
        $day = $date[2];
        $month = $date[1];
        $year = $date[0];

        $dob = mktime(0, 0, 0, $month, $day, $year);

        if (date("nj", $dob) > date("nj"))
            $age = date("Y") - date("Y", $dob);
        else
            $age = date("Y") - date("Y", $dob) - 1;

        return $age;
    }

}