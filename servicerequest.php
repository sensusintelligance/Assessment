<?php

class ServiceRequest extends Request
{
    private $m_Parameters;

    public function __construct($sessionId, $clientIP, $callFunct, $pageIdentifier, $filterDateStart, $filterDateEnd, $parameters)
    {
        parent::__construct(REQUEST_SERVICE, $sessionId, $clientIP, $callFunct, $pageIdentifier, $filterDateStart, $filterDateEnd);

        $this->m_Parameters = array();
    }
}