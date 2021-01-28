<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Helper;
use Rakit\Validation\MimeTypeGuesser;
use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Traits;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function array_shift;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_string;

/**
 * Class UploadedFileRule
 *
 * A re-implementation of the UploadedFile rule to work with Symfony file arrays.
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\UploadedFileRule
 */
class UploadedFileRule extends Rule
{
    use Traits\FileTrait;
    use Traits\SizeTrait;

    /** @var string */
    protected $message = "The :attribute is not a valid uploaded file";

    /**
     * Given $params and assign $this->params
     *
     * @param array $params
     *
     * @return self
     */
    public function fillParameters(array $params): Rule
    {
        $this->minSize(array_shift($params));
        $this->maxSize(array_shift($params));
        $this->fileTypes($params);

        return $this;
    }

    /**
     * Given $size and set the max size
     *
     * @param string|int $size
     *
     * @return self
     */
    public function maxSize($size): Rule
    {
        $this->params['max_size'] = $size;

        return $this;
    }

    /**
     * Given $size and set the min size
     *
     * @param string|int $size
     *
     * @return self
     */
    public function minSize($size): Rule
    {
        $this->params['min_size'] = $size;

        return $this;
    }

    /**
     * Given $min and $max then set the range size
     *
     * @param string|int $min
     * @param string|int $max
     *
     * @return self
     */
    public function sizeBetween($min, $max): Rule
    {
        $this->minSize($min);
        $this->maxSize($max);

        return $this;
    }

    /**
     * Given $types and assign $this->params
     *
     * @param mixed $types
     *
     * @return self
     */
    public function fileTypes($types): Rule
    {
        if (is_string($types)) {
            $types = explode('|', $types);
        }

        $this->params['allowed_types'] = $types;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeValidate()
    {
        $attribute = $this->getAttribute();

        if (!$attribute->isUsingDotNotation()) {
            return;
        }

        $keys          = explode(".", $attribute->getKey());
        $firstKey      = array_shift($keys);
        $firstKeyValue = $this->validation->getValue($firstKey);
        $resolvedValue = $this->resolveUploadedFileValue($firstKeyValue);

        if (!$resolvedValue) {
            return;
        }

        $this->validation->setValue($firstKey, $resolvedValue);
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

        if ($allowedTypes) {
            $or = $this->validation ? $this->validation->getTranslation('or') : 'or';
            $this->setParameterText('allowed_types', Helper::join(Helper::wraps($allowedTypes, "'"), ', ', ", {$or} "));
        }

        // below is Required rule job
        if (!$value->isValid()) {
            return true;
        }

        if ($minSize) {
            $bytesMinSize = $this->getBytesSize($minSize);
            if ($value->getSize() < $bytesMinSize) {
                $this->setMessage('The :attribute file is too small, minimum size is :min_size');

                return false;
            }
        }

        if ($maxSize) {
            $bytesMaxSize = $this->getBytesSize($maxSize);
            if ($value->getSize() > $bytesMaxSize) {
                $this->setMessage('The :attribute file is too large, maximum size is :max_size');

                return false;
            }
        }

        if (!empty($allowedTypes)) {
            $guesser = new MimeTypeGuesser;
            $ext     = $guesser->getExtension($value->getClientMimeType());
            unset($guesser);

            if (!in_array($ext, $allowedTypes)) {
                $this->setMessage('The :attribute file type must be :allowed_types');

                return false;
            }
        }

        return true;
    }

    public function resolveUploadedFileValue($value)
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
