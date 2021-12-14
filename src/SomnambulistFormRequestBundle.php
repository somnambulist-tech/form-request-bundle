<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle;

use Somnambulist\Bundles\FormRequestBundle\DependencyInjection\Compiler\ValidatorRuleCompilerPass;
use Somnambulist\Components\Validation\Rule;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SomnambulistFormRequestBundle
 *
 * @package    Somnambulist\Bundles\FormRequestBundle
 * @subpackage Somnambulist\Bundles\FormRequestBundle\SomnambulistFormRequestBundle
 */
class SomnambulistFormRequestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ValidatorRuleCompilerPass());

        $container->registerForAutoconfiguration(Rule::class)->addTag(ValidatorRuleCompilerPass::RULE_TAG_NAME);
    }
}
