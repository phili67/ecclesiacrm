<?php
class Foo{
    /**
     * something describes this method
     */
    function bar(){
        /** code here... */
    }
}
$ref = new ReflectionClass('Foo');
print_r($ref->getMethod('bar')->getDocComment()); //will print out the method document as expected.
?>

