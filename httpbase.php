<?php

define("REQUEST_PAGE", "Page");
define("REQUEST_SERVICE", "Service");

abstract class HTTPBase
{
    private $m_PageIdentifier;
    private $m_RequestIdentifier;
    private $m_RequestType;
    private $m_RequestTimestamp;
    private $m_SessionId;
    private $m_ClientIP;

    protected function __construct($requestIdentifier, $requestType, $requestTimestamp, $sessionId, $clientIP, $pageIdentifier)
    {
        $this->m_PageIdentifier = $pageIdentifier;
        $this->m_RequestIdentifier = $requestIdentifier;
        $this->m_SessionId = $sessionId;
        $this->m_RequestType = $requestType;
        $this->m_RequestTimestamp = $requestTimestamp;
        $this->m_ClientIP = $clientIP;
    }

    public function getPageIdentifier() { return $this->m_PageIdentifier; }

    public function getRequestIdentifier() { return $this->m_RequestIdentifier; }

    public function getRequestType() { return $this->m_RequestType; }

    public function getRequestTimestamp() { return $this->m_RequestTimestamp; }

    public function getSessionId() { return $this->m_SessionId; }

    public function getClientIP() { return $this->m_ClientIP; }
}