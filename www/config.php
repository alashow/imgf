<?php 
	ini_set('display_errors', 0);

	$config['page_title'] = "Image Fetcher";
	$config['blog'] = "melisica";
	$config['default_count'] = "15";

	//Redundant things..
	$config['derp_mode'] = isset($_GET['derp_mode']); //enable it at your own risk!

	if ($config['derp_mode']) {	
		$config['colors'] = ['red darken-1', 'red darken-2', 'red darken-3',
							 'purple darken-1', 'purple darken-4',
							 'light-blue darken-3', 'cyan darken-4',
							 'blue darken-1', 'blue darken-3', 'blue darken-4',
							 'teal darken-1', 'teal darken-3', 'teal darken-4',
							 'orange darken-1', 'orange darken-3', 'orange darken-4',
							 'deep-orange darken-1', 'deep-orange darken-3', 'deep-orange darken-4',
							 'brown darken-1', 'brown darken-3',
							 'grey darken-2', 'grey darken-3',
							 'blue-grey darken-1', 'blue-grey darken-2', 'blue-grey darken-3', 'blue-grey darken-4'];

		 $config['theme'] = $config['colors'][array_rand($config['colors'])];

		 $theme_splitted = split(" ", $config['theme']);
		 $config['theme_text'] = $theme_splitted[0] . "-text text-" . $theme_splitted[1];
	} else {
		 $config['theme'] = "grey darken-2";
		 $config['theme_text'] = "grey-text text-darken-2";
	}

?>