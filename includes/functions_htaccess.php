<?php

function check_htaccess_files($files)
{
	global $config;

	$files_qty = count($files);
	foreach($files as $file_idx => $filename)
	{
		echo (($file_idx + 1)." / ". $files_qty ." ".$filename."\n");

		$file_contents_string = file_get_contents($filename);

		add_hash($file_contents_string, $filename);

		$pos = stripos($file_contents_string, "AddHandler");
		if (false !== $pos)
		{
			$begin = $pos - 20;
			if ($begin < 0)
				$begin = 0;
			write_detection ("htaccess_addhandler.txt", $filename);
			write_detection ("htaccess_addhandler.txt", substr($file_contents_string, $begin, 20));
			write_detection ("htaccess_addhandler.txt", "\n");
		}
	}
	return 1;
}

?>
