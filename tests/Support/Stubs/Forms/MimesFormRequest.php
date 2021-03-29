<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class MimesFormRequest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\MimesFormRequest
 */
class MimesFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|uploaded_file:0,10K|mimes:php,txt',
        ];
    }
}
