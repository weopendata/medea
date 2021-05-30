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
 * Return the behaviour the app needs to run in:
 * - public: no registration, stripped from a number of elements in order to make it a slim application to use
 * - private: the full blown app, including registration, validation of finds, etc.
 *
 * @return boolean
 */
function getMedeaApplicationType()
{
    return env('APP_PUBLIC_ONLY', false) == true;
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
