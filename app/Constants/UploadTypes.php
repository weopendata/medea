<?php

namespace App\Constants;

class UploadTypes
{
    const EXCAVATION_UPLOAD = 'excavation';
    const FIND_UPLOAD = 'find';
    const CONTEXT_UPLOAD = 'context';
    const FIND_PAN_UPLOAD = 'pan';

    /**
     * @return string[]
     */
    public static function getUploadTypeValues(): array
    {
        return [
            self::EXCAVATION_UPLOAD,
            self::FIND_PAN_UPLOAD,
            self::CONTEXT_UPLOAD,
            self::FIND_PAN_UPLOAD,
        ];
    }
}