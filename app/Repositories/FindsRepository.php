<?php

namespace App\Repositories;

use App\Models\FindEvent;

class FindsRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(FindEvent::$NODE_TYPE, FindEvent::class);
    }

    public function store($properties)
    {
        $find = FindEvent::create($properties);

        $find->save();

        return $find;
    }
}
