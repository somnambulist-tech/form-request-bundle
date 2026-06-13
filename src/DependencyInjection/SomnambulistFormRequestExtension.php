<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\DependencyInjection;

use Somnambulist\Bundles\FormRequestBundle\EventSubscribers\AccessDeniedExceptionSubscriber;
use Somnambulist\Bundles\FormRequestBundle\EventSubscribers\FormValidationExceptionSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SomnambulistFormRequestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        if (false === $config['subscribers']['authorization']) {
            $container->removeDefinition(AccessDeniedExceptionSubscriber::class);
        }
        if (false === $config['subscribers']['form_validation']) {
            $container->removeDefinition(FormValidationExceptionSubscriber::class);
        }
    }
}
