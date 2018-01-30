<?php

namespace App\Component\Console;

class Table
{
    
    public $width = [];
    
    public $height = 0;
    
    public function start()
    {
        if ($this->height) {
            //printf("\033[{$this->height}A\e[?25l");
            printf("\033[{$this->height}A");
            $this->height = 0;
        }
    }
    
    public function setWidth(array $width)
    {
        $this->width = $width;
    }
    
    public function show(array $data)
    {
        foreach ($data as $index => $item) {
            if ($this->width[$index] > $this->getLength($item)) {
                echo ' ' . $item . str_repeat(' ', $this->width[$index] - $this->getLength($item)) . ' ';
            } else {
                echo ' ' . $item . ' ';
            }
        }
        echo "\n";
        $this->height++;
    }
    
    
    private function getLength($word)
    {
        $num = 0;
        $len = mb_strlen($word);
        for ($i = 0; $i < $len; $i++) {
            $w = mb_substr($word, $i, 1);
            if (strlen($w) == 1) {
                $num++;
            } else {
                $num += 2;
            }
        }
        return $num;
    }
    
    public function stop()
    {
        //printf("\033[?25h");
    }
}