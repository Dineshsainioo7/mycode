<?php

	/// video 
public function getPitchDetection($s3Key,$videoID)
	{
		if (!empty($s3Key) && !empty($videoID)) 
		{

			$upload_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/';
			$fileName = $s3Key;
			$json_array = [];
			
			exec('ffmpeg -y -i "'. __DIR__ ."/../../../upload/" . $fileName .'" -acodec pcm_u8 -b:a 8k -ac 1 -ar 11025 "'. __DIR__ . "/../../../upload/wav/".$fileName.'.wav"');
			
			if (file_exists(__DIR__ . "/../../../upload/wav/" . $fileName . '.wav')){
			    exec('aubiopitch -i "' . __DIR__ . "/../../../upload/wav/" . $fileName . '.wav"', $result);
			    $json_array['status'] = true;

			    foreach ($result as $row){
			        $split = explode(' ', $row);
			        $json_array['pitch'][] = array('time'=>$split[0], 'value'=>$split[1]);
			    }

			    unlink(__DIR__ . "/../../../upload/wav/" . $fileName . '.wav');

			    /// data base connection
           		$conn= new DBConnection();
	 			$db = $conn->mConnect();

			    $update = mysqli_query($db, "UPDATE tblreport SET Pitches='". mysqli_real_escape_string($db, json_encode($json_array['pitch'])) . "' WHERE VideoID='{$videoID}'");
			}else{
			    $json_array['status'] = false;
			    $json_array['message'] = 'Failed to convert into WAV file.';
			}

			return json_encode($json_array);

		}
	}

	public static function getPoseDetection($s3Key,$videoID)
	{
		if (!empty($s3Key) && !empty($videoID)) 
		{
			$upload_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/';
			$fileName = $s3Key;
			$json_array = [];

			if (file_exists(__DIR__ . '/../../../upload/' . $fileName)){
		    	exec('ffmpeg -y -i "'. __DIR__ . '/../../../upload/' . $fileName . '" -vf fps=1/5 "' . __DIR__ . '/../../../upload/images/' . $fileName . '"_%03d.jpg');
		    	
			}else{
			    $json_array['status'] = false;
			    $json_array['message'] = 'Failed to download video file.' . __DIR__ . '/../../../upload/' . $fileName;
			    header('Content-type: application/json');
			    echo json_encode($json_array);
			    exit();
			}

			$path = __DIR__ . '/../../../upload/images';
			//get all files having prefix out_
			$files = glob($path.'/' . $fileName . '_*');
			//var_dump($files);
			if ( count($files)<=0){
			    $json_array['status'] = false;
			    $json_array['message'] = "There are no. Frame Images";
			}
			foreach ($files as $row){
			    $file = $row;
			    if (function_exists('curl_file_create')) { // php 5.5+
			        $cFile = curl_file_create($file);
			    } else { //
			        $cFile = '@' . realpath($file);
			    }

			    $post_args = array('file' => $cFile);

			    $curl = curl_init();
			    $headers = array('accept: application/json','Content-Type: multipart/form-data');
			    curl_setopt($curl, CURLOPT_POST, true);
			    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			    //curl_setopt($curl, CURLOPT_USERPWD, "apiKey:FWBRr-Bu1fGrZeYGfQHNICN38IZchCzAjWyonKrdQf9G");

			    //curl_setopt($curl, CURLOPT_URL, "http://max-human-pose-estimator.max.us-south.containers.appdomain.cloud/model/predict");
			    curl_setopt($curl, CURLOPT_URL, "http://13.235.209.237:5000/model/predict");//hosted on docker container
			    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_args);
			    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			    $json_string = curl_exec($curl);
			    if (curl_errno($curl)) {
			        $json_array['status'] = false;
			        $json_array['message'] = "Error with curl response";

			        header('Content-type: application/json');
			        echo json_encode($json_array);

			        throw new Exception('Error with curl response: '.curl_error($curl));
			    }
			    $result = json_decode($json_string);
			    //echo $result->predictions;
			    list($width, $height) = getimagesize($file);

			    $json_array['status'] = true;
			    $json_array['pose'][] = array('file'=>$file, 'imageWidth'=>$width, 'imageHeight'=>$height, 'prediction'=>$result->predictions);
			    curl_close($curl);

			    unlink($file);
			}

			/// data base connection
           		$conn= new DBConnection();
	 			$db = $conn->mConnect();

			$update = mysqli_query($db, "UPDATE tblreport SET Poses='". mysqli_real_escape_string($db, json_encode($json_array['pose'])) . "' WHERE VideoID='{$videoID}'");

			return json_encode($json_array);			

		}
		
	}




?>