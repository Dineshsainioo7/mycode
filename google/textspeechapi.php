<?php

use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\SpeechTranscriptionConfig;
use Google\Cloud\VideoIntelligence\V1\VideoContext;

// Include Google Cloud dependendencies using Composer
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require_once('google_auth.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/google_auth.php';
// [START video_speech_transcription_gcs]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;

	////  video speech transcribe 
	public static function analyzeTranscribe()
	{
		
		$google_link = "gs://video-sample-data/interview.mp3";
		$video_id    = 540;
		if (!empty($google_link) && !empty($video_id)) 
		{

			$options = [];
					# set configs
			$features = [Feature::SPEECH_TRANSCRIPTION];
			$speechTranscriptionConfig = (new SpeechTranscriptionConfig())
			    ->setLanguageCode('en-US')
			    ->setEnableAutomaticPunctuation(true);
			$videoContext = (new VideoContext())
			    ->setSpeechTranscriptionConfig($speechTranscriptionConfig);
			# instantiate a client
			$client = new VideoIntelligenceServiceClient();
			# execute a request.
			$operation = $client->annotateVideo([
			    'inputUri' => $google_link,
			    'features' => $features,
			    'videoContext' => $videoContext
			]);
			
			//file_put_contents("output.txt", 'Append', FILE_APPEND);
			//print('Processing video for speech transcription...' . PHP_EOL);

			# Wait for the request to complete.
			$operation->pollUntilComplete($options);
			# Print the result.
			if ($operation->operationSucceeded()) {
			    $result = $operation->getResult();
				
			    # there is only one annotation_result since only
			    # one video is processed.
			    $annotationResults = $result->getAnnotationResults()[0];
			    $speechTranscriptions = $annotationResults ->getSpeechTranscriptions();
				//var_dump($speechTranscriptions);
				$speech ='';
			    foreach ($speechTranscriptions as $transcription) {
			        # the number of alternatives for each transcription is limited by
			        # $max_alternatives in SpeechTranscriptionConfig
			        # each alternative is a different possible transcription
			        # and has its own confidence score.
					//printf('Transcribe: %s' . PHP_EOL, $transcription);
					//var_dump($transcription);
			        foreach ($transcription->getAlternatives() as $alternative) {
			            //print('Alternative level information' . PHP_EOL);
			            //printf('Transcript: %s' . PHP_EOL, $alternative->getTranscript());
			            //printf('Confidence: %s' . PHP_EOL, $alternative->getConfidence());
						$speech .= $alternative->getTranscript();
						/*
			            print('Word level information:');
			            foreach ($alternative->getWords() as $wordInfo) {
			                printf(
			                    '%s s - %s s: %s' . PHP_EOL,
			                    $wordInfo->getStartTime()->getSeconds(),
			                    $wordInfo->getEndTime()->getSeconds(),
			                    $wordInfo->getWord()
			                );
			            }*/
			        }		
			    }
				$str['result'] = true;
				$str['transcribeText'] = $speech;
				  /// data base connection
		           	$conn= new DBConnection();
			 		$db = $conn->mConnect();

			 	print_r($speech);
			 	exit();	
			    // $update = mysqli_query($db, "UPDATE tblreport SET DecodedText='". mysqli_real_escape_string($db, $speech) . "' WHERE VideoID='{$video_id}'");

				return $str;
				//printf('Transcript: %s' . PHP_EOL, $speech);
			}else{
				$str['result'] = false;
				$str['message'] = "Failed to Transcribe File";
				return json_encode($str);
			}
			$client->close();
			// [END video_speech_transcription_gcs]
		}else{
				$response->getBody()->write(json_encode(['error' => 'google link and video id not avablie']));
	    		return $response;
		}	





?>