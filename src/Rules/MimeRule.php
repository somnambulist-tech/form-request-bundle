<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Helper;
use Rakit\Validation\MimeTypeGuesser;
use Rakit\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function explode;
use function in_array;
use function is_string;

/**
 * Class MimeRule
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\MimeRule
 */
class MimeRule extends Rule
{
    /** @var string */
    protected $message = ':attribute file type must be :allowed_types';

    public function fillParameters(array $params): Rule
    {
        $this->allowTypes($params);

        return $this;
    }

    public function allowTypes($types): Rule
    {
        if (is_string($types)) {
            $types = explode(',', $types);
        }

        $this->params['allowed_types'] = $types;

        return $this;
    }

    /**
     * @param UploadedFile $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        $allowedTypes = $this->parameter('allowed_types');

        if ($allowedTypes) {
            $or = $this->validation ? $this->validation->getTranslation('or') : 'or';
            $this->setParameterText('allowed_types', Helper::join(Helper::wraps($allowedTypes, "'"), ', ', ", {$or} "));
        }

        // below is Required rule job
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            return true;
        }

        if (!empty($allowedTypes)) {
            $guesser = new MimeTypeGuesser;
            $ext     = $guesser->getExtension($value->getClientMimeType());
            unset($guesser);

            if (!in_array($ext, $allowedTypes)) {
                return false;
            }
        }

        return true;
    }
}
