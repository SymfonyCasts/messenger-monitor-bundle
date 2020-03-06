<?php

declare(strict_types=1);

namespace SymfonyCasts\MessengerMonitorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\FailureReceiverPass;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\ReceiverLocatorPass;
use SymfonyCasts\MessengerMonitorBundle\DependencyInjection\SymfonyCastsMessengerMonitorExtension;

final class SymfonyCastsMessengerMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReceiverLocatorPass());
        $container->addCompilerPass(new FailureReceiverPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new SymfonyCastsMessengerMonitorExtension();
        }

        return $this->extension ?: null;
    }
}
