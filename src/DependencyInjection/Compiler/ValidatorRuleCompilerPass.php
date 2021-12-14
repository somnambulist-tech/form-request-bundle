<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\DependencyInjection\Compiler;

use ReflectionClass;
use Somnambulist\Components\Validation\Factory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function array_filter;
use function preg_replace;
use function strtolower;
use function ucwords;

/**
 * Class ValidatorRuleCompilerPass
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\DependencyInjection\Compiler
 * @subpackage Somnambulist\Bundles\FormRequestBundle\DependencyInjection\Compiler\ValidatorRuleCompilerPass
 */
class ValidatorRuleCompilerPass implements CompilerPassInterface
{
    public const RULE_TAG_NAME = 'somnambulist.form_request_bundle.rule';

    public function process(ContainerBuilder $container): void
    {
        $validator = $container->getDefinition(Factory::class);

        foreach ($container->findTaggedServiceIds(self::RULE_TAG_NAME) as $id => $tags) {
            $tags = array_filter($tags);

            if (empty($tags)) {
                $tags = [['rule_name' => $this->createRuleName($id)]];
            }

            foreach ($tags as $attributes) {
                $validator->addMethodCall('addRule', [$attributes['rule_name'], new Reference($id)]);
            }
        }
    }

    private function createRuleName(string $string): string
    {
        $value = (new ReflectionClass($string))->getShortName();
        $value = preg_replace('/\s+/u', '', ucwords($value));

        return strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $value));
    }
}
