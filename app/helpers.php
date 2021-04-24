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

/**
 * Equivalent of dd(), but returns json
 *
 * @param $data
 */
function jj($data)
{
    header('Content-Type: application/json');

    if (method_exists($data, 'toArray')) {
        $data = $data->toArray();
    }

    echo json_encode($data, JSON_PRETTY_PRINT);

    die(1);
}

/**
 * @param Exception $ex
 */
function medea_log_error (\Exception $ex)
{
    \Log::error($ex->getMessage());
    \Log::error($ex->getTraceAsString());
}
