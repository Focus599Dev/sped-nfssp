<?php

namespace NFePHP\NFs\SP\Common;

class EntitiesCharacters{
    
    private static $chars = [
        '[0xc2][0xa1]' => '¡',
        '[0xc2][0xa2]' => '¢',
        '[0xc2][0xa3]' => '£',
        '[0xc2][0xa4]' => '¤',
        '[0xc2][0xa5]' => '¥',
        '[0xc2][0xa6]' => '¦',
        '[0xc2][0xa7]' => '§',
        '[0xc2][0xa9]' => '©',
        '[0xc2][0xaa]' => 'ª',
        '[0xc2][0xab]' => '«',
        '[0xc2][0xac]' => '¬',
        '[0xc2][0xae]' => '®',
        '[0xc2][0xaf]' => '¯',
        '[0xc2][0xb0]' => '°',
        '[0xc2][0xb1]' => '±',
        '[0xc2][0xb2]' => '²',
        '[0xc2][0xb3]' => '³',
        '[0xc2][0xb4]' => '´',
        '[0xc2][0xb5]' => 'µ',
        '[0xc2][0xb6]' => '¶',
        '[0xc2][0xb7]' => '·',
        '[0xc2][0xb8]' => '¸',
        '[0xc2][0xb9]' => '¹',
        '[0xc2][0xba]' => 'º',
        '[0xc2][0xbb]' => '»',
        '[0xc2][0xbc]' => '¼',
        '[0xc2][0xbd]' => '½',
        '[0xc2][0xbe]' => '¾',
        '[0xc2][0xbf]' => '¿',
        '[0xc3][0x80]' => 'À',
        '[0xc3][0x81]' => 'Á',
        '[0xc3][0x82]' => 'Â',
        '[0xc3][0x83]' => 'Ã',
        '[0xc3][0x84]' => 'Ä',
        '[0xc3][0x85]' => 'Å',
        '[0xc3][0x86]' => 'Æ',
        '[0xc3][0x87]' => 'Ç',
        '[0xc3][0x88]' => 'È',
        '[0xc3][0x89]' => 'É',
        '[0xc3][0x8a]' => 'Ê',
        '[0xc3][0x8b]' => 'Ë',
        '[0xc3][0x8c]' => 'Ì',
        '[0xc3][0x8d]' => 'Í',
        '[0xc3][0x8e]' => 'Î',
        '[0xc3][0x8f]' => 'Ï',
        '[0xc3][0x90]' => 'Ð',
        '[0xc3][0x91]' => 'Ñ',
        '[0xc3][0x92]' => 'Ò',
        '[0xc3][0x93]' => 'Ó',
        '[0xc3][0x94]' => 'Ô',
        '[0xc3][0x95]' => 'Õ',
        '[0xc3][0x96]' => 'Ö',
        '[0xc3][0x97]' => '×',
        '[0xc3][0x98]' => 'Ø',
        '[0xc3][0x99]' => 'Ù',
        '[0xc3][0x9a]' => 'Ú',
        '[0xc3][0x9b]' => 'Û',
        '[0xc3][0x9c]' => 'Ü',
        '[0xc3][0x9d]' => 'Ý',
        '[0xc3][0x9e]' => 'Þ',
        '[0xc3][0x9f]' => 'ß',
        '[0xc3][0xa0]' => 'à',
        '[0xc3][0xa1]' => 'á',
        '[0xc3][0xa2]' => 'â',
        '[0xc3][0xa3]' => 'ã',
        '[0xc3][0xa4]' => 'ä',
        '[0xc3][0xa5]' => 'å',
        '[0xc3][0xa6]' => 'æ',
        '[0xc3][0xa7]' => 'ç',
        '[0xc3][0xa8]' => 'è',
        '[0xc3][0xa9]' => 'é',
        '[0xc3][0xaa]' => 'ê',
        '[0xc3][0xab]' => 'ë',
        '[0xc3][0xac]' => 'ì',
        '[0xc3][0xad]' => 'í',
        '[0xc3][0xae]' => 'î',
        '[0xc3][0xaf]' => 'ï',
        '[0xc3][0xb0]' => 'ð',
        '[0xc3][0xb1]' => 'ñ',
        '[0xc3][0xb2]' => 'ò',
        '[0xc3][0xb3]' => 'ó',
        '[0xc3][0xb4]' => 'ô',
        '[0xc3][0xb5]' => 'õ',
        '[0xc3][0xb6]' => 'ö',
        '[0xc3][0xb7]' => '÷',
        '[0xc3][0xb8]' => 'ø',
        '[0xc3][0xb9]' => 'ù',
        '[0xc3][0xba]' => 'ú',
        '[0xc3][0xbb]' => 'û',
        '[0xc3][0xbc]' => 'ü',
        '[0xc3][0xbd]' => 'ý',
        '[0xc3][0xbe]' => 'þ',
        '[0xc3][0xbf]' => 'ÿ'
    ];
    
    public static function convert($subject)
    {
        $search = array_keys(self::$chars);
        $replace = array_values(self::$chars);
        return str_replace($search, $replace, $subject);
    }
    
    public static function unconvert($subject)
    {
        $replace = array_keys(self::$chars);
        $search = array_values(self::$chars);
        return str_replace($search, $replace, $subject);
    }
}
