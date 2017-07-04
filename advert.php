<?php

class Advert
{
    private $m_Region;
    private $m_StartDate;
    private $m_ExpiryDate;

    public function __construct($region, $startDate, $expiryDate)
    {
        $this->m_Region = $region;
        $this->m_StartDate = $startDate;
        $this->m_ExpiryDate = $expiryDate;
    }

    public function getRegion() { return $this->m_Region; }

    public function getStartDate() { return $this->m_StartDate; }

    public function getExpiryDate() { return $this->m_ExpiryDate; }
}