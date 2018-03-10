<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 27/02/18
 * Time: 17:57
 */

namespace AlphaFinder;

require_once __DIR__ . "/DirStructure.php";
require_once __DIR__ . "/Exceptions/InvalidPathException.php";

class Finder
{

    private $paths;/* dirs + files + unreadable */
    private $dirs;
    private $files;
    private $depth;
    private $ignore;
    /*public function sort(critic, ASC/DESC ){
        optional, may be called by user to optimise search for specific dir later
    }*/

    /**
     * Finder constructor.
     * @param String $parentPath
     * @param int $depth
     * @param array $ignore
     * @throws InvalidPathException
     * @throws PathNotFoundException
     * @throws UnreadablePathException
     */
    public function __construct(String $parentPath, array $ignore = NULL, int $depth = 2)
    {
        $this->paths = array();
        $this->files = array();
        $this->dirs = new Dir($parentPath);
        $this->depth = $depth;
        $this->ignore = $ignore?? array();
        $this->getAllPaths($this->dirs, $depth);
    }

    /**
     * @param Dir $parentDir
     * @param int $depth
     * @throws InvalidPathException
     * @throws PathNotFoundException
     * @throws UnreadablePathException
     */
    private function getAllPaths(Dir $parentDir, $depth = 20)
    {
        // reached maximum depth return ;
        // Notice : negative value will pass the test so we will have unlimited depth
        if ($depth == 0) {
            return;
        }

        // specified path does not exist
        if (!file_exists($parentDir->fullName))
            throw new PathNotFoundException();

        // read access is not granted Simply ignore path and notify user through logs
        if (!is_readable($parentDir->fullName)) {
            error_log("WARNING (Finder.php) " . date("Y-m-d H:i:s") . " : " . new UnreadablePathException() . " returned while trying to access " . $parentDir->fullName . ", file was ignored\n", 3, __DIR__ . "/../../logs/finder.log");
            return;
        }


        //get dir handler instance of current parent directory
        $dir = dir($parentDir->fullName);

        // in case of failure , throw general exception error.
        if ($dir == NULL || $dir == false)
            throw new InvalidPathException();

        //read all files one by one and classify them
        while (($entry = $dir->read()) !== false) {
            //escape current and previous directories
            if ($entry == "." || $entry == ".." || in_array($entry,$this->ignore))
                continue;

            $entry = $parentDir->fullName . '/' . $entry;
            array_push($this->paths, $entry);

            if (is_dir($entry)) {
                array_push($parentDir->directories, new Dir($entry));
                $this->getAllPaths(end($parentDir->directories), $depth - 1);
            } else if (is_file($entry)) {
                array_push($this->files, $entry);
                array_push($parentDir->files, new File($entry));
            }
        }
    }

    /**
     * @param String $expr
     * @return bool|mixed
     */
    public function findDir(String $expr)
    {
        if (empty($this->paths)) {
            return false;
        }

        foreach ($this->paths as $path) {
            if (preg_match("/.*" . $expr . "(\/)?\z/iD", $path) == 1)
                return $path;
        }
        return false;
    }

    /**
     * @param String $expr
     * @return bool|mixed
     */
    public function findFile(String $expr)
    {
        if (empty($this->files)) {
            return false;
        }

        foreach ($this->files as $file) {
            if (preg_match("/.*" . $expr . "\z/iD", $file))
                return $file;
        }
        return false;
    }
    //public function whereIs($file)

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @return Dir
     */
    public function getDirs(): Dir
    {
        return $this->dirs;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth(int $depth)
    {
        $this->depth = $depth;
    }
}