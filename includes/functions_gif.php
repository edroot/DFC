<?php

function check_gif_files($files)
{
	global $config;

	$files_qty = count($files);
	foreach($files as $file_idx => $filename)
	{
		echo (($file_idx + 1)." / ". $files_qty ." ".$filename."\n");

		$file_contents_string = file_get_contents($filename);

		check_hash($file_contents_string, $filename);
		check_php_presence($file_contents_string, "gif", $filename);
		check_eval_presence($file_contents_string, "gif", $filename);
	}
	return 1;
}

?>
