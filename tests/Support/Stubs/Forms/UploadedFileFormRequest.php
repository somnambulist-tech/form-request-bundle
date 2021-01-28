<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms;

use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;

/**
 * Class UploadedFileFormRequest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UploadedFileFormRequest
 */
class UploadedFileFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|uploaded_file:0,10K,php,txt',
        ];
    }
}
