<?php

namespace App\Transformers;

abstract class Transformer
{
    /**
     * @param  array $objects
     * @return array
     */
    abstract public function transform(array $objects): array;
}