<?php
$opts = getopt('p:h:');

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

// @todo spacing here feels hackish
// @bug eol weasels are lost, but \b or \s* causes 4x performance issues. research this.
$weasels  = ' many | various | very | fairly | several | extremely | exceedingly |';
$weasels .= ' quite | remarkably | few | surprisingly | mostly | largely | huge | ';
$weasels .= ' excellent | interestingly | significantly | substantially | clearly | vast | relatively | completely ';

$count = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($opts['p'])) as $file) {

	$filepath = $file->getPathname();

	// $file->getExtension() exists as of PHP 5.3.6
	if (!$file->isFile() || pathinfo($filepath, PATHINFO_EXTENSION) !== 'xml') {
		continue;
	}
	
	$lines = file($filepath, FILE_IGNORE_NEW_LINES);

	preg_match_all("/($weasels)/i", implode(PHP_EOL, $lines), $matches, PREG_OFFSET_CAPTURE);
	
	if (empty($matches[1])) {
		continue;
	}

	// @todo prettier output
	echo '--------------------------------------------------' . PHP_EOL;
	echo 'File path: ' . $filepath . PHP_EOL . PHP_EOL;
	printf("%-8s|%-20s|%s" . PHP_EOL, "Line #", "Weasel", "Full Line");
	echo '--------------------------------------------------' . PHP_EOL;
	
	foreach ($lines as $line_number => $line) {
		
		preg_match_all("/($weasels)/i", $line, $matches, PREG_OFFSET_CAPTURE);
		
		if (!empty($matches[1])) {
			printf("%-8d|%-20s|%s" . PHP_EOL, $line_number+1, trim($matches[1][0][0]), trim($line));
			++$count;
		}
		
	}
}

echo PHP_EOL . "Found $count weasels." . PHP_EOL;

function usage() {
	echo "\nUSAGE:\n";
	echo "$ php {$_SERVER['SCRIPT_FILENAME']} -p /path/to/check" . PHP_EOL;
	exit;
}
