<?php
require_once('../Barstool.php');


$bs = new Barstool(
    array(
        'adaptor'=>'sqlite'
    )
);

/*
    delete everything
*/
$bs->nuke();

/*
    make a new object to store
*/
$obj = new stdClass;
$obj->foo = 'boo';
$obj->x = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus nisl dolor, pulvinar id, pharetra a, egestas nec, ante. Duis scelerisque eleifend metus. Sed non odio id odio varius rutrum. Pellentesque congue commodo lacus. In semper pede lacinia felis. Morbi mollis molestie lorem. Morbi suscipit libero. Quisque ut erat sit amet elit aliquam nonummy. Donec tortor. Aliquam gravida ullamcorper pede. Praesent eros. Sed fringilla ligula sed odio pharetra imperdiet. Integer aliquet quam vitae nibh. Nam pretium, neque non congue vulputate, odio odio vehicula augue, sit amet gravida pede massa ac lectus. Curabitur a libero vitae dui sagittis aliquet. Ut suscipit. Curabitur accumsan sem a urna. Ut elit pede, vulputate sed, feugiat quis, congue sed, lacus.';
$obj->key = 'callbacktestobj';

/*
    store it and delete it
*/
$bs->save($obj);
unset($obj);

/*
    define a simple callback function
*/
function simple_callback($obj) {
    echo "we are in the simple callback\n";
    var_dump($obj);
}

/*
    retrieve it and call an external function on it
*/
$obj = $bs->get('callbacktestobj', 'simple_callback');

/*
    retrieve it and call an anonymous function on it
*/
$obj = $bs->get('callbacktestobj', function($obj){
    echo "we are within an anonymous function callback\n";
    var_dump($obj);
});


$bs->all(function($obj) {
    echo "we are executing this on a array of all data\n";
    var_dump($obj);
});

$bs->each(function($obj) {
    echo "we are executing this on each record in the store\n";
    var_dump($obj);
});


$bs->remove('callbacktestobj');

?>