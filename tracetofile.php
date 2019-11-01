<?php
/**
 * tracetofile.php
 *
 * php debug harness to help trace problems
 * @author Jeff Hendrickson JKH <jeff@hendricom.com>
 * @version 1.0
 * @package tracetofile
 */

// tracetofile namespace 
namespace tracetofile {
	// let the developer decide to what directory the log goes 
	define("TTFTMP", "/tmp/");

	/**
	 * trace output to log.txt
	 *
	 * @return nothing
	 */
	function trace($file=NULL, $line=NULL, $message) {
		$filename = '';
		$linenumber = '';
		if($file!=NULL) {
			$filename = pathinfo($file, PATHINFO_FILENAME) .'.'. pathinfo($file, PATHINFO_EXTENSION);
		}
		if($line!=NULL) {
			$linenumber = 'ln[' . $line . ']';
		}
		file_put_contents(TTFTMP . "log.txt", "$message $filename $linenumber\n", FILE_APPEND);
	}

	/**
	 * trace object(s) passed
	 *
	 * @return nothing
	 */
	function traceobject() {
		$message = "";
		$things = func_get_args();
		foreach ($things as $thing) {
			switch (gettype($thing)) {
				case 'object':
				case 'array':
					$message .= print_r($thing, TRUE);
					break;
				case 'resource':
					$message .= "resource\n";
					break;
				default:
					$message .= $thing . "\n";
			}
		}
		file_put_contents(TTFTMP . "log.txt", "$message\n", FILE_APPEND);
	}

	/**
	 * provide formatted backtrace function
	 *
	 * @return nothing
	 */
	function traceback($message = '') {
		static $count;
		$count++;
		$time = round(microtime(TRUE), 3);
		$request_string = '[' . getmypid() . " - $count - $time] ";
		$request_string .= php_sapi_name() != 'cli' ? "{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}" : '';

		$backtrace = debug_backtrace();
		$first = array_shift($backtrace);
		$file_info = isset($first['file']) ? '[' . basename($first['file']) . ' ' . $first['line'] . '] ' : '';
		$temp_backtrace[] = $file_info . (isset($first['class']) ? $first['class'] . $first['type'] : '') . $first['function'] . '()';
		foreach ($backtrace as $trace) {
			$file_info = isset($trace['file']) ? '[' . basename($trace['file']) . ' ' . $trace['line'] . '] ' : '';
			$temp_backtrace[] = $file_info . (isset($trace['class']) ? $trace['class'] . $trace['type'] : '') . $trace['function'] . '()';
		}
		array_shift($temp_backtrace);
		$filebuffer = implode("\n  -->  ", array_reverse($temp_backtrace)) . "\n";

		file_put_contents(TTFTMP . "log.txt", "$request_string: $message\n$filebuffer\n", FILE_APPEND);
	}
}
?>
