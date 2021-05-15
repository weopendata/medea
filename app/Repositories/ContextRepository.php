<?php


namespace App\Repositories;


use App\Models\Context;
use Everyman\Neo4j\Exception;

class ContextRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('S22', Context::class);
    }
}
