# medea
The MEDEA project is a project for find experts, researchers and detectorists to collaborate on historical findings.

## Development documentation

### Usefull commands while developing

During development, data will get injected that will have to be deleted at a certain point in time due to testing, data injection, dummy data etc.

Deleting data in Neo4j isn't as trivial as in a RDBMS systems, rather you have nodes with relationships. First you'll have to delete incoming and outgoing relationships before one can delete a node. So in order to delete nodes with label E10 for example, delete the relationships with the end node:

    MATCH (n:E10)-[r*]-(e)
    FOREACH (rel IN r| DELETE rel)
    DELETE e

Then delete the nodes with label E10:

    MATCH n
    WHERE n:E10
    delete n