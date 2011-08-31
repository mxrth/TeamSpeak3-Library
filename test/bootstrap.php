<?php

spl_autoload_register( function($class) {
    $path = str_replace("\\","/", $class);
    $path = "../".$path.".php";
    if(  file_exists( $path) )
        require_once $path;
    else
        echo "cannot load ".$path;
})

?>
