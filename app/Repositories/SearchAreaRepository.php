<?php


namespace App\Repositories;


use App\Models\SearchArea;

class SearchAreaRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(SearchArea::$NODE_TYPE, SearchArea::class);
    }
}
