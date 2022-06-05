	$text = $input['title'];
		
		    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
			    // transliterate
			   $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

			    // remove unwanted characters
			    $text = preg_replace('~[^-\w]+~', '', $text);

			    // trim
			    $text = trim($text, '-');

			    // remove duplicated - symbols
			    $text = preg_replace('~-+~', '-', $text);

			    // lowercase
			    $slug = strtolower($text);

			    