<?php

class LogEntry implements ICSVInstance
{
    private $m_Request;
    private $m_Tag;
    private $m_Timestamp;
    private $m_Message;

    public function __construct(Request $request, $tag, $message)
    {
        $this->m_Request = $request;
        $this->m_Tag = $tag;
        $this->m_Message = $message;

        $currentTimestamp = new DateTime();

        $this->m_Timestamp = $currentTimestamp->format("H:i:s");
    }

    public function bind(CSVRecord $record) {  }

    public function package()
    {
        $bindings = array();
        $bindings["time"] = $this->m_Timestamp;
        $bindings["tag"] = $this->m_Tag;
        $bindings["requestid"] = $this->m_Request->getRequestIdentifier();
        $bindings["requesttype"] = $this->m_Request->getRequestType();
        $bindings["clientip"] = $this->m_Request->getClientIP();
        $bindings["sessionid"] = $this->m_Request->getSessionId();

        $requestTimestamp = $this->m_Request->getRequestTimestamp();

        $bindings["requesttimestamp"] = $requestTimestamp->format("Y-m-d H:i:s");

        $bindings["message"] = $this->m_Message;

        return $bindings;
    }
}