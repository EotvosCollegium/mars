<?php

namespace App\Utils;

class LatexSanitizer
{
    /**
     * Converts a single character to a \symbol{...} command
     * @param  string  $char
     * @return string
     */
    private static function convertCharToSymbol(string $char): string
    {
        if($char == ' ') {
            return ' ';
        }
        return "\\symbol{" . mb_ord($char) . "}";
    }

    /**
     * Converts string to a relatively safe Latex code by putting every character into a seperate \symbol{...}
     * @param  string  $data
     * @return string
     */
    public static function sanitizeLatex(string|null $data): string
    {
        if($data == null) {
            return "";
        }
        $len = mb_strlen($data);
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            $result[] = LatexSanitizer::convertCharToSymbol(mb_substr($data, $i, 1));
        }
        return implode($result);
    }
}
