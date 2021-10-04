<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\DependencyInjection;

use Somnambulist\Bundles\FormRequestBundle\EventSubscribers\AccessDeniedExceptionSubscriber;
use Somnambulist\Bundles\FormRequestBundle\EventSubscribers\FormValidationExceptionSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class SomnambulistFormRequestExtension
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\DependencyInjection
 * @subpackage Somnambulist\Bundles\FormRequestBundle\DependencyInjection\SomnambulistFormRequestExtension
 */
class SomnambulistFormRequestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (false === $config['subscribers']['authorization']) {
            $container->removeDefinition(AccessDeniedExceptionSubscriber::class);
        }
        if (false === $config['subscribers']['form_validation']) {
            $container->removeDefinition(FormValidationExceptionSubscriber::class);
        }
    }
}
