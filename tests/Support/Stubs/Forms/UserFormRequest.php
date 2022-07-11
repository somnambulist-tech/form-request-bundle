<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

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
