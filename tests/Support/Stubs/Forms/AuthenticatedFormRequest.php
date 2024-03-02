<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Symfony\Bundle\SecurityBundle\Security;

class AuthenticatedFormRequest extends FormRequest
{

    public function authorize(Security $security): bool
    {
        return $security->isGranted('ROLE_ADMIN');
    }

    public function rules(): array
    {
        return [
            'id'       => 'required|uuid',
            'name'     => 'required|min:8|max:100',
            'email'    => 'required|min:4|max:255|email',
            'password' => 'required|min:8|max:100',
        ];
    }
}
