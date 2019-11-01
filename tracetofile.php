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
	
	require_once "mutex.php"; 
	
	// let the developer decide to what directory the log goes 
	define("TTFTMP", "/tmp/");
	// maximum number of tries for log...
	define("TTFMAXCOUNT", "10");
	// the handle for our trace mutex
	$ttf_trace_mutex = NULL;
	
	/**  
	 * try to acquire mutex
	 *
	 * @return nothing
	 */
	function acquireMutex($mutexName) {
	    global $ttf_trace_mutex;
	    $retcode = true;
	    $trycount = 0;
		$ttf_trace_mutex = new \Mutex($mutexName); 
		while($ttf_trace_mutex->isLocked()) {
			print("mutex is locked\n");
			// wait for 1ms 1000000 = 1 second
			usleep(1000);
		}
		while(!$ttf_trace_mutex->getLock()) { 
		    print("mutex get lock fails\n");
			if(++$trycount > TTFMAXCOUNT) {
				$retcode = false;
				throw new \MutexException('Failed to get lock on mutex.', 5);
			}
		}
		return $retcode;
	}
	
	function releaseMutex() {
		global $ttf_trace_mutex;
		$ttf_trace_mutex->releaseLock();
		$ttf_trace_mutex = NULL;
	}
	
	/**
	 * trace output to log.txt
	 *
	 * @return nothing
	 */
	function trace($message) {
		$bt = debug_backtrace();
		$caller = array_shift($bt);
		$longfilename = $caller['file'];
		$line = $caller['line'];
		$filename = pathinfo($longfilename, PATHINFO_FILENAME) .'.'. pathinfo($longfilename, PATHINFO_EXTENSION);
		$linenumber = 'ln[' . $line . ']';
 		try {
			if(acquireMutex("Trace Mutex")) {
				file_put_contents(TTFTMP . "log.txt", "$message $filename $linenumber\n", FILE_APPEND);
				releaseMutex();
			}
		} catch (\MutexException $e) {
			// what to do!?!
			echo "Warning! " . $e->getMutexError() . "\n";
		}
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
		
 		try {
			if(acquireMutex("Trace Mutex")) {
				file_put_contents(TTFTMP . "log.txt", "$message\n", FILE_APPEND);
				releaseMutex();
			}
		} catch (\MutexException $e) {
			// what to do!?!
			echo "Warning! " . $e->getMutexError() . "\n";
		}		
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
		
 		try {
			if(acquireMutex("Trace Mutex")) {
				file_put_contents(TTFTMP . "log.txt", "$request_string: $message\n$filebuffer\n", FILE_APPEND);
				releaseMutex();
			}
		} catch (\MutexException $e) {
			// what to do!?!
			echo "Warning! " . $e->getMutexError() . "\n";
		}		
	}
}
?>
