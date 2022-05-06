<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Psr\Container\ContainerInterface;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;

/**
 * @internal
 */
final class CreateSchemaListener implements EventSubscriber
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        $this->container->get(Connection::class)->configureSchema(
            $event->getSchema(),
            $event->getEntityManager()->getConnection()
        );
    }

    public function getSubscribedEvents(): array
    {
        if (!class_exists(ToolEvents::class)) {
            return [];
        }

        return [
            ToolEvents::postGenerateSchema,
        ];
    }
}
