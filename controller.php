<?php

require_once("../httpbase.php");
require_once("../request.php");
require_once("../pagerequest.php");
require_once("../servicerequest.php");

require_once("../response.php");
require_once("../errorresponse.php");

require_once("../csvrecord.php");
require_once("../icsvinstance.php");
require_once("../csvprocessor.php");

require_once("../logentry.php");
require_once("../pageinfo.php");

require_once("../advert.php");

require_once("../pageresponse.php");

require_once("../serviceresponse.php");
require_once("../exportresponse.php");
require_once("../contentresponse.php");
require_once("../tablecontentresponse.php");
require_once("../graphcontentresponse.php");
require_once("../assessmentexportresponse.php");

require_once("../serviceerrorresponse.php");

require_once("../placementpartnercontroller.php");

define("DEFAULT_PAGE", "table");

define("LOG_TAG_ERROR", "ERROR");
define("LOG_TAG_WARN", "WARNING");

class CoreController
{
    private $m_PageContentIdentifier;
    private $m_PageContentFilterStartDate;
    private $m_PageContentFilterEndDate;
    private $m_ClientIPAddress;
    private $m_SessionIdentifier;

    private function __construct($pageIdentifier, $clientIP, $sessionId, $filterDateStart, $filterDateEnd)
    {
        $this->m_PageContentIdentifier = $pageIdentifier;
        $this->m_ClientIPAddress = $clientIP;
        $this->m_SessionIdentifier = $sessionId;
        $this->m_PageContentFilterStartDate = $filterDateStart;
        $this->m_PageContentFilterEndDate = $filterDateEnd;
    }

    private function loadPageInformation()
    {
        $pageInfoTemplate = new CSVProcessor("../templates/pages.csv");

        $numberOfRecords = $pageInfoTemplate->load();

        if ($numberOfRecords == 0)
            throw new Exception("Page Information template contains no records");

        $pageInfo = new PageInfo();

        $pageRowNumber = $pageInfoTemplate->pullFromPrimary($pageInfo, "pageid", $this->m_PageContentIdentifier);

        if($pageRowNumber == -1)
            throw new Exception("Page Information template contains no record for page identifier: [".
                $this->m_PageContentIdentifier."]");

        return $pageInfo;
    }

    private static function log(Request $request, $tag, $message)
    {
        $currentTimestamp = new DateTime();

        $filename = "../logs/".$currentTimestamp->format("Y-m-d").".csv";

        $logFile = new CSVProcessor($filename);

        $entry = new LogEntry($request, $tag, $message);

        try
        {
            $logFile->pushRecord($entry);
        }
        catch(Exception $exception) { }
    }

    private static function buildRequest(CoreController $controller, $getParameters, $postParameters)
    {
        $callFunct = CALL_PAGELOAD;
        $request = null;

        if (isset($getParameters["c"]))
            $callFunct = $getParameters["c"];

        if ($callFunct != CALL_PAGELOAD)
        {
            $parameters = array();

            $request = new ServiceRequest(
                $controller->m_SessionIdentifier,
                $controller->m_ClientIPAddress,
                $callFunct,
                $controller->m_PageContentIdentifier,
                $controller->m_PageContentFilterStartDate,
                $controller->m_PageContentFilterEndDate,
                $parameters);
        }
        else
            $request = new PageRequest(
                $controller->m_SessionIdentifier,
                $controller->m_ClientIPAddress,
                $controller->m_PageContentIdentifier,
                $controller->m_PageContentFilterStartDate,
                $controller->m_PageContentFilterEndDate);

        return $request;
    }

    private static function processContentRequest(CoreController &$controller, ServiceRequest &$request, PageInfo &$pageInfo)
    {
        $serviceFilterDateStart = DateTime::createFromFormat("m/d/Y", $request->getFilterDateStart())->format("Y-m-d");
        $serviceFilterDateEnd = DateTime::createFromFormat("m/d/Y", $request->getFilterDateEnd())->format("Y-m-d");

        switch($request->getPageIdentifier())
        {
            case "table":
            {
                try {
                    $advertList = PlacementPartnerController::getAdverts($serviceFilterDateStart, $serviceFilterDateEnd);
                } catch(Exception $exception) {
                    $message = "Failed to get advert list. Reason: " . $exception->getMessage();

                    $controller->log($request, LOG_TAG_WARN, $message);

                    return new ServiceErrorResponse($request, HTTP_STATUS_SERVICE_UNAVAILABLE, "Network Access Error");
                }

                return new TableContentResponse($request, $pageInfo->getTitle(), $advertList);
            }
            case "bargraph":
            {
                try {
                    $advertList = PlacementPartnerController::getAdverts($serviceFilterDateStart, $serviceFilterDateEnd);
                } catch(Exception $exception) {
                    $message = "Failed to get advert list. Reason: " . $exception->getMessage();

                    $controller->log($request, LOG_TAG_WARN, $message);

                    return new ServiceErrorResponse($request, HTTP_STATUS_SERVICE_UNAVAILABLE, "Network Access Error");
                }

                return new GraphContentResponse($request, $pageInfo->getTitle(), $advertList);
            }
        }

        return new ServiceErrorResponse($request, HTTP_STATUS_BAD_REQUEST, "Invalid Content Request");
    }

