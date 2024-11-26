<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Somnambulist\Components\Validation\Contracts\MimeTypeGuesser as MimeTypeGuesserContract;
use Somnambulist\Components\Validation\Helper;
use Somnambulist\Components\Validation\MimeTypeGuesser;
use Somnambulist\Components\Validation\Rule;
use Somnambulist\Components\Validation\Rules\Behaviours;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function array_shift;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_string;

/**
 * A re-implementation of the UploadedFile rule to work with Symfony file arrays.
 */
class UploadedFileRule extends Rule
{
    use Behaviours\CanValidateFiles;
    use Behaviours\CanObtainSizeValue;

    protected string $message = 'rule.uploaded_file';
    protected MimeTypeGuesserContract $guesser;

    public function __construct(?MimeTypeGuesserContract $guesser = null)
    {
        $this->guesser = $guesser ?? new MimeTypeGuesser();
    }

    public function fillParameters(array $params): self
    {
        if (count($params) < 2) {
            return $this;
        }

        $this->minSize(array_shift($params));
        $this->maxSize(array_shift($params));
        $this->types($params);

        return $this;
    }

    /**
     * Set the minimum filesize
     */
    public function minSize(int|string $size): self
    {
        $this->params['min_size'] = $size;

        return $this;
    }

    /**
     * Set the max allowed file size
     */
    public function maxSize(int|string $size): self
    {
        $this->params['max_size'] = $size;

        return $this;
    }

    /**
     * Set the filesize between the min/max
     */
    public function between(int|string $min, int|string $max): self
    {
        $this->minSize($min);
        $this->maxSize($max);

        return $this;
    }

    /**
     * Set the array of allowed types e.g. doc,docx,xls,xlsx
     */
    public function types($types): self
    {
        if (is_string($types)) {
            $types = explode(',', $types);
        }

        $this->params['allowed_types'] = $types;

        return $this;
    }

    public function beforeValidate()
    {
        $attribute = $this->attribute();

        if (!$attribute->isUsingDotNotation()) {
            return;
        }

        $keys          = explode('.', $attribute->key());
        $firstKey      = array_shift($keys);
        $firstKeyValue = $this->validation->input()->get($firstKey);
        $resolvedValue = $this->resolveUploadedFileValue($firstKeyValue);

        if (!$resolvedValue) {
            return;
        }

        $this->validation->input()->set($firstKey, $resolvedValue);
    }

    /**
     * Check the $value is valid
     *
     * @param UploadedFile $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        $minSize      = $this->parameter('min_size');
        $maxSize      = $this->parameter('max_size');
        $allowedTypes = $this->parameter('allowed_types');

        // below is Required rule job
        if (!$value->isValid()) {
            return true;
        }

        if ($minSize && $value->getSize() < $this->getSizeInBytes($minSize)) {
            $this->message = 'rule.uploaded_file.min_size';

            return false;
        }

        if ($maxSize && $value->getSize() > $this->getSizeInBytes($maxSize)) {
            $this->message = 'rule.uploaded_file.max_size';

            return false;
        }

        if (!empty($allowedTypes) && !in_array($this->guesser->getExtension($value->getClientMimeType()), $allowedTypes)) {
            $this->message = 'rule.uploaded_file.type';

            return false;
        }

        return true;
    }

    public function resolveUploadedFileValue($value): ?array
    {
        if (!$value->isValid()) {
            return null;
        }

        $arrayDots = Helper::arrayDot($value);

        $results = [];
        foreach ($arrayDots as $key => $val) {
            $splits   = explode(".", $key);
            $firstKey = array_shift($splits);
            $key      = count($splits) ? implode(".", $splits) . ".{$firstKey}" : $firstKey;

            Helper::arraySet($results, $key, $val);
        }

        return $results;
    }
}
