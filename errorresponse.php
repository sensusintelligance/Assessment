<?php

define("ERR_FILE_PAGEINFO", 0);
define("ERR_FILE_GLOBALINFO", 1);

class ErrorResponse extends Response
{
    private $m_ErrorCode;

    public function __construct(Request $request, $statusCode, $message, $errorCode)
    {
        parent::__construct($request, $statusCode, $message, HTTP_OUTPUT_HTML);

        $this->m_ErrorCode = $errorCode;
    }

    public function getErrorCode() { return $this->m_ErrorCode; }
}