    private static function processExportRequest(CoreController &$controller, ServiceRequest &$request)
    {
        $serviceFilterDateStart = DateTime::createFromFormat("m/d/Y", $request->getFilterDateStart())->format("Y-m-d");
        $serviceFilterDateEnd = DateTime::createFromFormat("m/d/Y", $request->getFilterDateEnd())->format("Y-m-d");

        try {
            $advertList = PlacementPartnerController::getAdverts($serviceFilterDateStart, $serviceFilterDateEnd);
        } catch(Exception $exception) {
            $message = "Failed to get advert list. Reason: " . $exception->getMessage();

            $controller->log($request, LOG_TAG_WARN, $message);

            return new ServiceErrorResponse($request, HTTP_STATUS_SERVICE_UNAVAILABLE, "Network Access Error");
        }

        return new AssessmentExportResponse($request, $advertList);
    }

    private static function processServiceRequest(CoreController &$controller, ServiceRequest &$request, PageInfo &$pageInfo)
    {
        switch ($request->getCallFunction())
        {
            case CALL_PAGECONTENT:
                return CoreController::processContentRequest($controller, $request, $pageInfo);
            case CALL_EXPORT:
                return CoreController::processExportRequest($controller, $request);
        }

        return new ServiceErrorResponse($request, HTTP_STATUS_BAD_REQUEST, "Invalid Request");
    }

    public static function initialize($getParameters, $postParameters, $serverParameters)
    {
        $currentTimestamp = new DateTime();

        $clientIP = "Unknown";
        $pageIdentifier = DEFAULT_PAGE;
        $pageFilterDateEnd = $currentTimestamp->format("m/d/Y");

        $currentTimestamp->sub(new DateInterval("P1M"));

        $pageFilterDateStart = $currentTimestamp->format("m/d/Y");
        session_start();
        $sessionId = session_id();

        if (isset($serverParameters["REMOTE_ADDR"]))
            $clientIP = $serverParameters["REMOTE_ADDR"];

        if (isset($getParameters["p"]))
            $pageIdentifier = $getParameters["p"];

        if (isset($getParameters["sf"])) {
            $filterText = $getParameters["sf"];

            $pageFilterDateStart = substr($filterText, 0, 2) . "/" . substr($filterText, 2, 2) . "/" . substr($filterText, 4, 4);
        }

        if (isset($getParameters["ef"])) {
            $filterText = $getParameters["ef"];

            $pageFilterDateEnd = substr($filterText, 0, 2) . "/" . substr($filterText, 2, 2) . "/" . substr($filterText, 4, 4);
        }

        $controller = new CoreController($pageIdentifier, $clientIP, $sessionId, $pageFilterDateStart, $pageFilterDateEnd);

        $request = CoreController::buildRequest($controller, $getParameters, $postParameters);

        $pageInfo = null;

        if($request->loadPageTemplate()) {
            try {
                $pageInfo = $controller->loadPageInformation();
            } catch (Exception $exception) {
                $message = "Failed to load page information. Reason: " . $exception->getMessage();

                CoreController::log($request, LOG_TAG_ERROR, $message);

                return new ErrorResponse($request, HTTP_STATUS_FATAL, $message, ERR_FILE_PAGEINFO);
            }
        }

        $response = null;

        if ($request->getCallFunction() == CALL_PAGELOAD)
            $response = new PageResponse($request, $pageInfo);
        else
            $response = CoreController::processServiceRequest($controller, $request, $pageInfo);

        return $response;
    }
}

$response = CoreController::initialize($_GET, $_POST, $_SERVER);

http_response_code($response->getStatusCode());
header("Content-Type: ".$response->getOutputType());

if ($response->getRequestType() == REQUEST_SERVICE)
{
    if ($response->getCallFunction() == CALL_PAGECONTENT)
        echo $response->exportJSON();
    elseif ($response->getCallFunction() == CALL_EXPORT) {
        header('Content-Disposition: attachment; filename="Regional Report.csv"');
        echo $response->exportCSV();
    }
    flush();
    exit();
}

if (!$response->isSuccess())
{
    flush();
    exit();
}