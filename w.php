<?php
abstract class A {
    public $s; 

    public function __construct($s){
        $this->s = $s;
    } 

    public function info(){
        echo "this is my " . $this->s;
    }
}

class B extends A {

    public function info1(){
        echo "this is my dgdfgdfg " . $this->s;
    }
}
$p = new B("RRRR");
$p->info();

?>