<?php

namespace WesleyDeKanter\LaravelLock\Driver;

use \Illuminate\Cache\Lock;
use Illuminate\Contracts\Cache\Lock as LockContract;

class FileLock extends Lock implements LockContract
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var resource
     */
    private $file;

    /**
     * FileLock constructor.
     * @param string $name
     * @param string $path
     */
    public function __construct(string $name, ?string $path = null)
    {
        parent::__construct($name, 0);

        $this->setPath($path ?? config('lock.filesystem.folder'));
    }

    /**
     * Set the folder path and checks existance.
     *
     * @param string $path
     */
    private function setPath(string $path)
    {
        $directory = rtrim($path, DIRECTORY_SEPARATOR);
        $file = "{$this->name}.lock";

        // Attemp to create the folder if it does not exist.
        if ( ! is_dir($directory)) {
            mkdir($directory, 0770, true);
        }

        $this->path = $directory . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function acquire()
    {
        $this->file = fopen($this->path, 'c');

        if (is_resource($this->file) && flock($this->file, LOCK_EX | LOCK_NB)) {
            $this->writeLockContent();
            return true;
        }

        return false;
    }

    /**
     * Writes the PID to the lock file.
     */
    private function writeLockContent()
    {
        ftruncate($this->file, 0);
        fwrite($this->file, getmypid());
    }

    /**
     * Attempt to release the lock.
     *
     * @return bool
     */
    public function release()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
            @unlink($this->path);
            $this->file = null;
            return true;
        }

        return false;
    }
}