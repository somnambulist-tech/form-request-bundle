<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Resources;

function arrayAll(array $array, array $keys): array
{
    $ret = [];

    foreach ($keys as $key) {
        $ret[$key] = arrayGet($array, $key);
    }

    return $ret;
}

/**
 * Based on Laravel Arr::forget
 * @link https://github.com/laravel/framework/blob/cf26e13fa45ac5d9e64ddd0c830ed78e56e3fd4d/src/Illuminate/Collections/Arr.php#L241
 *
 * @param array $array
 * @param array $keys
 *
 * @return void
 */
function forget(array &$array, array $keys): void
{
    $original = &$array;

    if (count($keys) === 0) {
        return;
    }

    foreach ($keys as $key) {
        if (array_key_exists($key, $array)) {
            unset($array[$key]);

            continue;
        }

        $parts = explode('.', $key);

        $array = &$original;

        while (count($parts) > 1) {
            $part = array_shift($parts);

            if (isset($array[$part]) && is_array($array[$part])) {
                $array = &$array[$part];
            } else {
                continue 2;
            }
        }

        unset($array[array_shift($parts)]);
    }
}

/**
 * Based on Laravel Arr::get
 * @link https://github.com/laravel/framework/blob/cf26e13fa45ac5d9e64ddd0c830ed78e56e3fd4d/src/Illuminate/Collections/Arr.php#L286
 *
 * @param array      $array
 * @param string     $key
 * @param mixed|null $default
 *
 * @return mixed
 */
function arrayGet(array $array, string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $array)) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (is_array($array) && array_key_exists($segment, $array)) {
            $array = $array[$segment];
        } else {
            return is_callable($default) ? $default() : $default;
        }
    }

    return $array;
}

/**
 * Based on Laravel Arr::has
 * @link https://github.com/laravel/framework/blob/cf26e13fa45ac5d9e64ddd0c830ed78e56e3fd4d/src/Illuminate/Collections/Arr.php#L322
 *
 * @param array $array
 * @param array $keys
 *
 * @return bool
 */
function arrayHas(array $array, array $keys): bool
{
    if (!$array || $keys === []) {
        return false;
    }

    foreach ($keys as $key) {
        $testArr = $array;

        if (array_key_exists($key, $array)) {
            continue;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($testArr) && array_key_exists($segment, $testArr)) {
                $testArr = $testArr[$segment];
            } else {
                return false;
            }
        }
    }

    return true;
}
