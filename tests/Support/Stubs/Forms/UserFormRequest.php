<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class UserFormRequest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UserFormRequest
 */
class UserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'     => 'required|min:8|max:100',
            'email'    => 'required|min:4|max:255|email',
            'password' => 'required|min:8|max:100',
        ];
    }
}
