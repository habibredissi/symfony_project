<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Favorite;
use App\Repository\UserRepository;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(EntityManagerInterface $em, FavoriteRepository $repoFav, UserRepository $repo, ObjectManager $manager)
    {
        // Check if the user is admin
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if($user->getAdmin() == false) {
            return $this->redirectToRoute('home');
        }

        // Group favorite movies by id
        $query = $em->createQuery('SELECT u, count(u.id) c FROM App\Entity\Favorite u GROUP BY u.api_id ORDER BY c DESC');
        $favorites = $query->getResult();
        dump($favorites);
        $users = $repo->findAll();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'users' => $users,
            'favorites' => $favorites
        ]);
    }

    /**
     * @Route("/admin/ban/{id}", name="ban_user")
     */
    public function banUser(User $user, ObjectManager $manager) {
        $user->setBanned(1);
        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('admin');
    
    }
    /**
     * @Route("/admin/unban/{id}", name="unban_user")
     */
    public function unbanUser(User $user, ObjectManager $manager) {
        $user->setBanned(0);
        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('admin');
    }
}
