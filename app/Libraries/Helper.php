<?php
namespace App\Libraries;

class Helper
{
    static function convertToNumber(string $str)
    {
        $number = preg_replace("/([^0-9\\.])/i", "", $str);

        return (float) $number;
    }
}
