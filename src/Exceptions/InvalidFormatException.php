<?php 

namespace WallaceMaxters\Timespan\Exceptions;

use InvalidArgumentException;

class InvalidFormatException extends InvalidArgumentException 
{

    public function __construct(string $value, string $format)
    {
        $message = sprintf(
            'Invalid time string "%s" for format "%s"',
            $value, 
            $format
        );

        parent::__construct($message);
    }
}
