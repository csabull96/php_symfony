<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SongVoterController
 * @package App\Controller
 * @Route(path="songs/")
 */
class SongVoterController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     * @Route(path="form", name="songVoterForm")
     */
    public function SongVoterForm(Request $request) : Response
    {
        $songs = file("../templates/SongVoterTemplates/songs.txt", FILE_IGNORE_NEW_LINES);
        $selectOptionTemplate = file_get_contents("../templates/SongVoterTemplates/SelectOption.html");
        $options = "";

        foreach ($songs as $value)
            $options .= str_replace("{{ option }}", $value, $selectOptionTemplate);

        $songVoterForm = file_get_contents("../templates/SongVoterTemplates/SongVoterForm.html");
        return new Response(str_replace("{{ Songs }}", $options, $songVoterForm));
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="vote", name="submitSongVote")
     */
    public function SubmitVote(Request $request) : Response
    {
        $name = $request->request->get("VoterName", "no_name");
        $email = $request->request->get("VoterEmail", "no_email");
        $favoriteSong = $request->request->get("VoterFavoriteSong", "no_favorite_song");

        $voteInfo = "{$name};{$email};{$favoriteSong}\n";
        file_put_contents("../templates/SongVoterTemplates/votes.txt", $voteInfo,FILE_APPEND);

        $voteSubmittedTemplate = file_get_contents("../templates/SongVoterTemplates/VoteSubmitted.html");

        return new Response(str_replace("{{ VoterName }}", $name, $voteSubmittedTemplate));
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="list", name="listVotes")
     */
    public function ListVotes(Request $request) : Response
    {
        $votes = file("../templates/SongVoterTemplates/votes.txt");
        $votesGroupedBySongs = array();

        foreach ($votes as $value)
        {
            $voteDetails = explode(";", $value);
            $song = $voteDetails[2];

            if (key_exists($song, $votesGroupedBySongs))
            {
                $votesGroupedBySongs[$song]++;
            }
            else
            {
                $votesGroupedBySongs[$song] = 1;
            }
        }

        $voteListTemplate = file_get_contents("../templates/SongVoterTemplates/VoteList.html");
        $songTemplate =  file_get_contents("../templates/SongVoterTemplates/Song.html");

        $songs = "";
        $toReplace = array("{{ Song }}", "{{ NumberOfVotes }}");

        foreach ($votesGroupedBySongs as $key => $value)
        {
            $replacement = array($key, $value);
            $songs .= str_replace($toReplace, $replacement,$songTemplate);
        }

        $time = date("yy.m.d H:i:s");

        $response = str_replace(array("{{ Time }}", "{{ Songs }}"),
            array($time, $songs),
            $voteListTemplate);

        return new Response( $response);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="lottery", name="voterLottery")
     */
    public function VoterLottery(Request $request) : Response
    {
        $votes = file("../templates/SongVoterTemplates/votes.txt");
        $Key = array_rand($votes);
        $voteDetails = explode(";", $votes[$Key]);

        $lotteryWinner = "{$voteDetails[0]} ({$voteDetails[1]})";
        $lotteryTemplate = file_get_contents("../templates/SongVoterTemplates/Lottery.html");
        $response = str_replace("{{ LotteryWinner }}", $lotteryWinner, $lotteryTemplate);
        return new Response($response);
    }
}