<?php

abstract class ExportResponse extends Response
{
    protected function __construct(Request $request, $resultCode, $message)
    {
        parent::__construct($request, $resultCode, $message, HTTP_OUTPUT_CSV);
    }

    protected abstract function packageResults();

    public function exportCSV()
    {
        return $this->packageResults();
    }
}