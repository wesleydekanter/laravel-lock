<?php

namespace WesleyDeKanter\LaravelLock;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\Lock;
use WesleyDeKanter\LaravelLock\Driver\FileLock;
use WesleyDeKanter\LaravelLock\Driver\MysqlLock;
use InvalidArgumentException;

class LockManager implements ManagerContract
{
    /**
     * @var Lock[]
     */
    private $locks = [];

    /**
     * Attempt to acquire a lock.
     *
     * @param string $name
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function get(string $name) : bool
    {
        if ($this->acquired($name)) {
            return false;
        }

        $lock = $this->create($name);
        $acquired = $lock->get();

        if ($acquired) {
            $this->locks[$name] = $lock;
        }

        return $acquired;
    }

    /**
     * Attempt to acquire a lock for a number of seconds.
     *
     * @param string $name
     * @param int $seconds
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function block(string $name, int $seconds) : bool
    {
        if ($this->acquired($name)) {
            return false;
        }

        $lock = $this->create($name);
        $acquired = false;

        try {
            $lock->block($seconds);
            $this->locks[$name] = $lock;
            $acquired = true;
        } catch (LockTimeoutException $exception) {
            //
        }

        return $acquired;
    }

    /**
     * Returns wether the lock is acquired.
     *
     * @param string $name
     * @return bool
     */
    public function acquired(string $name) : bool
    {
        return isset($this->locks[$name]);
    }

    /**
     * Attempt to release the lock.
     *
     * @param string $name
     * @return bool
     */
    public function release(string $name) : bool
    {
        $released = false;

        if ($this->acquired($name)) {
            $released = $this->locks[$name]->release();
        }

        if ($released) {
            unset($this->locks[$name]);
        }

        return $released;
    }

    /**
     * Attempts to acquire a lock to execute the callable.
     *
     * @param string $name
     * @param int $seconds
     * @param callable $callable
     *
     * @return bool|mixed
     * @throws InvalidArgumentException
     */
    public function atomic(string $name, int $seconds, callable $callable)
    {
        if ($this->block($name, $seconds)) {
            return tap($callable(), function() use ($name) {
               $this->release($name);
            });
        }

        return false;
    }

    /**
     * Release all locks when the manager is destructed.
     */
    public function __destruct()
    {
        foreach ($this->locks as $lock) {
            $lock->release();
        }
    }

    /**
     * Creates a new lock instance.
     *
     * @param string $name
     * @param null|string $driver
     *
     * @return Lock
     * @throws InvalidArgumentException
     */
    public function create(string $name, $driver = null)
    {
        $driver = $driver ?? $this->getDefaultDriver();

        switch ($driver)
        {
            case 'mysql':
                return $this->createMysqlLock($name);
            case 'file':
                return $this->createFileLock($name);
            case 'cache':
                return $this->createCacheLock($name);
            default:
                throw new InvalidArgumentException("Driver [{$driver}] is not supported");
        }

    }

    /**
     * Returns the default driver.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return config('lock.driver');
    }

    /**
     * Creates a cache lock.
     *
     * @param string $name
     * @return Lock
     */
    protected function createCacheLock(string $name)
    {
        return Cache::lock($name);
    }

    /**
     * Creates a MySQL lock.
     *
     * @param string $name
     *
     * @return MysqlLock
     */
    protected function createMysqlLock(string $name)
    {
        return new MysqlLock($name);
    }

    /**
     * Creates a file lock.
     *
     * @param string $name
     *
     * @return FileLock
     */
    protected function createFileLock(string $name)
    {
        return new FileLock($name);
    }
}