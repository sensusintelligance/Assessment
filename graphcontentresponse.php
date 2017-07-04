<?php

class GraphContentResponse extends ContentResponse
{
    private $m_Adverts;

    public function __construct(Request $request, $pageTitle, $adverts)
    {
        parent::__construct($request, $pageTitle);

        $this->m_Adverts = $adverts;

        usort($this->m_Adverts, "compareAdverts");
    }


    protected function packageContent()
    {
        $graphContent = array(
            "title" => array(
                "text" => "Regional Tallies"
            ),
            "data" => array()
        );

        $currentDateInstance = DateTime::createFromFormat("m/d/Y", $this->getRequestedFilterDateStart());

        $currentDateWeekDay = $currentDateInstance->format("w");

        if ($currentDateWeekDay != "1")
            $currentDateInstance->sub(new DateInterval("P".$currentDateWeekDay."D"));

        $currentDateInstanceTime = strtotime($currentDateInstance->format("m/d/Y"));

        $processingWeekNumber = 0;

        $processingRegions = array();

        while($currentDateInstanceTime < strtotime($this->getRequestedFilterDateEnd())) {
            $processingWeekNumber++;

            foreach ($this->m_Adverts as $advert) {
                if (!isset($processingRegions[$advert->getRegion()])) {
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

        foreach($processingRegions as $regionKey => $regionWeekTallies)
        {
            for($weekNumber = 0; $weekNumber != $processingWeekNumber; ++$weekNumber)
                if(!isset($regionWeekTallies[$weekNumber]))
                    array_push($regionWeekTallies, 0);

            $regionColumn = array(
                "type" => "column",
                "showInLegend" => true,
                "legendText" => $regionKey,
                "dataPoints" => array()
            );

            $weekNumberLabel = 1;

            foreach($regionWeekTallies as $tally)
                array_push($regionColumn["dataPoints"], array(
                    "label" => "Week #".$weekNumberLabel++,
                    "y" => $tally
                ));

            array_push($graphContent["data"], $regionColumn);
        }

        return array("barGraphOptions" => $graphContent);
    }
}