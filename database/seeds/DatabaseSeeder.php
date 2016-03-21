<?php

use Illuminate\Database\Seeder;
use Everyman\Neo4j\Client;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Everyman\Neo4j\Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        // Check if the admin user already exists, if not create one.
        // Use the Person label, as described by the vocabulary the MEDEA project handles

        // Get the Person label
        $label = $client->makeLabel('Person');

        // Get all of the Person node with the admin email
        $nodes = $label->getNodes("email", "foo@bar.com");

        if ($nodes->count() == 0) {
            $person_label = $client->makeLabel('Person');
            $cidoc_label = $client->makeLabel('E21');
            $person = $client->makeNode();

            // TODO Create the admin user through an env configuration
            $person->setProperty('name', 'medea-admin');
            $person->setProperty('email', 'foo@bar.com');
            $person->setProperty('verified', true);

            $password = Hash::make('admin');
            $person->setProperty('password', $password);
            $person->save();

            // Set the type
            $person->addLabels([$person_label, $cidoc_label]);

            $this->command->info("An admin user was created.");
        } else {
            $this->command->info("The admin user already exists.");
        }

        // Seed the values in the lists of MEDEA
        $this->call(ListValueSeeder::class);
    }
}
