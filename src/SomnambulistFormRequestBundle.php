<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle;

use Somnambulist\Bundles\FormRequestBundle\DependencyInjection\Compiler\ValidatorRuleCompilerPass;
use Somnambulist\Components\Validation\Rule;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SomnambulistFormRequestBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ValidatorRuleCompilerPass());

        $container->registerForAutoconfiguration(Rule::class)->addTag(ValidatorRuleCompilerPass::RULE_TAG_NAME);
    }
}
