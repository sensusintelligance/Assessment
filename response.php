<?php

define("HTTP_STATUS_SUCCESS", 200);
define("HTTP_STATUS_BAD_REQUEST", 400);
define("HTTP_STATUS_FATAL", 500);
define("HTTP_STATUS_SERVICE_UNAVAILABLE", 503);

define("HTTP_OUTPUT_HTML", "text/html");
define("HTTP_OUTPUT_CSV", "text/csv");
define("HTTP_OUTPUT_JSON", "application/json");

abstract class Response extends HTTPBase
{
    private $m_StatusCode;
    private $m_OutputType;
    private $m_Message;
    private $m_CallFunct;

    private $m_RequestedFilterDateStart;
    private $m_RequestedFilterDateEnd;

    protected function __construct(Request $request, $statusCode, $message, $outputType)
    {
        parent::__construct(
            $request->getRequestIdentifier(),
            $request->getRequestType(),
            $request->getRequestTimestamp(),
            $request->getSessionId(),
            $request->getClientIP(),
            $request->getPageIdentifier()
        );

        $this->m_StatusCode = $statusCode;
        $this->m_Message = $message;
        $this->m_OutputType = $outputType;
        $this->m_CallFunct = $request->getCallFunction();
        $this->m_RequestedFilterDateStart = $request->getFilterDateStart();
        $this->m_RequestedFilterDateEnd = $request->getFilterDateEnd();
    }

    protected function getRequestedFilterDateStart() { return $this->m_RequestedFilterDateStart; }

    protected function getRequestedFilterDateEnd() { return $this->m_RequestedFilterDateEnd; }

    public function getCallFunction() { return $this->m_CallFunct; }

    public function getStatusCode() { return $this->m_StatusCode; }

    public function getMessage() { return $this->m_Message; }

    public function isSuccess() { return $this->m_StatusCode == HTTP_STATUS_SUCCESS; }

    public function getOutputType() { return $this->m_OutputType; }
}