<?php

class LogSeekableIterator implements SeekableIterator
{

    private $handle;
    private $position = 0;          // Номер записи, запрашиваемый пользователем
    private $realPosition = 0;      // Номер записи, на котором находится курсор
    private $pointer = 0;           // Положение указателя внутри файла
    private $currentValue;
    private $map = array(0);

    public function __construct($filePath, $recSeparator = "\n", $valSeparator = "\t")
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
        $this->recSeparator = $recSeparator;
        $this->valSeparator = $valSeparator;
    }

    /**
     * Seeks to a position
     * @param int $position
     * The position to seek to.
     * @return void No value is returned.
     */
    public function seek($position)
    {
        $this->position = $position;
    }

    /**
     * Return the current element
     * @return mixed Can return any type.
     */
    public function current()
    {
        if ($this->position == $this->realPosition) {
            return $this->getValue();
        } else if ($this->position > $this->realPosition) {
            do {
                $this->next();
            } while ($position > $this->position && $this->valid());
        } else {
            do {
                $this->prev();
            } while ($position < $this->position && $this->valid());
        }

        fseek($this->handle, $this->position);  // fgetc move pointer
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
     * Move forward to prev element
     * @return void Any returned value is ignored.
     */
    public function prev()
    {
        $this->position--;
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
        $this->realPosition = 0;
        $this->pointer = 0;
        rewind();
    }

    private function getValue()
    {
        if($this->currentValue) {
            $this->currentValue;
        }
    }
    
    private function test() {
        while(($char = fgetc($this->handle)) !== false) {
            if($char === $this->recSeparator) {
                $this->currentPosition++;
                $map[$this->currentPosition] = ftell($this->handle);
            }
        }
        $this->lastPosition = $this->currentPosition;
        $map[$this->currentPosition + 1] = EOF;
        fseek($this->handle, $this->pointer);
    }

}
