<?php

class LogSeekableIterator implements SeekableIterator
{

    private $handle;
    private $recSeparator;
    private $valSeparator;
    private $position = 0;              // Номер записи, запрашиваемый пользователем
    private $map = array();             // "Карта" лога, хранит указатели на начало записей
    private $eof = false;               // Достигнут конец файла. Хранит указатель на несуществующую запись.

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

        while (!$this->eof                                  // Пока не достигнут конец файла
        && !isset($this->map[$this->position])) {           // Указатель на начало запрашиваемой записи уже имеется
            $this->getNextMapPoint();
        }

        if (isset($this->map[$this->position])) {
            $start = $this->map[$this->position][0];
            $length = $this->map[$this->position][1] - $start;
            fseek($this->handle, $start);
            return explode($this->valSeparator, fread($this->handle, $length));
        } else {
            return false;
        }
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
        rewind($this->handle);
    }

    private function getNextMapPoint()
    {
        // Указатель на конец последней найденной записи + разделитель
        $start = count($this->map) ? $this->map[count($this->map) - 1][1] + 1 : 0;
        fseek($this->handle, $start);

        while (($char = fgetc($this->handle)) !== false && $char !== $this->recSeparator) {
            ;
        }

        if ($char === false) {
            $this->eof = true;
        }

        $end = ftell($this->handle) - 1;

        $this->map[] = array($start, $end);
    }

}
