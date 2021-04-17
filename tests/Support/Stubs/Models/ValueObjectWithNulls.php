<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models;

/**
 * Class ValueObjectWithNulls
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ValueObjectWithNulls
 */
class ValueObjectWithNulls
{

    private string $name;
    private ?string $email;
    private ?string $phone;

    public function __construct(string $name, ?string $email, ?string $phone)
    {
        $this->name  = $name;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
