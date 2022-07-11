<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

class SearchUsersFormRequest extends FormRequest
{
    protected array $ignore = ['page', 'per_page', 'include'];

    public function rules(): array
    {
        return [
            'name'  => 'nullable|string|max:100',
            'email' => 'nullable|string|max:255',

            'page'     => 'default:1|numeric',
            'per_page' => 'default:30|numeric|max:100',
            'include'  => ['nullable', 'regex:/[(groups|permissions),.]/'],
        ];
    }
}
