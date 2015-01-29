<?php

if ($_SERVER['argc'] < 2) {
	echo 'Usage: php ml-lib.test.php <source> [train]

<source>: directory path, containing txt files
[train]: size of the train partition (defaults to 10)
';
	die();
}

if ($_SERVER['argc'] >= 3) {
	$trainSize = (int) $_SERVER['argv'][2];
} else {
	$trainSize = 10;
}

$classes = 11;
$maxFeatures = 10;
$trainPartition = $trainSize * $maxFeatures * $classes;
$testPartitionOffset = $trainPartition + 1;

foreach (new DirectoryIterator($_SERVER['argv'][1]) as $fileInfo) {
    if ($fileInfo->isDir() && !$fileInfo->isDot()) {
		$type = $fileInfo->getFilename();
		
		$subdir = realpath($_SERVER['argv'][1]) . '/' . $type;
		
		foreach (new DirectoryIterator($subdir) as $fileInfo) {
			if ($fileInfo->isDir() && !$fileInfo->isDot()) {
				$number = $fileInfo->getFilename();
				
				$source = $subdir . '/' . $number;
				
				system("cat $source/*.txt > /tmp/mllib.all.txt");
				
				system("cd /tmp && head -n $trainPartition mllib.all.txt > training.txt");
				system("cd /tmp && tail -n +$testPartitionOffset mllib.all.txt > mapping.txt");
				
				$test = array();
				$verification = array();
				$testVectors = explode(PHP_EOL, file_get_contents('/tmp/mapping.txt'));
				
				foreach ($testVectors as $testVector) {
					$index = trim(substr($testVector, 0, 2));
					if ($index != '') {
						$row = trim(substr($testVector, 2));
						$test[] = $row;
						$verification[] = $index;
					}
				}
				
				file_put_contents('/tmp/mapping.txt', implode(PHP_EOL, $test));
				system("rm /tmp/mllib.all.txt");

				$predictions = callPureData();
				
				if (count($predictions) != count($verification)) {
					echo "Warning: " . count($predictions)." != ".count($verification). PHP_EOL;
				}
				
				$hits = 0;
				for ($i=0; $i<count($verification); $i++) {
					if ($verification[$i] == $predictions[$i]) {
						$hits++;
					}
				}
				
				echo "$type, $number" . PHP_EOL;
				printf("%.2f%% (%d/%d)", $hits/count($verification) * 100, $hits, count($verification));
				echo PHP_EOL . PHP_EOL;
				
			}
		}
		
	}
}



function callPureData()
{
	$command = "expect -c 'set timeout 360
				spawn sh -c {pd -lib ml -lib flatgui -lib cyclone -lib zexy -lib list-abs -lib mapping -nogui -noaudio -nomidi ml-lib.test.pd}
				expect \"PREDICTION_END\" { exit }'";

	exec($command, $output);

	$start = array_search("INFO: train 1", $output);
	$stop = array_search("PREDICTION_END: bang", $output);

	$lines = array_slice($output, $start+1, $stop-$start-1);
	
	$predictions = array();
	foreach ($lines as $line) {
		$predictions[] = $line[9];
	}

	return $predictions;
}
