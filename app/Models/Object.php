<?php

namespace App\Models;

class Object extends Base
{
    protected static $fillable = ['description', 'inscription', 'material', 'technique', 'bibliography'];

    public static $NODE_TYPE = 'E22';
    public static $NODE_NAME = 'object';

    public static function create($properties = [])
    {
        static::$fillable = self::$fillable;

        $node = parent::createNode($properties);

        return new Object($node);
    }

    /**
     * Dimension is not a main entity, so we create it in this object only
     *
     * @param $dimension array An array with value, type, unit
     *
     * @return Node
     */
    public function createDimension($dimension)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make E54 Dimension
        $dimension_node = $client->makeNode();
        $dimension_node->save();

        // Set the proper labels to the objectDimensionType
        $dimension_node->addLabels([self::makeLabel('E54'), self::makeLabel('objectDimension'), self::makeLabel($general_id)]);

        // Make E55 Type objectDimensionType
        $dimension_type = $client->makeNode();
        $dimension_type->setProperty('value', $dimension['type']);
        $dimension_type->save();
        $dimension_type->addLabels([self::makeLabel('E55'), self::makeLabel($general_id)]);

        $dimension_node->relateTo($dimension_type, 'P2')->save();

        // Make E60 Number
        $dimension_value = $client->makeNode();
        $dimension_value->setProperty('value', $dimension['value']);
        $dimension_value->save();
        $dimension_value->addLabels([self::makeLabel('E60'), self::makeLabel($general_id)]);

        $dimension_node->relateTo($dimension_value, 'P90')->save();

        // Make E58 Measurement Unit
        $dimension_unit = $client->makeNode();
        $dimension_unit->setProperty('value', $dimension['unit']);
        $dimension_unit->save();
        $dimension_unit->addLabels([self::makeLabel('E58'), self::makeLabel($general_id)]);

        $dimension_node->relateTo($dimension_unit, 'P91')->save();

        // Relate the object to the dimension
        $this->node->relateTo($dimension_node, 'P43')->save();

        return $dimension_node;
    }

    /**
     * Need to override the base delete and delete our relationships with Dimension nodes
     */
    public function delete()
    {
        // Get all related nodes through the general id
        $client = $this->getClient();
        $label = $client->makeLabel($this->getGeneralId());

        $related_nodes = $label->getNodes();

        foreach ($related_nodes as $related_node) {
            // Get and delete all of the relationships
            $relationships = $related_node->getRelationships();

            foreach ($relationships as $relationship) {
                $relationship->delete();
            }

            $related_node->delete();
        }

        // Delete the main node
        parent::delete();
    }
}
