<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class UserAdminController extends AbstractController
{
    #[Route('/admin/add-points', name: 'admin_add_points')]
public function addPointsToActiveUsers(EntityManagerInterface $em, UserRepository $userRepository): Response
{
    $users = $userRepository->findBy(['active' => true]);

    foreach ($users as $user) {
        $user->setPoints($user->getPoints() + 1000);
    }

    $em->flush();

    $this->addFlash('success', '1000 points ont été ajoutés à tous les utilisateurs actifs.');

    return $this->redirectToRoute('admin_user_index');
}

}
