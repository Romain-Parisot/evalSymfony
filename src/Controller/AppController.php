<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_app')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('app/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'product_show', methods: ['GET', 'POST'])]
    public function show(
        int $id,
        ProductRepository $productRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $product = $productRepository->find($id);

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$product || !$user instanceof User) {
            throw $this->createNotFoundException('Produit ou utilisateur introuvable');
        }

        if ($request->isMethod('POST')) {
            $quantity = (int) $request->request->get('quantity', 1);
            $totalPrice = $product->getPrice() * $quantity;

            if ($user->getPoints() >= $totalPrice) {
                $user->setPoints($user->getPoints() - $totalPrice);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Votre produit est bien commandé. Vous le recevrez sous peu !');
                return $this->redirectToRoute('product_show', ['id' => $id]);
            } else {
                $this->addFlash('error', 'Points insuffisants pour commander ce produit.');
            }
        }

        return $this->render('app/show.html.twig', [
            'product' => $product,
            'userPoints' => $user->getPoints(),
        ]);
    }
}
