<?php

class FileSeekableIterator implements SeekableIterator
{

    private $handle;
    private $position = 0;

    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("File " . $filePath . " not exist!");
            return;
        }

        if (!$handle = fopen($filePath, "r")) {
            throw new Exception("File " . $filePath . " can't be read");
            return;
        }

        $this->handle = $handle;
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Seeks to a position
     * @param int $position
     * The position to seek to.
     * @return void No value is returned.
     */
    public function seek($position)
    {
        if(!is_numeric($position)) {
            trigger_error(get_class()  . '::seek() parameter must be integer, ' . gettype($position) . ' given!', E_USER_ERROR);
            return;
        } else if(!is_int($position)) {
            trigger_error(get_class() . '::seek() parameter must be integer, ' . gettype($position) . ' given!', E_USER_NOTICE);
        }
        $this->position = (int)$position;
    }

    /**
     * Return the current element
     * @return mixed Can return any type.
     */
    public function current()
    {
        if($this->position < 0) {
            return false;
        }
        fseek($this->handle, $this->position);
        return fgetc($this->handle);
    }

    /**
     * Move forward to next element
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     * @return scalar scalar on success, or NULL on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns TRUE on success or FALSE on failure.
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
    }

}
