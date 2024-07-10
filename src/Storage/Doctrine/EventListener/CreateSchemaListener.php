<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\EventListener;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Psr\Container\ContainerInterface;
use SymfonyCasts\MessengerMonitorBundle\Storage\Doctrine\Connection;

/**
 * @internal
 */
final class CreateSchemaListener
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
}
