<?php

namespace WesleyDeKanter\LaravelLock\Driver;

use Illuminate\Contracts\Cache\Lock as LockContract;
use Illuminate\Cache\Lock;
use Illuminate\Database\MySqlConnection;
use InvalidArgumentException;

class MysqlLock extends Lock implements LockContract
{
    /**
     * @var null|string
     */
    private $connectionName;

    /**
     * @var null|MySqlConnection
     */
    private $connection;

    /**
     * Indicates wether the connection should be closed after releasing the lock.
     *
     * @var bool
     */
    private $closeAfterRelease = false;

    /**
     * MysqlLock constructor.
     * @param string $name
     * @param string|null $connection
     */
    public function __construct(string $name, string $connection = null)
    {
        parent::__construct($name, 0);

        $this->connectionName = $connection ?? config('lock.mysql.connection');
    }

    /**
     * @return MySqlConnection
     * @throws InvalidArgumentException
     */
    private function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = \DB::connection($this->connectionName);

            if ( ! ($this->connection instanceof MySqlConnection)) {
                $driver = $this->connection->getDriverName();
                throw new InvalidArgumentException("The connection is a [{$driver}] connection, not a [mysql] connection");
            }

            /**
             * MySQL versions lower than 5.7 do not support holding multiple locks on a single connection. Every
             * new lock releases all prior locks. This could pose a problem as a lock may be released earlier than
             * expected. To circumvent this we create a new connection for each lock.
             */
            if (version_compare(self::getVersion($this->connection), '5.7','<')) {
                $this->connection = $this->getDuplicateConnection($this->connection);
                $this->closeAfterRelease = true;
            }
        }

        return $this->connection;
    }

    /**
     * Returns a new connection with the same credentials.
     *
     * @param MySqlConnection $connection
     * @return MySqlConnection
     */
    private function getDuplicateConnection(MySqlConnection $connection)
    {
        $connection = clone $connection;
        $connection->reconnect();
        return $connection;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function acquire()
    {
        $row = head($this->getConnection()->select("SELECT GET_LOCK(?, 0) AS acquired;", [
            $this->name
        ]));

        return $row->acquired == 1;
    }

    /**
     * Attempt to release the lock.
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function release()
    {
        $row = head($this->getConnection()->select("SELECT RELEASE_LOCK(?) AS released;", [
            $this->name
        ]));

        $released = $row->released == 1;

        if ($released && $this->closeAfterRelease) {
            $this->connection->disconnect();
        }

        return $released;
    }

    /**
     * Contains known version numbers for connections.
     *
     * @var array
     */
    private static $connectionVersions = [];

    /**
     * Retrieves the MySQL version by querying the database.
     *
     * @param MySqlConnection $connection
     * @return int
     */
    private static function getVersion(MySqlConnection $connection) {
        $name = $connection->getName();

        if ( ! isset(self::$connectionVersions[$name])) {
            self::$connectionVersions[$name] = head($connection->select("SHOW VARIABLES LIKE 'version';"))->Value;
        }

        return self::$connectionVersions[$name];
    }
}