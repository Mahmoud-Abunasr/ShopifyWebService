<?php

/**
 * BaseController provides the common functions and logic for the controllers
 */
class BaseController
{
    /**
     * Converts the data to json and send it to the client
     *
     * @param  array $data
     */
    public function jsonResponse($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}
