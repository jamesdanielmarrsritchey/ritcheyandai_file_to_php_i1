<?php
$location = realpath(dirname(__FILE__));
require_once $location . '/function.php';
$inputFile = "{$location}/temporary/input.txt";
$outputFile = "{$location}/temporary/output.txt.php";
$return = createFileFromBase64ChunksWithCRC32($inputFile, $outputFile);
var_dump($return);