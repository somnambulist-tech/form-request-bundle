<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models;

/**
 * Class ExternalIdentity
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ExternalIdentity
 */
class ExternalIdentity
{

    private string $provider;
    private string $identity;

    public function __construct(string $provider, string $identity)
    {
        $this->provider = $provider;
        $this->identity = $identity;
    }

    public function toString(): string
    {
        return sprintf('%s:%s', $this->provider, $this->identity);
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function identity(): string
    {
        return $this->identity;
    }
}
