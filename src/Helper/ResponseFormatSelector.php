<?php

namespace App\Helper;

class ResponseFormatSelector
{
    /**
     * @var string[]
     */
    public static array $supportedFormats = [
        'application/json',
        'application/xml',
        'application/yaml'
    ];

    /**
     * Selects a supported format from the preferred formats:
     * - if one of them are supported, it returns it
     * - if none of them are supported, it returns null
     * 
     * @param string[] $preferredFormats
     * A list of preferred mime types
     * 
     * @return ?string
     * Selected supported mime type
     */
    public static function select(array $preferredFormats): ?string
    {
        foreach ($preferredFormats as $preferredFormat) {
            if (in_array($preferredFormat, self::$supportedFormats)) {
                return $preferredFormat;
            }
        }

        return null;
    }
}
