<?php  
   function encrypt_decrypt($action, $string) {
		$output = false;
		$encrypt_method = "AES-256-CBC";
		//This is my secret key
		$secret_key = '5b7cfd2937f2681f1d9139e5963312a39266ce52df93ded48f93d0f10b3c35ba';
		//This is my secret iv
		$secret_iv = '566ce52df93ded48f93d0f10b3c35bab7cfd2937f2681f1d9139e5963312a392';
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
		return $output;
	}

?>
	