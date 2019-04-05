<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Liste;
use App\Form\ListeType;
use App\Entity\MovieList;
use App\Repository\ListeRepository;
use App\Repository\MovieListRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ListeController extends AbstractController
{
    /**
     * @Route("/liste", name="liste")
     */
    public function index(ListeRepository $repo, ObjectManager $manager)
    {
        $listes = $repo->findAll();
        // $form = $this->createForm(ListeType::class, $liste);
        return $this->render('liste/index.html.twig', [
            'controller_name' => 'ListeController',
            'listes' => $listes
        ]);
    }

    /**
     * @Route("/liste/new", name="create_liste")
     * @Route("/liste/edit/{id}", name="edit_liste")
     */
    public function createListe(Liste $liste = null, Request $request, ObjectManager $manager)
    {
        $edit = true;
        if(!$liste) {
            $liste = new Liste();
            $edit = false;
        }
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $form = $this->createForm(ListeType::class, $liste);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $liste->setUsers($user);
            $manager->persist($liste);
            $manager->flush();

            return $this->redirectToRoute('liste');
        }

        return $this->render('liste/create.html.twig', [
            'controller_name' => 'ListeController',
            'form' => $form->createView(),
            'edit' => $edit
        ]);
    }

    /**
     * @Route("/liste/delete/{id}", name="liste_delete")
     */
    public function deleteList(ObjectManager $manager, ListeRepository $repo, $id) {
        
        $liste = $repo->find($id);

        if($liste) {
            $manager->remove($liste);
            $manager->flush();
        }

        return $this->redirectToRoute('liste');
    }

    /**
     * @Route("/movie/addToList", name="addToList")
     */
    public function addToList(Request $request, ObjectManager $manager, MovieListRepository $repo, ListeRepository $repo2) {

        $listId = $request->request->get('listId');
        $movieId = $request->request->get('movieId');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $movie = new MovieList();

        $doesItExist = $repo->findBy(['listId' => $listId, 'apiId' => $movieId]);
        dump($doesItExist);
        if($doesItExist) {
            $this->addFlash(
                'warning',
                'You already have this movie in your list!'
            );
            return $this->redirectToRoute('home');
        }

        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, TRUE); 
        curl_setopt ($curl, CURLOPT_CAINFO, dirname(__FILE__)."/ssl.txt");
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.themoviedb.org/3/movie/$movieId?api_key=86d5c0fa05ecba8211b550bab3db9432&language=en-US",
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

        $list = $repo2->find($listId);

        $movie->setTitle($json['title']);
        $movie->setListId($list);
        $movie->setApiId($json['id']);
        $movie->setNote($json['vote_average']);
        $movie->setYear(\DateTime::createFromFormat('Y-m-d', $json['release_date']));
        $movie->setOverview($json['overview']);
        $movie->setImage('https://image.tmdb.org/t/p/w500/'.$json['poster_path']);

        $manager->persist($movie);
        $manager->flush();

        
        return $this->redirectToRoute('home');
        // return $this->render('liste/dump.html.twig', [
        //     'controller_name' => 'ListeController',
        // ]);
    }


    /**
     * @Route("/liste/{id}", name="viewList")
     */
    public function viewList(Liste $list, MovieListRepository $repo, ObjectManager $manager, $id) {


        $movie_liste = $repo->findBy(['listId' => $list]);
        
        // dump($movie_liste);

        return $this->render('liste/view.html.twig', [
            'controller_name' => 'ListeController',
            'movies' => $movie_liste
        ]);
    }

    /**
     * @Route("/movieliste/delete/{id}", name="deleteMovieFromList")
     */
    public function deleteMovieFromList(MovieListRepository $repo, ObjectManager $manager, $id)
    {
        $movie_liste = $repo->find($id);
        $listId = $movie_liste->getListId()->getId();

        // return $this->render('liste/dump.html.twig', [
        //     'controller_name' => 'ListeController',
        // ]);

        $manager->remove($movie_liste);
        $manager->flush();
        return $this->redirectToRoute('viewList', ['id' => $listId]);
    }
}
