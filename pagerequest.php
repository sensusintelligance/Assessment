<?php

class PageRequest extends Request
{
    public function __construct($sessionId, $clientIP, $pageIdentifier, $filterDateStart, $filterDateEnd)
    {
        parent::__construct(REQUEST_PAGE, $sessionId, $clientIP, CALL_PAGELOAD, $pageIdentifier, $filterDateStart, $filterDateEnd);
    }
}