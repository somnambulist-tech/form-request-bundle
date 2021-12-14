<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Somnambulist\Components\Validation\Contracts\MimeTypeGuesser as MimeTypeGuesserContract;
use Somnambulist\Components\Validation\MimeTypeGuesser;
use Somnambulist\Components\Validation\Rule;
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
    protected string $message = 'rule.mimes';
    protected MimeTypeGuesserContract $guesser;

    public function __construct(MimeTypeGuesserContract $guesser = null)
    {
        $this->guesser = $guesser ?? new MimeTypeGuesser();
    }

    public function fillParameters(array $params): Rule
    {
        $this->types($params);

        return $this;
    }

    public function types($types): self
    {
        if (is_string($types)) {
            $types = explode(',', $types);
        }

        $this->params['allowed_types'] = $types;

        return $this;
    }

    public function check($value): bool
    {
        $allowedTypes = $this->parameter('allowed_types');

        // below is Required rule job
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            return true;
        }

        if (!empty($allowedTypes) && !in_array($this->guesser->getExtension($value->getClientMimeType()), $allowedTypes)) {
            return false;
        }

        return true;
    }
}
