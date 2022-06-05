<?php

		public static function getDetectSentiment($text, $videoID) {


		if (!empty($text) && !empty($videoID)) {
			// echo "string";

			$conn = self::db();
			$post_args = [
				'text' => self::test_input($text),
			];
			$headers = array('Accept: application/json', 'Content-Type: multipart/form-data');

			try {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post_args);
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($curl, CURLOPT_USERPWD, "apiKey:FWBRr-Bu1fGrZeYGfQHNICN38IZchCzAjWyonKrdQf9G");

				curl_setopt($curl, CURLOPT_URL, "https://api.eu-gb.natural-language-understanding.watson.cloud.ibm.com/instances/68ad53f9-f757-44ea-b782-6c1e20320892/v1/analyze?version=2019-07-12&features=sentiment,keywords,entities,emotion,categories,concepts");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				$result = curl_exec($curl);
				if (curl_errno($curl)) {
					throw new Exception('Error with curl response: ' . curl_error($curl));
				}

				curl_close($curl);
			} catch (Exception $e) {
				echo $e;
			}
			if ($result) {


				$json_array['Status'] = true;
				$json_array['data'] = json_decode($result);
				$update = mysqli_query($conn, "UPDATE tblreport SET Sentiments='" . mysqli_real_escape_string($conn, $result) . "' WHERE VideoID='{$videoID}'");
			} else {
				$json_array['Status'] = false;
				$json_array['message'] = $result;
			}

			//header('Content-Type: application/json');
			return  json_encode($json_array);
			//exit();
		}

	}



?>