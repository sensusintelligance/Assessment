<?php

class PlacementPartnerController
{
    private static $s_PlacementPartnerWSDL = "https://www.placementpartner.co.za/ws/clients/?wsdl";

    private static $s_PlacementPartnerUsername = "parallel";
    private static $s_PlacementPartnerPassword = "parallel";

    private static $s_SOAPClientArgs = array(
        'exceptions'   => true,
        'cache_wsdl'    => WSDL_CACHE_NONE,
        'soap_version'  => SOAP_1_1,
        'trace'         => 1
    );

    public static function getAdverts($filterDateStart, $filterDateEnd)
    {
        $SOAPClient = new SoapClient(PlacementPartnerController::$s_PlacementPartnerWSDL, PlacementPartnerController::$s_SOAPClientArgs);

        $sessionIdentifier = $SOAPClient->login(PlacementPartnerController::$s_PlacementPartnerUsername, PlacementPartnerController::$s_PlacementPartnerPassword);

        if(!isset($sessionIdentifier))
        {

        }

        $advertFilter = array();

        $responseList = $SOAPClient->getAdverts($sessionIdentifier, $advertFilter);

        $advertList = array();

        if (count($responseList) != 0)
        {
            foreach($responseList as $responseAdvert)
                if(strtotime($responseAdvert->expiry_date) >= strtotime($filterDateStart) && strtotime($responseAdvert->start_date) <= strtotime($filterDateEnd))
                    array_push($advertList, new Advert($responseAdvert->region, $responseAdvert->start_date, $responseAdvert->expiry_date));
        }

        return $advertList;
    }
}