<?php 
include_once 'database.php';
include_once 'crud_opration.php';

$database = new Database();
$conn = $database->getConnection();

$obj = new dynamicModel($conn, "studends");

$all = $obj->fetchData(); // 0r $all = $obj->fetchData([], "*", "id DESC");

echo "<pre> all sudents :\n "; print_r($all); echo "</pre>";


$selected = $obj->fetchData([], "id,student_name", "id DESC" ,"2");
/* or 
$selected = $obj->fetchData([], ["id","student_name"], "id DESC" ,"2"); */
echo "<pre> Some columns sudents :\n "; print_r($selected); echo "</pre>";

?>