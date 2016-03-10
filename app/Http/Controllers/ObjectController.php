<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\ObjectRepository;

class ObjectController extends Controller
{
    public function __construct(ObjectRepository $objects)
    {
        $this->objects = $objects;
    }
}
