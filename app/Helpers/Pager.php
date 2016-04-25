<?php

namespace App\Helpers;

class Pager
{
    public static function calculatePagingInfo($limit, $offset, $total_rows)
    {
        $paging = [];

        // Check if limit and offset are integers
        if (!is_integer((int)$limit) || !is_integer((int)$offset)) {
            \App::abort(400, "Please make sure limit and offset are integers.");
        }

        // Calculate the paging parameters and pass them with the data object
        if ($offset + $limit < $total_rows) {
            $paging['next'] = [$limit + $offset, $limit];
            $last_page = round($total_rows / $limit, 1);
            $last_full_page = round($total_rows / $limit, 0);

            if ($last_page - $last_full_page > 0) {
                $paging['last'] = [($last_full_page) * $limit, $limit];
            } else {
                $paging['last'] = [($last_full_page - 1) * $limit, $limit];
            }
        }
        if ($offset > 0 && $total_rows > 0) {
            $previous = $offset - $limit;
            if ($previous < 0) {
                $previous = 0;
            }
            $paging['previous'] = [$previous, $limit];
        }
        return $paging;
    }
}
