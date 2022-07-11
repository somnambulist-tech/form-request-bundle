<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

class ProductFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'                 => 'required|uuid',
            'name'               => 'required|min:6',
            'properties'         => 'array',
            'properties.*.key'   => 'required|alpha_num',
            'properties.*.value' => 'required|alpha_num',
        ];
    }
}
