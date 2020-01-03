<?php
namespace Think;
class MyStr{
    private $str = '';
    private $arr = array();
    private $len = 0;
    public function __construct( $str){
        $this->str = $str;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++ ){
            $cStr = $str[$i];
            if(ord($cStr) > 127){
                $cStr = substr($str, $i , 3);
                $i += 2;
            }
            array_push($this->arr, $cStr);
        }
        $this->len = count($this->arr);
    }
    public function getIndex( $idx ){
        return $this->arr[$idx];
    }
    public function getLength(){
        return $this->len;
    }
}
?>