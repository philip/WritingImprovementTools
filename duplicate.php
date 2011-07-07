<?php
$opts = getopt('p:h:e:');

if (isset($opts['h'])) {
	usage();
}
if (empty($opts['p'])) {
	echo "\nERROR:\n - A path to your files is required\n";
	usage();
}
if (!is_dir($opts['p'])) {
	echo "\nERROR:\n - Please pass in a real directory, unlike this mysterious '$opts[p]'\n";
	usage();
}

$exts = array('xml','txt');
if (!empty($opts['e'])) {
	$exts = explode(',', $opts['e']);
}

$count = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($opts['p'])) as $file) {

	$filepath = $file->getPathname();

	// $file->getExtension() exists as of PHP 5.3.6
	if (!$file->isFile() || !in_array(pathinfo($filepath, PATHINFO_EXTENSION), $exts)) {
		continue;
	}
	
	$contents = file_get_contents($filepath);
	
	preg_match_all("/\b([\w'-]+)(\s+\\1)+/i", $contents, $matches, PREG_OFFSET_CAPTURE);
	
	if (empty($matches[1])) {
		continue;
	}
	
	if (is_array($matches[0]) && count($matches[0]) > 0) {
		
		echo $filepath . PHP_EOL;
		foreach ($matches[0] as $match) {
			echo "Match:    {$match[0]}" . PHP_EOL;
			echo "Position: {$match[1]}" . PHP_EOL;
		}
	}
}

function usage() {
	echo "\nUSAGE:\n";
	echo "$ php {$_SERVER['SCRIPT_FILENAME']} -p /path/to/check" . PHP_EOL;
	exit;
}
