<?php

function pw_removeDiacritics($string)
{
    $string = str_replace("č", "c", $string);
    $string = str_replace("ć", "c", $string);
    $string = str_replace("š", "s", $string);
    $string = str_replace("ž", "z", $string);
    $string = str_replace("đ", "d", $string);
    $string = str_replace("Č", "C", $string);
    $string = str_replace("Ć", "C", $string);
    $string = str_replace("Š", "S", $string);
    $string = str_replace("Ž", "Z", $string);
    $string = str_replace("Đ", "D", $string);
    return $string;
}

?>
