<?php

namespace App\Models;

class FindEvent extends Base
{
    protected static $fillable = ['date'];

    public static $NODE_TYPE = 'E10';
    public static $NODE_NAME = 'findEvent';

    public function __construct($node)
    {
        $this->node = $node;
    }

    public static function create($properties = [])
    {
        static::$fillable = self::$fillable;

        $node = parent::createNode($properties);

        return new FindEvent($node);
    }
}
