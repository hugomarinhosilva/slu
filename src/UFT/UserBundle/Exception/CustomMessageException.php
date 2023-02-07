<?php

namespace UFT\UserBundle\Exception;

/**
 * NewException
 * Extends the Exception class so that the $message parameter is now mendatory.
 *
 */
class CustomMessageException extends \Exception {
    //$message is now not optional, just for the extension.
    public function __construct($message, $code = 1, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
