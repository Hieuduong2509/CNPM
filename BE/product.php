<?php 
    require_once("db.php");
    function get_products(){

        $conn=create_connection();
        $sql="SELECT * FROM product ";
        $products= $conn->query($sql) ;
        
        $array=array();
        for ($i= 1;$i<=$products->num_rows;$i++){
            $row=$products->fetch_assoc();
            $array[]=$row;
        }
        return $array;
    }
    
?>