<?php

namespace LucianoTonet\CartesiaPHP;

/**
 * Class CartesiaClientException
 * 
 * Custom exception class for handling errors related to the Cartesia client.
 * Extends the base Exception class to provide specific error messages and codes.
 */
class CartesiaClientException extends \Exception {
    // The error message
    protected $message;

    // The error code
    protected $code;

    /**
     * Constructor for CartesiaClientException.
     *
     * @param string $message The error message to be displayed.
     * @param int $code The error code (default is 0).
     * @param \Exception|null $previous The previous exception used for exception chaining (default is null).
     */
    public function __construct($message = "Cartesia client error", $code = 0, \Exception $previous = null) {
        // Set the message and code properties
        $this->message = $message;
        $this->code = $code;

        // Call the parent constructor to ensure proper exception handling
        parent::__construct($this->message, $this->code, $previous);
    }

    /**
     * String representation of the exception.
     *
     * @return string A string representation of the exception, including the class name, code, and message.
     */
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
