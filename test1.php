<?php
/**
 * test.php
 *
 * test php debug harness 
 * @author Jeff Hendrickson JKH <jeff@hendricom.com>
 * @version 1.0
 * @package tracetofile
 */
require_once "tracetofile.php";
// define namespace
use tracetofile as t;
$runCount = 100;
while(--$runCount > 0) {
	// wait for 5ms seconds 1000000 = 1 second
	usleep(5000);
	// test array
	$test = array("Jeff", "Hendrickson");
	// test trace to file ...
	// e.g. namespace\trace(string or filename,number,string message);
	// will create entry in log.txt ...
	// this is a test... test.php ln[19]
	t\trace("this is a test...");

	// test trace object
	// e.g. namespace\traceobjects($object);
	// will create an object list ...
	// Array
	// (
	//    [0] => Jeff
	//    [1] => Hendrickson
	// )
	t\traceobject($test);

	// test trace back (in function below)
	// e.g. traceback("some cool message");
	// will create a traceback list ...
	//  PID    CNT Microtime         MESSAGE
	// [4762 - 1 - 1572614665.413] : saying goodbye
	// [test.php 40] hello()
	//   -->  [test.php 44] goodbye()

	// call test functions...
	hello("Jeff [" . $runCount . "]");
}

function hello($you) {
	printf("hello %s!\n",$you);
	goodbye($you);
}

function goodbye($you) {
	printf("goodbye %s!\n",$you);
	// test trace back 
	t\traceback("saying goodbye");
}
?>
