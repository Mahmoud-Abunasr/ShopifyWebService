<?php

/**
 * Holds the common response data members
 */
class Response
{
    /**
     * Holds the operation status [true|false]
     */
    public $status;

    /**
     * Holds the response data
     */
    public $data = null;

    /**
     * Holds the response messages
     */
    public $messages = [];
}
