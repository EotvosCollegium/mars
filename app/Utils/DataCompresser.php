<?php

namespace App\Utils;

class DataCompresser
{
    private const DELIMETER = '|';

    /**
     * Compress an array to a delimeter separated string.
     *
     * @author @hamaren2517
     *
     * @param  array  $array
     * @return string|null $string
     */
    public static function compressData($array): ?string
    {
        if ($array === null) {
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
     * @return string[]|null $string
     * @author @hamaren2517
     *
     */
    public static function decompressData($string): ?array
    {
        if ($string === null) {
            return null;
        }

        return explode(self::DELIMETER, $string);
    }
}
