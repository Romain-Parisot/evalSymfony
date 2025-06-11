<?php
namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserSubscriber implements EventSubscriber
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'postPersist',
            'postUpdate',
            'postRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->notifyIfUserEntity($args, 'ajouté');
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->notifyIfUserEntity($args, 'modifié');
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->notifyIfUserEntity($args, 'supprimé');
    }

    private function notifyIfUserEntity(LifecycleEventArgs $args, string $action): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $admin = null;

        $this->notificationService->notifyUserAction(
            $admin,
            $entity->getFirstName(),
            $action
        );
    }
}
