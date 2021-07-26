<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models;

/**
 * Class Address
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\Address
 */
class Address
{
    public ?string $line1;
    public ?string $line2;
    public ?string $city;
    public ?string $state;
    public ?string $postcode;

    public function __construct(?string $line1, ?string $line2, ?string $city, ?string $state, ?string $postcode)
    {
        $this->line1    = $line1;
        $this->line2    = $line2;
        $this->city     = $city;
        $this->state    = $state;
        $this->postcode = $postcode;
    }
}
