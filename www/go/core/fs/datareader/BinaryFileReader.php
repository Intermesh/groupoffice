<?php
namespace go\core\fs\datareader;
/**
 * A simple class to read variable byte length binary data.
 * This is basically is a better replacement for unpack() function
 * which creates a very large associative array.
 *
 * @author Shubham Jain <shubham.jain.1@gmail.com>
 * @example https://github.com/shubhamjain/PHP-ID3
 * @license MIT License
 */
class BinaryFileReader
{
    /**
     * size of block depends upon the variable defined in the next array element.
     */
    const SIZE_OF = 1;
    /**
     * Block is read until NULL is encountered.
     */
    const NULL_TERMINATED = 2;
    /**
     * Block is read until EOF  is encountered.
     */
    const EOF_TERMINATED = 3;
    /**
     * Block size is fixed.
     */
    const FIXED = 4;
    /**
     * Datatypes to transform the read block
     */
    const INT = 5;
    const FLOAT = 6;
    /**
     * file handle to read data
     */
    private $fp;
    /**
     * Associative array of Varaibles and their info ( TYPE, SIZE, DATA_TYPE)
     * In special cases it can be an array to handle different types of block data lengths
     */
    private $map;
    public function __construct($fp, array $map)
    {
        $this->fp = $fp;
        $this->setMap($map);
    }
    public function setMap($map)
    {
        $this->map = $map;
        foreach ($this->map as $key => $size) {
            //Create property from keys of $map
            $this->$key = null;
        }
    }
    public function read()
    {
        if (feof($this->fp)) {
            return false;
        }
        foreach ($this->map as $key => $info) {
            $this->fillTag($info, $key);
            if (isset($info[2])) {
                $this->convertBinToNumeric($info[2], $key);
            }
            $this->$key = ltrim($this->$key, "\0x");
        }
        return $this;
    }
    private function nullTeminated($key)
    {
        while ((int) bin2hex(($ch = fgetc($this->fp))) !== 0) {
            $this->$key .= $ch;
        }
    }
    private function eofTerminated($key)
    {
        while (!feof($this->fp)) {
            $this->$key .= fgetc($this->fp);
        }
    }
    private function fillTag($tag, $key)
    {
        switch ($tag[0]) {
            case self::NULL_TERMINATED:
                $this->nullTeminated($key);
                break;
            case self::EOF_TERMINATED:
                $this->eofTerminated($key);
                break;
            case self::SIZE_OF:
                //If the variable is not an integer return false
					 if (!( $tag[1] = $this->{$tag[1]} )) {
                    return false;
					 }
            default:
                //Read as string
                $this->$key = fread($this->fp, $tag[1]);
                break;
        }
    }
    private function convertBinToNumeric($value, $key)
    {
        switch ($value) {
            case self::INT:
                $this->$key = intval(bin2hex($this->$key), 16);
                break;
            case self::FLOAT:
                $this->$key = (float)bin2hex($this->$key);
                break;
        }
    }
}
