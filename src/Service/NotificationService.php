<?php 
namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function notifyProductAction(User $admin, string $productName, string $action): void
    {
        $notification = new Notification();
        $notification->setUserNotification($admin);
        $notification->setLabel(sprintf(
            'Produit "%s" a été %s le %s',
            $productName,
            $action,
            (new \DateTime())->format('d/m/Y H:i')
        ));
        $notification->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($notification);
        $this->em->flush();
    }

    public function notifyUserAction(?User $admin, string $userIdentifier, string $action): void
{
    $notification = new Notification();

    $label = sprintf(
        'Utilisateur "%s" a été %s le %s',
        $userIdentifier,
        $action,
        (new \DateTime())->format('d/m/Y H:i')
    );

    $notification->setLabel($label);
    if ($admin !== null) {
        $notification->setUserNotification($admin);
    }

    $notification->setCreatedAt(new \DateTimeImmutable());

    $this->em->persist($notification);
    $this->em->flush();
}

}