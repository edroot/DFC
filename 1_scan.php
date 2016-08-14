<?php

include_once("functions.php");

if (isset($argv[1]))
	$scan_target = $argv[1];
else
{
	echo ("Please give file or directory to scan\n");
	exit;
}

switch(filetype($scan_target))
{
	default: break;

	case "file":
		$files = array($scan_target);
	break;

	case "dir":
		echo ("building files list...\n");
		$files = get_files_list($scan_target, array());
	break;
}

if (isset($argv[2]))
	$check_filetype = $argv[2];
else
	$check_filetype = "";

if (isset($argv[3]))
	$check_pattern = $argv[3];
else
	$check_pattern = "";

if (isset($check_pattern) && $config['debug'])
	echo ("check pattern: ".$check_pattern."\n");

global $hashes;
$hashes = array();

$files_php = array();
$files_js = array();
$files_other = array();
foreach($files as $file_idx => $filename)
{
	$memory_limit = round(0.9 * get_memory_limit());
	$filesize = filesize($filename);
	if ($filesize > $memory_limit || $filesize > $config['big_file_size'])
		write_detection ("files_big.txt", $filename."\n");
	else if (0 == $filesize)
		write_detection ("files_empty.txt", $filename."\n");
	else
	{
		$pinfo = pathinfo($filename);
		if (isset($pinfo['extension']))
			switch ($pinfo['extension'])
			{
				default: 
					$files_other[] = $filename;
				break;

				case "php":
					$files_php[] = $filename;
				break;

				case "js":
					$files_js[] = $filename;
				break;
			}

		else
			$files_other[] = $filename;
	}
}

if ("" == $check_filetype || "php" == $check_filetype)
{
	echo ("scanning PHP files\n");

	$patterns_files = scandir($config['patterns_dir']);
	$patterns = array();
	foreach ($patterns_files as $pattern_file)
		if ("." != $pattern_file && ".." != $pattern_file)
		{
			$pattern_array = parse_ini_file($config['patterns_dir']."/".$pattern_file);
			if (("" == $check_pattern) || ($check_pattern == $pattern_array['name']))
				$patterns[] = $pattern_array;
		}

	$exceptions_files = scandir($config['exceptions_dir']);
	$exceptions = array();
	foreach ($exceptions_files as $exception_file)
		if ("." != $exception_file && ".." != $exception_file)
		{
			$exception_array = parse_ini_file($config['exceptions_dir']."/".$exception_file);
			if (("" == $check_pattern) || ($check_pattern == $exception_array['category']))
				$exceptions[$exception_array['category']][] = $exception_array;
		}

	$files_qty = count($files_php);
	foreach($files_php as $file_idx => $filename)
	{
		echo (($file_idx + 1)." / ". $files_qty ." ".$filename."\n");
		check_php_file($filename, $patterns, $exceptions);
	}
}

if ("" == $check_filetype || "js" == $check_filetype)
{
	echo ("scanning JS files\n");

	$files_qty = count($files_js);
	foreach($files_js as $file_idx => $filename)
	{
		echo (($file_idx + 1)." / ". $files_qty ." ".$filename."\n");

		if (isset($check_pattern))
		{
			switch($check_pattern)
			{
				default:
					check_js_file($filename);
				break;

				case "remove_last_line":
					remove_last_line($filename);
				break;
			}
		}
		else
			check_js_file($filename);
	}
}

if ("" == $check_filetype || "other" == $check_filetype)
{
	echo ("scanning other files\n");

	$files_qty = count($files_other);
	foreach($files_other as $file_idx => $filename)
	{
		echo (($file_idx + 1)." / ". $files_qty ." ".$filename."\n");

		$file_contents_string = file_get_contents($filename);
		$hash = md5(trim($file_contents_string));
		$hashes[$hash][] = $filename;

		if (false !== strpos($file_contents_string, "php"))
			write_detection ("php_in_otherfiles.txt", $filename);
	}
}


foreach($hashes as $hash => $files)
{
	if (count($files) > 1)
	{
		foreach ($files as $file)
		{
			if ($config['debug'])
				echo ("duplicate ".$file."\n");
			write_detection("files_duplicate.txt", $file);
		}

		write_detection("files_duplicate.txt", "\n");
	}
}

?>
