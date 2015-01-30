<?php

if ($_SERVER['argc'] < 4) {
	echo 'Usage: php convert-experiments <source> <destination> <libsvm> [instances] [train]

<source>: directory path, containing txt files
<destination>: directory path, where to put libsvm files
<libsvm>: path of (compiled) libsvm
[instances]: select this number of rows for each txt file (defaults to 10)
[train]: size of the train partition (defaults to 10)
';
	die();
}

$out = realpath($_SERVER['argv'][2]);
$libsvm = realpath($_SERVER['argv'][3]);

if ($_SERVER['argc'] >= 5) {
	$maxFeatures = (int) $_SERVER['argv'][4];
} else {
	$maxFeatures = 10;
}

if ($_SERVER['argc'] >= 6) {
	$trainSize = (int) $_SERVER['argv'][5];
} else {
	$trainSize = 10;
}

$classes = 11;
$classes = 6;
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
				$destination = $out . '/' . $type . '/' . $number;
				
				if (!is_dir($destination)) {
					mkdir($destination, 0777, true);
				}
				
//				if ($maxFeatures == 10) {
					system("cat $source/*.txt > $destination/all.txt");
//				} else {
//					$op = '>';
//					foreach (new DirectoryIterator($source) as $fInfo) {
//						if ($fInfo->isFile() && !$fInfo->isDot()) {
//							$txt = $source .'/'. $fInfo->getFilename();
//							
//							for ($f=1; $f<=11; $f++) {
//								system("grep '^$f ' $txt > /tmp/grepout && head -n $maxFeatures < /tmp/grepout $op $destination/all.txt");
////								system("grep '^$f ' $txt | head -n $maxFeatures $op $destination/all.txt").PHP_EOL;
//								$op = '>>';
//							}
//						}
//					}
//				}
				
				system("php pd2libsvm.php $destination/all.txt $destination/all.svm");	
				system("cd $destination && head -n $trainPartition all.svm > train");
				system("cd $destination && tail -n +$testPartitionOffset all.svm > test");
//				system("cd $destination && split -l $trainPartition all.svm");
//				system("mv $destination/xaa $destination/train");
//				system("mv $destination/xab $destination/test");
				system("rm $destination/all.*");
				
				system("cd $destination && $libsvm/svm-scale -l 0 -u 1 -s range train > train.scale");
				system("cd $destination && $libsvm/svm-scale -r range test > test.scale");
				system("cd $destination && $libsvm/svm-train -q train.scale");
				
				echo "$type, $number" . PHP_EOL;
				passthru("cd $destination && $libsvm/svm-predict test.scale train.scale.model test.predict");
				exec("cd $destination && python $libsvm/tools/grid.py train.scale", $cmdoutput);
				$parts = explode(' ', $cmdoutput[0]);
				
				system("cd $destination && $libsvm/svm-train -c $parts[0] -g $parts[1] -q train.scale");
				passthru("cd $destination && $libsvm/svm-predict test.scale train.scale.model test.predict");
				
				echo PHP_EOL;
			}
		}
		
	}
}

