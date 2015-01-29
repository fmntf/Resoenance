<?php

foreach (new DirectoryIterator($_SERVER['argv'][1]) as $fileInfo) {
    if ($fileInfo->isFile() && !$fileInfo->isDot()) {
		$file = $_SERVER['argv'][1] . $fileInfo->getFilename();
		
		$features = array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0,
			8 => 0,
			9 => 0,
			10 => 0,
			11 => 0,
		);
		
		$rows = explode(PHP_EOL, file_get_contents($file));
		foreach ($rows as $row) {
			$index = trim(substr($row, 0, 2));
			if ($index != '') {
				$features[$index]++;
			}
		}
		
		for ($i=1; $i<=11; $i++) {
			if ($features[$i] != 10) {
				echo "Feature $i in $file has $features[$i] records, 10 expected!" . PHP_EOL;
			}
		}
		
	}
}

