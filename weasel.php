<?php
$opts = getopt('p:h:s:');

if (isset($opts['h']) || empty($opts)) {
	usage();
}
if (empty($opts['p'])) {
	echo "ERROR: - A path to your files is required" . PHP_EOL;
	usage();
}
if (!is_dir($opts['p'])) {
	echo "ERROR: - Please pass in a real directory, unlike this mysterious '$opts[p]'" . PHP_EOL;
	usage();
}
if (isset($opts['s'])) {
	$save = true;
} else {
	$save = false;
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
	if (!$save) {
		echo '--------------------------------------------------' . PHP_EOL;
		echo 'File path: ' . $filepath . PHP_EOL . PHP_EOL;
		printf("%-8s|%-20s|%s" . PHP_EOL, "Line #", "Weasel", "Full Line");
		echo '--------------------------------------------------' . PHP_EOL;
	}
	
	foreach ($lines as $line_number => $line) {
		++$line_number;
		
		preg_match_all("/($weasels)/i", $line, $matches, PREG_OFFSET_CAPTURE);
		
		if (!empty($matches[1])) {
			
			$weasel = trim($matches[1][0][0]);
			$line   = trim($line);
			
			if (!$save) {
				printf("%-8d|%-20s|%s" . PHP_EOL, $line_number, $weasel, $line);
			} else {
				$saved[$filepath][] = array('line_number' => $line_number, 'weasel' => $weasel, 'line' => $line);
			}
			++$count;
		}
	}
}

if ($save) {
	echo '<?php', PHP_EOL, var_export($saved), PHP_EOL, '?>';
}

echo PHP_EOL . "Found $count weasels." . PHP_EOL;

function usage() {
	echo "USAGE:" . PHP_EOL;
	echo "Required: '-p /path/to/check' is a path to files needing checked. Recursive." . PHP_EOL;
	echo "Optional: '-s 1' will var_export the results as a PHP array [default: 0]" . PHP_EOL;
	echo "Ex: $ php {$_SERVER['SCRIPT_FILENAME']} -p /path/to/check" . PHP_EOL;
	echo "Ex: $ php {$_SERVER['SCRIPT_FILENAME']} -p /path/to/check -s 1 > weasels_array.php" . PHP_EOL;	
	exit;
}
