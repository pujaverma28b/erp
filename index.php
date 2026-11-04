<?php 
include_once 'database.php';
include_once 'crud_opration.php';

$database = new Database();
$conn = $database->getConnection();

$model = new dynamicModel($conn, "studends");

$data = [
    "student_name"  => "Puja Verma",
    "age"   => 23,
    "city"   => "varanasi",

];

// Insert record

try {
    /* $review = $model->insertData($data, true);
    print_r($review);exit; */
    $lastId = $model->insertData($data);
    if( $lastId){
        echo "✅ Record inserted 1 successfully!";
        
        $studentDataModel  = new dynamicModel($conn, "studends_data");
        $data1 = [
                "paren_id"  => $lastId,
                "age"   => 44,
                "city"   => "rr",

            ];
        $second_data = $studentDataModel->insertData($data1);
        if ($second_data) {
            echo "✅ Record inserted 2 successfully!";
        } else {
            echo "❌ Insert failed to studends_data";
        }
    }else{
        echo "❌ Insert failed to studends";
    }
    
} catch (Exception $e) {
    echo "⚠️ Error: " . $e->getMessage();
}


/*if ($conn) {
    echo "✅ Database connected successfully!";
} else {
    echo "❌ Connection failed!";
} */

// puja
