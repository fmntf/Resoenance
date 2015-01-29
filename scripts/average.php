<?php

$average = null;

$rows = explode(PHP_EOL, file_get_contents($_SERVER['argv'][1]));
foreach ($rows as $row) {
	$index = trim(substr($row, 0, 2));
	if ($index == $_SERVER['argv'][2]) {
		$numbers = explode(' ', $row);
		
		if ($average === null) {
			$average = $numbers;
		} else {
			for ($i=1; $i<count($numbers); $i++) {
				$average[$i] += $numbers[$i];
			}
		}
	}
}

for ($i=1; $i<count($numbers); $i++) {
	$average[$i] /= 10;
}

echo implode(' ', $average) . PHP_EOL;