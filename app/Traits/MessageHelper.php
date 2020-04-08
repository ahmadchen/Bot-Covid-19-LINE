<?php

namespace App\Traits;

trait MessageHelper
{
    public function isCommand($message)
    {
        $messages = explode(" ", $message);
        if (count($messages) > 1)
        {
            if($messages[0] == '/covid')
            {
                return true;
            }
        }
        return false;
    }

    public function getParamCount($message)
    {
        return count(explode(" ", $message));
    }

    public function getParam($message, $index)
    {
        return strtolower(explode(" ", $message)[$index]);
    }

    public function sliceParamUntilEnd($message, $index)
    {
        return implode(" ", array_slice(explode(" ", $message), $index));
    }

    public function getEmoticon($code) {
        $bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
        return mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');
    }

    public function formatNum($num, $dec = 0)
    {
        return number_format($num, $dec, ',', '.');
    }
}
