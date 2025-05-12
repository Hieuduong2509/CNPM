<?php

    define("HOST","localhost");
    define("USER","root");
    define("PASS","");
    define("DB","computershop");
    function create_connection(){

        $conn=new mysqli(HOST,USER,PASS,DB);
        if ($conn->connect_error) {
            die("fail to connect". $conn->connect_error);
        }
        return $conn;
    }

?>