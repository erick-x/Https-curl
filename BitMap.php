<?php
/**
 * 大数据，对无重复值排序
 * bitMap
 */
class BitMap
{
    private $map;
    private $bit_size;
    public function __construct() 
    {
        $this->map = [];
        $this->bit_size =64;
    }
    function set(int $i)
    {
        $this->map[$i/$this->bit_size] |=(1<<($i % $this->bit_size));
    }

    function clear(int $i)
    {
        $this->map[$i/$this->bit_size] &= ~(1<<($i % $this->bit_size));
    }
    function test(int $i)
    {
        return $this->map[$i/$this->bit_size] & (1<<($i % $this->bit_size));
    }

    public function init($map)
    {
        foreach($map as $v){
            $this->set($v);
        }
        ksort($this->map);
    }
    public function sort()
    {
        foreach($this->map as $k=>$v){
            for ($i=0; $i < $this->bit_size;  $i++) { 
                $bit = 1<<$i;
                $flg =  $bit&$v;
               if( $flg ){
                yield $k*$this->bit_size+$i;
                }
            }
        }
    }
}