<?php

global $config;
$config = array(
	'infected_dir' => "virii",
	'detections_dir' => "detections",
	'patterns_dir' => "patterns",
	'exceptions_dir' => "exceptions",
	'lines_include' => 3,
	'max_detection_strlen' => 150,
	'dangerous_strlen' => 400,
	'slash' => "/"
);


function backup_infected($s_file)
{
	global $config;
	
	$basedir = __DIR__.$config['slash'];

	$file = str_replace ($basedir, "", $s_file);
	echo ("backing up ".$file."\n");
	$path_arr = explode($config['slash'], $file);

	$filename = array_pop($path_arr);

	$path = "";
	foreach($path_arr as $k => $v)
	{
		if (0 == $k)
			$v = $config['infected_dir'];

		$path .= $v.$config['slash'];

		if (!file_exists($path))
			mkdir($path);
	}

	$s_file_new = $path.$config['slash'].$filename;
	if (!file_exists($s_file_new))
		copy ($s_file, $s_file_new);

	return 1;
}

function write_detection($filename, $info)
{
	global $config;
	$det_file_name = $config['detections_dir'].$config['slash'].$filename;

	if (!file_exists($config['detections_dir']))
		mkdir($config['detections_dir'], 0775, 1);

	file_put_contents($det_file_name, $info."\n", FILE_APPEND);

	return 1;
}

?>