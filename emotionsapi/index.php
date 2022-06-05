<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//require_once "conn.php";

if (!isset($_REQUEST['data'])){
    http_response_code(400);
    exit();
}
$text = "Api  Working For Home";
$post_args = [
    'text' => test_input($text),
    //"features" => ['sentiment']
];
$headers = array('Accept: application/json','Content-Type: multipart/form-data');

try{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_args);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "apiKey:FWBRr-Bu1fGrZeYGfQHNICN38IZchCzAjWyonKrdQf9G");

    //URL With Features examples
    //curl_setopt($curl, CURLOPT_URL, "https://api.eu-gb.natural-language-understanding.watson.cloud.ibm.com/instances/68ad53f9-f757-44ea-b782-6c1e20320892/v1/analyze?version=2019-07-12&features=sentiment,keywords,entities&entities.emotion=true&entities.sentiment=true&keywords.emotion=true&keywords.sentiment=true");
    //"https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27&text=" . encodedQuery ."&features=sentiment&language=en&concepts.limit=8&entities.limit=50&keywords.limit=50&relations.model=en-news&semantic_roles.limit=50";
    curl_setopt($curl, CURLOPT_URL, "https://api.eu-gb.natural-language-understanding.watson.cloud.ibm.com/instances/68ad53f9-f757-44ea-b782-6c1e20320892/v1/analyze?version=2019-07-12&features=sentiment,keywords,entities,emotion,categories,concepts");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        throw new Exception('Error with curl response: '.curl_error($curl));
    }
    //echo $result;

    curl_close($curl);

}catch (Exception $e){
    echo $e;
}


$json_array = array();
if ($result){
    $json_array['Status']=true;
    $json_array['data'] = json_decode($result);
}else{
    $json_array['Status'] = false;
    $json_array['message'] = $result;
}


header('Content-Type: application/json');
echo json_encode($json_array);


function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?><?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//require_once "conn.php";

if (!isset($_REQUEST['data'])){
    http_response_code(400);
    exit();
}
$text = $_REQUEST['data'];
$post_args = [
    'text' => test_input($text),
    //"features" => ['sentiment']
];
$headers = array('Accept: application/json','Content-Type: multipart/form-data');

try{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_args);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "apiKey:FWBRr-Bu1fGrZeYGfQHNICN38IZchCzAjWyonKrdQf9G");

    //URL With Features examples
    //curl_setopt($curl, CURLOPT_URL, "https://api.eu-gb.natural-language-understanding.watson.cloud.ibm.com/instances/68ad53f9-f757-44ea-b782-6c1e20320892/v1/analyze?version=2019-07-12&features=sentiment,keywords,entities&entities.emotion=true&entities.sentiment=true&keywords.emotion=true&keywords.sentiment=true");
    //"https://gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27&text=" . encodedQuery ."&features=sentiment&language=en&concepts.limit=8&entities.limit=50&keywords.limit=50&relations.model=en-news&semantic_roles.limit=50";
    curl_setopt($curl, CURLOPT_URL, "https://api.eu-gb.natural-language-understanding.watson.cloud.ibm.com/instances/68ad53f9-f757-44ea-b782-6c1e20320892/v1/analyze?version=2019-07-12&features=sentiment,keywords,entities,emotion,categories,concepts");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        throw new Exception('Error with curl response: '.curl_error($curl));
    }
    //echo $result;

    curl_close($curl);

}catch (Exception $e){
    echo $e;
}


$json_array = array();
if ($result){
    $json_array['Status']=true;
    $json_array['data'] = json_decode($result);
}else{
    $json_array['Status'] = false;
    $json_array['message'] = $result;
}


header('Content-Type: application/json');
echo json_encode($json_array);



?>