<?php

namespace App\Utils;

class DataCompresser
{
    private const DELIMETER = '|';

    /**
     * Compress an array to a delimeter separated string.
     * @author @hamaren2517
     * @param  array  $array
     * @return string|null $string
     */
    public static function compressData($array): ?string
    {
        if ($array == null) {
            return null;
        }

        return join(
            self::DELIMETER,
            array_map(
                function ($item) {
                    return str_replace(self::DELIMETER, ' ', $item);
                },
                array_filter($array, function ($item) {
                    return $item !== null;
                })
            )
        );
    }

    /**
     * Decompress a delimeter separated string into an array.
     *
     * @param $string
     * @return array|string[]
     * @author @hamaren2517
     *
     */
    public static function decompressData($string): array
    {
        if (!isset($string) || $string == "") {
            return [];
        }

        return explode(self::DELIMETER, $string);
    }
}
