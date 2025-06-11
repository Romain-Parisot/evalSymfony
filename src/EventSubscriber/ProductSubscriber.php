<?php

namespace App\EventSubscriber;

use App\Entity\Product;
use App\Service\NotificationService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

class ProductSubscriber implements EventSubscriber
{
    public function __construct(
        private NotificationService $notificationService,
        private Security $security
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->handleEvent($args, 'ajouté');
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->handleEvent($args, 'modifié');
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->handleEvent($args, 'supprimé');
    }

    private function handleEvent(LifecycleEventArgs $args, string $action): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $admin = $this->security->getUser();
        if (!$admin instanceof \App\Entity\User) {
            return;
        }

        $this->notificationService->notifyProductAction(
            $admin,
            $entity->getName(),
            $action
        );
    }
}