<?php 
/**
 * @author: xiaojiang 2014-01-08
 * php 建立分词树
 * */
namespace Think;
class Tree{
    public $w = '';
    public $subT = array();
    public $isEnd = false;
    public function __construct($w= '' , $isEnd = false){
        if(!empty($w)){
            $this->w = $w;
            $this->isEnd = $isEnd;
        }
    }
    public function insert( $str ){
        $len = strlen($str);
        if(!$len) return ;
        $scope = $this;
        for( $i = 0; $i< $len; $i++ ){
            //判断汉字
            $cStr = $str[$i];
            if( ord( $cStr ) > 127 ){
                $cStr = substr($str, $i, 3);
                $i += 2;
            }
            $scope = $scope->insertNode( $cStr );
        }
        $scope->isEnd = true;
    }
    private function &insertNode(  $w ){
        $t = $this->hasTree( $w );
        if( !$t ){
            $t =  new Tree( $w );
            array_push($this->subT, $t );
        }
        return $t;
    }
    public function &hasTree($w){
        foreach ($this->subT as $t){
            if($t->w == $w)
                return $t;
        }
        return false;
    }
}
?>