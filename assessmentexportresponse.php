<?php

class AssessmentExportResponse extends ExportResponse
{
    private $m_Adverts;

    public function __construct(Request $request, $adverts)
    {
        parent::__construct($request, HTTP_STATUS_SUCCESS, "SUCCESS");

        $this->m_Adverts = $adverts;

        usort($this->m_Adverts, "compareAdverts");
    }


    protected function packageResults()
    {
        $csvDataPackage = "";

        $currentDateInstance = DateTime::createFromFormat("m/d/Y", $this->getRequestedFilterDateStart());

        $currentDateWeekDay = $currentDateInstance->format("w");

        if ($currentDateWeekDay != "1")
            $currentDateInstance->sub(new DateInterval("P".$currentDateWeekDay."D"));

        $currentDateInstanceTime = strtotime($currentDateInstance->format("m/d/Y"));

        $processingWeekNumber = 0;

        $processingRegions = array();

        $csvDataPackage .= "\"\"";

        while($currentDateInstanceTime < strtotime($this->getRequestedFilterDateEnd())) {
            $processingWeekNumber++;

            foreach ($this->m_Adverts as $advert) {
                if (!isset($processingRegions[$advert->getRegion()])) {
                    $csvDataPackage .= ",\"" . $advert->getRegion() . "\"";
                    $processingRegions[$advert->getRegion()] = array();

                    for ($weekNumber = 0; $weekNumber != $processingWeekNumber; ++$weekNumber)
                        array_push($processingRegions[$advert->getRegion()], 0);
                }

                for ($weekNumber = 0; $weekNumber != $processingWeekNumber; ++$weekNumber)
                    if (!isset($processingRegions[$advert->getRegion()][$weekNumber]))
                        array_push($processingRegions[$advert->getRegion()], 0);

                if ($currentDateInstanceTime > strtotime($advert->getStartDate()) && $currentDateInstanceTime < strtotime($advert->getExpiryDate()))
                    $processingRegions[$advert->getRegion()][$processingWeekNumber - 1] = $processingRegions[$advert->getRegion()][$processingWeekNumber - 1] + 1;
            }

            $currentDateInstance->add(new DateInterval("P1W"));
            $currentDateInstanceTime = strtotime($currentDateInstance->format("m/d/Y"));
        }

        for ($weekNumber = 0; $weekNumber != $processingWeekNumber; ++$weekNumber) {
            $csvDataPackage .= "\r\n\"Week #" . ($weekNumber + 1) . "\"";
            foreach ($processingRegions as $regionKey => $regionWeekTallies) {
                if (!isset($regionWeekTallies[$weekNumber]))
                    $csvDataPackage .= ",0";
                else
                    $csvDataPackage .= "," . $regionWeekTallies[$weekNumber];
            }
        }

        return $csvDataPackage;
    }
}