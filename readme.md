## Laravel lock extension

This package provides additional locking features on top of the default cache locking mechanism. It also provided alternate drivers to use in stead of the default cache lock.

### Cache lock
By default this package uses the default Cache lock functionality as provided in Laravel.

### File lock
This package offers locking through the filesystem using flock. By default it places lock files in the storage/app/lock folder, though this can be changed in the config file.

### MySQL lock
This package also offers locks using MySQL's GET_LOCK() implementation. By default it uses the default connection, but the connection can be changed in the config file.

**NOTICE:** MySQL <5.7 does not allow holding multiple locks on one connection! To circumvent this, the driver sets up a new connection with the same credentials for each new lock.