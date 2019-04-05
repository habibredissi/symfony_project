<?php

namespace App\Controller;

use DateTime;
use App\Entity\Favorite;
use App\Repository\ListeRepository;
use App\Repository\FavoriteRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    /**
     * @Route("/movie", name="movie")
     * @Route("/movie/{page}", name="moviePage")
     * @Route("/", name="home")
     */
    public function index($page = 1, ObjectManager $manager, ListeRepository $repo)
    {
        // Verify if the user is banned
        if($this->get('security.token_storage')->getToken()->getUser() != 'anon.') {
            if($this->get('security.token_storage')->getToken()->getUser()->getBanned()) {
                return $this->redirectToRoute('security_logout');
            }
        }
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, TRUE); 
        curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/ssl.txt");
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.themoviedb.org/3/movie/top_rated?api_key=86d5c0fa05ecba8211b550bab3db9432&language=en-US&page=$page",
        CURLOPT_RETURNTRANSFER => true,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $json = json_decode($response, true);
            if($json['page'] == 1) {
                $previousPage = 1;
            } else {
                $previousPage = $json['page'] - 1;
            }
            
            $nextPage = $json['page'] + 1;
        }
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $listes = $repo->findBy(['users' => $user]);
        // dump($listes);
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
            'movies' => $json['results'],
            'previousPage' => $previousPage,
            'nextPage' => $nextPage,
            'listes' => $listes
        ]);
    }

    /**
     * @Route("/movie/show/{id}", name="showMovie")
     */
    public function showMovie($id, ListeRepository $repo)
    {
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, TRUE); 
        curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/ssl.txt");
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.themoviedb.org/3/movie/$id?api_key=86d5c0fa05ecba8211b550bab3db9432&language=en-US",
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

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $listes = $repo->findBy(['users' => $user]);

        return $this->render('movie/show.html.twig', [
            'controller_name' => 'MovieController',
            'movie' => $json,
            'listes' => $listes
        ]);
    }
}
