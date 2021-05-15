<?php

namespace App\Helpers;

/**
 * The FixCsvStreamFilter wil fix a csv containing an escaped quote -> \"
 *
 * Spreadsheet: a field with \"escaped quotes\" will break the whole line
 * Spreadsheet (exported as csv): a field with \""escaped quotes\"" will break the whole line
 * After FixCsvStreamFilter: a field with ""escaped quotes"" will break the whole line
 * After csv Parsing: a field with "escaped quotes" will break the whole line
 */
class FixCsvStreamFilter extends \php_user_filter
{
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = str_replace('\""', '""', $bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
