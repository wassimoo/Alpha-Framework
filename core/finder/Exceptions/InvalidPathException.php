<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 02/03/18
 * Time: 22:11
 */
namespace AlphaFinder;

Interface PathException
{
    public function  __construct($message, $code);

    public function __toString();
}

/**
 * Class InvalidPathException
 * General Path exception
 */
class InvalidPathException extends \Exception implements PathException
{
    public function __construct($message = "Invalid path exception ", $code = 0x00200)
    {
        parent::__construct($message, $code);
    }
    public function __toString()
    {
        return $this->message ." Error code ". $this->getCode();
    }
}

/**
 * Class PathNotFoundException
 * Released when trying to open file / directory of specific path
 */
class  PathNotFoundException extends InvalidPathException
{
    public function __construct($message = "Couldn't find specified directory, Invalid path exception " , $code = 0x002001)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Class UnreadablePathException
 * Released when read access is not granted to user
 */
class UnreadablePathException extends InvalidPathException
{
    public function __construct($message = "Read access is not granted for this directory", $code = 0x00202)
    {
        parent::__construct($message, $code);
    }
}