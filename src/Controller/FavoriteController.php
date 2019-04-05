<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavoriteController extends AbstractController
{
    /**
     * @Route("/favorite", name="favorite")
     */
    public function index(FavoriteRepository $repo, ObjectManager $manager)
    {
        $currentUserId = $this->getUser()->getId();
        $favorites = $repo->findBy(array('relation' => $currentUserId), ['id' => 'DESC']);
        // dump($favorites);
        return $this->render('favorite/index.html.twig', [
            'controller_name' => 'FavoriteController',
            'favorites' => $favorites
        ]);
    }

    /**
     * @Route("/favorite/delete/{id}", name="delete_favorite")
     */
    public function deleteFavorite(FavoriteRepository $repo, ObjectManager $manager, $id)
    {
        $favorite = $repo->find($id);
        $manager->remove($favorite);
        $manager->flush();
        return $this->redirectToRoute('favorite');
    }

    /**
     * @Route("/movie/favorite/{id}", name="favoriteMovie")
     */
    public function favorite(FavoriteRepository $repo, ObjectManager $manager, $id) {

       
        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        $findFav = $repo->findBy(array('api_id' => $id));
        if($findFav) {
            $this->addFlash(
                'warning',
                'You already have this movie in your favorite list!'
            );
            return $this->redirectToRoute('home');
        }
        
        $favorite = new Favorite();
        $favorite->setTitle($json['title']);
        $favorite->setRelation($user);
        $favorite->setApiId($json['id']);
        $favorite->setNote($json['vote_average']);
        $favorite->setYear(\DateTime::createFromFormat('Y-m-d', $json['release_date']));
        $favorite->setOverview($json['overview']);
        $favorite->setImage('https://image.tmdb.org/t/p/w500/'.$json['poster_path']);

        $manager->persist($favorite);
        $manager->flush();

        return $this->redirectToRoute('favorite');
    }
}
