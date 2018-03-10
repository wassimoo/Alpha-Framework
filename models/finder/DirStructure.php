<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 03/03/18
 * Time: 01:15
 */

namespace AlphaFinder;

class UnivFile
{
    public $fullName;
    public $name;
    public $size;
    public $owner;
    public $creationDate;

    public function __construct(String $fullName)
    {
        $this->fullName = $fullName;
        $name = "";
        $size = 0;
        $owner = "";
        $creationDate = 0;
    }
}

class Dir extends UnivFile
{
    /*subs*/
    public $directories;
    public $files;

    public function __construct(String $fullName)
    {
        parent::__construct($fullName);
        $this->directories = array();
        $this->files = array();
    }
}

class File extends UnivFile
{
    public $extension;
    public $mimeType;

    public function __construct(String $fullName)
    {
        parent::__construct($fullName);
        // TODO : get $extension and $mimeType
    }
}
