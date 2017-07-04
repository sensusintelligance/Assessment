<?php

class PageResponse extends Response
{
    private $m_PageInfo;

    public function __construct(Request $request, PageInfo $pageInfo)
    {
        parent::__construct($request, HTTP_STATUS_SUCCESS, "SUCCESS", HTTP_OUTPUT_HTML);

        $this->m_PageInfo = $pageInfo;
    }

    public function getActiveContentTabName() { return $this->m_PageInfo->getIdentifier(); }

    public function getTitle() { return $this->m_PageInfo->getTitle(); }

    public function getFilterStartDateDefault()
    {
        return $this->getRequestedFilterDateStart();
    }

    public function getFilterEndDateDefault()
    {
        return $this->getRequestedFilterDateEnd();
    }
}