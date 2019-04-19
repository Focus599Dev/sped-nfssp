<?php
namespace NFePHP\NFs\SP\Exception;

/**
 * @category   NFePHP
 * @package    NFePHP\NFs\SP\Exception
 * @copyright  Copyright (c) 2008-2017
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O.Barbosa <marlon.academi@gmail.com>
 * @link       http://github.com/nfephp-org/sped-nfssp for the canonical source repository
 */

class DocumentsException extends \InvalidArgumentException implements ExceptionInterface
{
    public static $list = [
        0 => "O txt tem um campo não definido {{msg}}",
    ];
    
    public static function wrongDocument($code, $msg = '')
    {
        $msg = self::replaceMsg(self::$list[$code], $msg);
        return new static($msg);
    }
    
    private static function replaceMsg($input, $msg)
    {
        return str_replace('{{msg}}', $msg, $input);
    }
}
