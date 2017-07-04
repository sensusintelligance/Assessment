<?php

abstract class ServiceResponse extends Response
{

    protected function __construct(Request $request, $resultCode, $message)
    {
        parent::__construct($request, $resultCode, $message, HTTP_OUTPUT_JSON);
    }

    protected abstract function packageResults();

    public function exportJSON()
    {
        $jsonArray = array();
        $jsonArray["message"] = $this->getMessage();
        $jsonArray["requestId"] = $this->getRequestIdentifier();
        $jsonArray["pageId"] = $this->getPageIdentifier();

        $jsonArray["results"] = $this->packageResults();

        return json_encode($jsonArray);
    }
}