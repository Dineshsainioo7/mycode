<?php 
declare(strict_types=1);
namespace App\V1\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'].'/aws/aws-autoloader.php';
use Aws\Rekognition\RekognitionClient;
use App\V1\DBConnection;

/*
*
*	facedetection classs
*/

class FaceDetection{

    public function __construct()
    {
        global $client;
        global $bucket;

        $config = [
            'version' => 'latest',
            'region' => 'ap-south-1',
            'credentials' => [
                'key' => 'AKIAJFJ2HZWPH32OOS2A',
                'secret' => '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',
            ]
        ];

        try{
            $client = new RekognitionClient($config);
        }catch (Exception $e){
            echo $e;
        }

        $bucket = 'transcribe-docs';
    }

    public function startDetection($videoUri){
        global $client;
        global $bucket;
        $json_array = array();

        try{
            $result = $client->startFaceDetection([
                'Video' => [ // REQUIRED
                    'S3Object' => [
                        'Bucket' => $bucket,
                        'Name' => $videoUri,
                    ],
                ],
                'FaceAttributes' => 'ALL',
            ]);
            $jobId = $result['JobId'];
            $json_array['Status']=true;
            $json_array['jobId']=$jobId;
        }catch(Exception $e){
            $json_array['Status'] = false;
            $json_array['message'] = $e->getMessage();
        }
        return $json_array;
    }


    public function getResult($jobId,$videoID){

    	 /// data base connection
           	$conn= new DBConnection();
	 		$db = $conn->mConnect();

        global $client;
        $json_array = array();

        try{
            //getContentModeration
            $result = $client->getFaceDetection([
                'JobId' => $jobId,
            ]);
            //'MaxResults'=> 10,

            $json_array['Status'] = true;
            $json_array['JobStatus'] = $result["JobStatus"];

            if ($result["JobStatus"]=="SUCCEEDED"){
                $faces = $result["Faces"];
                foreach ($faces as $row){
                    $newFaces = array();
                    $newFaces['Emotions'] = $row['Face']['Emotions'];
                    $newFaces['Smile'] = $row['Face']['Smile'];
                    $newFaces['Pose'] = $row['Face']['Pose'];
                    $newFaces['EyesOpen'] = $row['Face']['EyesOpen'];

                    $json_array['Faces'][]['Face'] = $newFaces;
                }


                try{
                    $data = json_encode($json_array);
                  
                    // $update = mysqli_query($db, "UPDATE tblreport SET FaceDetection='". mysqli_real_escape_string($db,$data) . "' WHERE VideoID='{$videoID}'");
                    // $updateSQL = "UPDATE tblreport SET FaceDetection=:data WHERE VideoID=:videoID";

                    // $rsUpdate = $db->prepare($updateSQL);
                    // $rsUpdate->bindParam(':videoID', $videoID, PDO::PARAM_STR);
                    // $rsUpdate->bindParam(':data', $data, PDO::PARAM_STR);
                    // $rsUpdate->execute();
                    return $json_array;

                }catch(Exception $e){
                    $json_array['Status'] = false;
                    $json_array['message'] = $e->getMessage();
                }

            }

        }catch(Exception $e){
            //echo $e->getMessage() . PHP_EOL;
            $json_array['Status'] = false;
            $json_array['message'] = $e->getMessage();
        }
        return $json_array;
    }


    /// get face exercise
     public function getBodyExerciseResult($jobId){

        global $client;
        $json_array = array();

        try{
            //getContentModeration
            $result = $client->getFaceDetection([
                'JobId' => $jobId,
            ]);
            //'MaxResults'=> 10,

            $json_array['Status'] = true;
            $json_array['JobStatus'] = $result["JobStatus"];

            if ($result["JobStatus"]=="SUCCEEDED"){
                $faces = $result["Faces"];
                foreach ($faces as $row){
                    $newFaces = array();
                    $newFaces['Emotions'] = $row['Face']['Emotions'];
                    $newFaces['Smile'] = $row['Face']['Smile'];
                    $newFaces['Pose'] = $row['Face']['Pose'];
                    $newFaces['EyesOpen'] = $row['Face']['EyesOpen'];

                    $json_array['Faces'][]['Face'] = $newFaces;
                }

                try{
                    return $json_array;
                }catch(Exception $e){
                    $json_array['Status'] = false;
                    $json_array['message'] = $e->getMessage();
                }

            }

        }catch(Exception $e){
            //echo $e->getMessage() . PHP_EOL;
            $json_array['Status'] = false;
            $json_array['message'] = $e->getMessage();
        }
        return $json_array;
    }

}

////////////////////////////////////////////////////////////////////////////////////////////

public static function recursiveFaceDetection($jobId,$videoID)
    {
        if (!empty($jobId) && !empty($videoID)) {
            $face_detection = new FaceDetection();
            $result = $face_detection->getResult($jobId,$videoID);


            // self function call
            do {
            try {
            $result = $face_detection->getResult($jobId,$videoID);

            if ($result['JobStatus'] == "SUCCEEDED") {
            break;
            return $result;
            }
            } catch (Exception $e) {
            $json_array['Status'] = false;
            $json_array['message'] = $e->getMessage();
            return $json_array;

            }

            sleep(7);
            } while ($result['JobStatus'] == "IN_PROGRESS");

            }
        
    }
 ?>