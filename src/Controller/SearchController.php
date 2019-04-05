<?php

namespace App\Controller;

use App\Repository\ListeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    public function getGenreList() {
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, TRUE); 
        curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/ssl.txt");
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.themoviedb.org/3/genre/movie/list?api_key=86d5c0fa05ecba8211b550bab3db9432&language=en-US",
        CURLOPT_RETURNTRANSFER => true,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $json = json_decode($response, true);
            return $json['genres'];
        }
    }

    /**
     * @Route("/search", name="search")
     */
    public function index(Request $request, ObjectManager $manager, ListeRepository $repo)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $listes = $repo->findBy(['users' => $user]);
        $option = $request->request->get('option');
        $search = $request->request->get('search');
        $api = '86d5c0fa05ecba8211b550bab3db9432';
        $json = null;

        if(isset($option)){
            switch ($option) {
                case 1:
                    $url="https://api.themoviedb.org/3/search/movie?api_key=$api&language=en-US&query=$search&include_adult=false";
                    break;
                case 2:
                    $url="https://api.themoviedb.org/3/discover/movie?api_key=$api&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&primary_release_year=$search";
                    break;
                default:
                    $url="https://api.themoviedb.org/3/discover/movie?api_key=$api&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&with_genres=$option";
                    break;
            }

            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, TRUE); 
            curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/ssl.txt");
            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $json = json_decode($response, true);
            }
        }
        
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'movies' => $json['results'],
            'listes' => $listes,
            'genres' => $this->getGenreList()
        ]);
    }
}
