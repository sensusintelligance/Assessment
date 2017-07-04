<?php

class ServiceErrorResponse extends ServiceResponse
{
    public function __construct(Request $request, $responseCode, $message)
    {
        parent::__construct($request, $responseCode, $message);
    }

    protected function packageResults()
    {
        return null;
    }
}