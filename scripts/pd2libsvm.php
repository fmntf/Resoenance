<?php

if ($_SERVER['argc'] != 3) {
	throw new Exception("Usage: php pd2libsvm.php <pd file> <libsvm file>");
}

$input = file_get_contents($_SERVER['argv'][1]);
$lines = explode("\n", trim($input));

$out = '';

foreach ($lines as $line) {
	$numbers = explode(" ", trim($line));
	$newLine = array();
	foreach ($numbers as $i => $number) {
		if ($i == 0) {
			$newLine[] = $number;
		} else {
			$newLine[] = $i . ":" . $number;
		}
	}
	
	$out .= implode(' ', $newLine) . PHP_EOL;
}

file_put_contents($_SERVER['argv'][2], $out);

