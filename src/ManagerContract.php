<?php

namespace WesleyDeKanter\LaravelLock;

interface ManagerContract
{
    /**
     * Attempt to acquire a lock.
     *
     * @param string $name
     * @return bool
     */
    public function get(string $name): bool;

    /**
     * Attempt to acquire a lock for a number of seconds.
     *
     * @param string $name
     * @param int $seconds
     * @return bool
     */
    public function block(string $name, int $seconds): bool;

    /**
     * Indicates wether a lock has been acquired.
     *
     * @param string $name
     * @return bool
     */
    public function acquired(string $name): bool;

    /**
     * Attempt to release a lock.
     *
     * @param string $name
     * @return bool
     */
    public function release(string $name): bool;

    /**
     * Attempt to acquire a lock to execute a callable.
     *
     * @param string $name
     * @param int $seconds
     * @param callable $callable
     * @return mixed
     */
    public function atomic(string $name, int $seconds, callable $callable);
}