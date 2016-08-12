# MEDEA
The goal of the MEDEA projects is to bring together find experts, researchers and detectorists and let them collaborate on historical finds.

## Requirements

* Database: Neo4j 2.2.x
* PHP 5.6+
* MariaDB 10.10 (or equivalent)

## Development documentation

### Migration

For objects that are not involved in the graph vocabulary we use a MySQL database.
In order to perform the migration an extra option is necessary because the default database connection is Neo4J.

The command that will perform the correct MySQL migration is:

    php artisan migrate --database='mysql'

### Usefull commands while developing

    php artisan medea:management

This command will list a number of actions you can take in order to manage your database (e.g. remove all finds, users, ...)