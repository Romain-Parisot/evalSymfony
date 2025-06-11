<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notification;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_app', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $em
    ): Response {
        $products = $productRepository->findAll();

        /** @var User|null $user */
        $user = $this->getUser();

        $nameForm = null;

        if ($user instanceof User) {
            $form = $this->createFormBuilder($user)
                ->add('firstName', TextType::class, ['label' => 'Prénom'])
                ->add('lastName', TextType::class, ['label' => 'Nom'])
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'Nom et prénom mis à jour avec succès.');
                return $this->redirectToRoute('app_app');
            }

            $nameForm = $form->createView();
        }

        return $this->render('app/index.html.twig', [
            'products' => $products,
            'nameForm' => $nameForm,
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
        if (!$user->isActive()) {
            $this->addFlash('error', 'Votre compte a été désactivé, vous ne pouvez plus passer de commande.');
            return $this->redirectToRoute('product_show', ['id' => $id]);
        }

        $quantity = (int) $request->request->get('quantity', 1);
        $totalPrice = $product->getPrice() * $quantity;

        if ($user->getPoints() >= $totalPrice) {
            $user->setPoints($user->getPoints() - $totalPrice);
            $em->persist($user);

            $notification = new Notification();
            $notification->setLabel(sprintf(
                'L\'utilisateur "%s" a acheté %d fois le produit: "%s".',
                $user->getFirstName(),
                $quantity,
                $product->getName()
            ));

            if (method_exists($notification, 'setUser')) {
                $notification->setUserNotification($user);
            }

            $em->persist($notification);

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
