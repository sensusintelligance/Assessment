<?php

class ServicesContentResponse extends ContentResponse
{
    public function __construct(Request $request, $pageTitle)
    {
        parent::__construct($request, $pageTitle);
    }

    protected function packageContent()
    {
        return array();
    }
}