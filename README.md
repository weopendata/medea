# MEDEA
The goal of the MEDEA projects is to bring together find experts, researchers and detectorists and let them collaborate on historical finds.

## Requirements

* Database: Neo4j 2.2.x
* PHP7.2
* MariaDB 10.10 (or equivalent)

To install the application, please follow the installations steps [here](https://laravel.com/docs/6.x).
To build the front-end, run:

```
npm install
```

### Full text support

To enable full text search, we'll need to enable the legacy indexes. This means letting neo4j know that we're going to use Lucene for indexing, and which fields should be auto-indexed to "analysed" and not "exact".

[Credit where credit is due](http://jexp.de/blog/2014/03/full-text-indexing-fts-in-neo4j-2-0/)

1. Create a POST request, as mentioned in the [link](http://jexp.de/blog/2014/03/full-text-indexing-fts-in-neo4j-2-0/) Don't forget to add the extra lower case analyzer.
```
curl -XPOST http://neo4j:{password}@localhost:7474/db/data/index/node/ --header "Content-Type: application/json" -d '{
  "name" : "node_auto_index",
  "config" : {
    "type" : "fulltext",
    "provider" : "lucene",
    "to_lower_case" : "true"
  }
}'
```
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

Finally, run this query in neo4j to start indexing:

    MATCH (n)
    WHERE has(n.fulltext_description)
    SET n.fulltext_description=n.fulltext_description

## Development documentation

### Export/Import data

https://github.com/jexp/neo4j-shell-tools

### Migration

For objects that are not involved in the graph vocabulary we use a MySQL database.
In order to perform the migration an extra option is necessary because the default database connection is Neo4J.

The command that will perform the correct MySQL migration is:

    php artisan migrate --database='mysql'

### Usefull commands while developing

    php artisan medea:management

This command will list a number of actions you can take in order to manage your database (e.g. remove all finds, users, ...)


Get logging csv from Piwik:
```
SELECT server_time,
piwik_log_visit.user_id as user,
IFNULL(cat.name, 'Pageview') as category, IFNULL(act3.name, '') as 'action',
act2.name as 'value',
url.name as 'url'
INTO OUTFILE '/tmp/result.csv'
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
ESCAPED BY '\\'
LINES TERMINATED BY '\n'
FROM `piwik_log_link_visit_action`
LEFT JOIN piwik_log_visit ON piwik_log_link_visit_action.idvisit = piwik_log_visit.idvisit
LEFT JOIN piwik_log_action as url ON piwik_log_link_visit_action.idaction_url = url.idaction
LEFT JOIN piwik_log_action as act2 ON piwik_log_link_visit_action.idaction_name = act2.idaction
LEFT JOIN piwik_log_action as act3 ON piwik_log_link_visit_action.idaction_event_action = act3.idaction
LEFT JOIN piwik_log_action as cat ON piwik_log_link_visit_action.idaction_event_category = cat.idaction
ORDER BY `piwik_log_link_visit_action`.`server_time`  ASC;
```

    scp host:/tmp/result.csv .

## Trivia

### Open files
When booting the Neo4j, you might get a warning telling you that the amount of open files is limited and should be booted to a safe number, say 40000.
Following the documentation of Neo4j might not work, but adding the number of open files to the system config files for the sudo or neo4j user.

If this is the case, add "ulimit -n 40000" to in the do_start() function of the neo4j-service, this should boot the application with the preferred amount of open files.
