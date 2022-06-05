<?php


	public static function getVideoThumble($fileName)
	{
		$fileName = "Untitled_3_480p_1594282648.mp4";

		if (file_exists(__DIR__ . '/../../../upload/'.$fileName)){
			// echo "working";
			// exit();
		exec('C:\ffmpeg\bin -y -i "'. __DIR__ . '/../../../upload/' . $fileName . '" -vframes 1 "' . __DIR__ . '/../../../upload/' . $fileName . '_%03d.jpg" 2>&1',$return);
		echo "create thumbnail";
		exit();
		if (file_exists(__DIR__ . '/../../../upload/thumb/' . $fileName . '_001.jpg'))
		{
			echo "working Payment";
			exit();
			$fileURL = 'https://app.uspeeknow.com/upload/thumb/'.$fileName . '_001.jpg';
		}

		return $fileURL;
		}
	}


?>