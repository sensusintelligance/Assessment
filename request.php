<?php

define("CALL_PAGELOAD", "load");
define("CALL_PAGECONTENT", "content");
define("CALL_EXPORT", "export");

abstract class Request extends HTTPBase
{
    private $m_CallFunction;

    private $m_LoadPageTemplate;

    private $m_FilterDateStart;
    private $m_FilterDateEnd;

    protected function __construct($requestType, $sessionId, $clientIP, $callFunct, $pageIdentifier, $filterDateStart, $filterDateEnd)
    {
        $requestTimestamp = new DateTime();
        $requestIdentifier = uniqid();

        parent::__construct($requestIdentifier, $requestType, $requestTimestamp, $sessionId, $clientIP, $pageIdentifier);

        $this->m_CallFunction = $callFunct;

        $this->m_FilterDateStart = $filterDateStart;
        $this->m_FilterDateEnd = $filterDateEnd;

        switch($callFunct)
        {
            case CALL_PAGELOAD:
                $this->m_LoadPageTemplate = true;
                break;
            case CALL_PAGECONTENT:
                $this->m_LoadPageTemplate = true;
                break;
            case CALL_EXPORT:
                $this->m_LoadPageTemplate = true;
                break;
        }
    }

    public function getCallFunction() { return $this->m_CallFunction; }

    public function loadPageTemplate() { return $this->m_LoadPageTemplate; }

    public function getFilterDateStart() { return $this->m_FilterDateStart; }

    public function getFilterDateEnd() { return $this->m_FilterDateEnd; }
}