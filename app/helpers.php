<?php

use Illuminate\Support\Arr;

/**
 * Return the title of the find based
 * on its properties (or lack of)
 *
 * @param  array  $find
 * @return string
 */
function makeFindTitle($find)
{
    $title = 'ongeïdentificeerd, ';

    $material = Arr::get($find, 'object.objectMaterial');
    $category = Arr::get($find, 'object.objectCategory');
    $period = Arr::get($find, 'object.period');

    if (! empty($category) && $category != 'onbekend') {
        $title = $category . ', ';
    }

    if (! empty($period) && $period != 'onbekend') {
        $title .= $period . ', ';
    }

    if (! empty($material) && $material != 'onbekend') {
        $title .= $material . ', ';
    }

    $title = rtrim($title, ', ');
    $title .= ' ' . '(ID-' . $find['identifier'] . ')';

    return $title;
}
