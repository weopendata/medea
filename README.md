# MEDEA
The goal of the MEDEA projects is to bring together find experts, researchers and detectorists and let them collaborate on historical finds.

## Requirements

* Database: Neo4j 2.2.x (higher or lower versions will most likely break the application)
* PHP 5.6+
* MariaDB 10.10 (or equivalent)

### Full text support

To enable full text search, we'll need to enable the legacy indexes. This means letting neo4j know that we're going to use Lucene for indexing, and which fields should be auto-indexed to "analysed" and not "exact".

[Credit where credit is due](http://jexp.de/blog/2014/03/full-text-indexing-fts-in-neo4j-2-0/)

1. Create a POST request, as mentioned in the [link](http://jexp.de/blog/2014/03/full-text-indexing-fts-in-neo4j-2-0/)
2. Edit the conf/neo4j.properties file and add/edit the following lines:
    ```
        node_auto_indexing=true
        node_keys_indexable=fulltext_description
    ```

3. Make sure the analyzer has to_lower_case, open up the bin/shell of neo4j and run:

    ```
        index --get-config node_auto_index
    ```

This should return:

    ```
        {
            "provider": "lucene",
            "to_lower_case": "true",
            "type": "fulltext"
        }
    ```

## Development documentation

### Migration

For objects that are not involved in the graph vocabulary we use a MySQL database.
In order to perform the migration an extra option is necessary because the default database connection is Neo4J.

The command that will perform the correct MySQL migration is:

    php artisan migrate --database='mysql'

### Usefull commands while developing

    php artisan medea:management

This command will list a number of actions you can take in order to manage your database (e.g. remove all finds, users, ...)