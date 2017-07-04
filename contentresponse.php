<?php

abstract class ContentResponse extends ServiceResponse
{
    private $m_Title;

    public function __construct(Request $request, $pageTitle)
    {
        parent::__construct($request, HTTP_STATUS_SUCCESS, "SUCCESS");

        $this->m_Title = $pageTitle;
    }

    protected abstract function packageContent();

    protected function packageResults()
    {
        $contentResults = array();
        $contentResults["title"] = $this->m_Title;

        $contentResults["content"] = $this->packageContent();

        return $contentResults;
    }
}