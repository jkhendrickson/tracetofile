<?php
/**
 * mutex.php
 *
 * php mutex for synchronization of threads
 * @author Jeff Hendrickson JKH <jeff@hendricom.com>
 * @version 1.0
 * @package mutex
 */
 
class MutexException extends Exception
{
    // redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

	// report the error
    public function getMutexError() {
        echo "Mutex unable to lock file.";
    }
} 
 
class Mutex {

    var $writeablePath = '';
    var $lockName = '';
    var $fileHandle = null;

    public function __construct($lockName, $writeablePath = null){
        $this->lockName = preg_replace('/[^a-z0-9\-]/', '', $lockName);
        if($writeablePath == null){
            $this->writeablePath = sys_get_temp_dir();
        } else {
            $this->writeablePath = $writeablePath;
        }
    }

    public function getLock(){
        return flock($this->getFileHandle(), LOCK_EX);
    }

    public function getFileHandle(){
        if($this->fileHandle == null){
            $this->fileHandle = fopen($this->getLockFilePath(), 'c');
        }
        return $this->fileHandle;
    }

    public function releaseLock(){
        $success = flock($this->getFileHandle(), LOCK_UN);
        fclose($this->getFileHandle());
        return $success;
    }

    public function getLockFilePath(){
        return $this->writeablePath . DIRECTORY_SEPARATOR . $this->lockName;
    }

    public function isLocked(){
        $fileHandle = fopen($this->getLockFilePath(), 'c');
        $canLock = flock($fileHandle, LOCK_EX | LOCK_NB);
        if($canLock){
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            return false;
        } else {
            fclose($fileHandle);
            return true;
        }
    }
}
