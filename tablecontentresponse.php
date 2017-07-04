<?php

function compareAdverts($advertA, $advertB)
{
    return strtotime($advertA->getStartDate()) - strtotime($advertB->getStartDate());
}

class TableContentResponse extends ContentResponse
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
        $tableContent = array();

        $tableContent["columns"] = array();

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

        $weekColumn = array(
            "title" => "",
            "rowData" => array()
        );

        for($weekNumber = 0; $weekNumber != $processingWeekNumber; ++$weekNumber)
            array_push($weekColumn["rowData"], "Week #".($weekNumber + 1));

        array_push($tableContent["columns"], $weekColumn);

        foreach($processingRegions as $regionKey => $regionWeekTallies)
        {
            for($weekNumber = 0; $weekNumber != $processingWeekNumber; ++$weekNumber)
                if(!isset($regionWeekTallies[$weekNumber]))
                    array_push($regionWeekTallies, 0);

            $regionColumn = array(
                "title" => $regionKey,
                "rowData" => array()
            );

            foreach($regionWeekTallies as $tally)
                array_push($regionColumn["rowData"], $tally);

            array_push($tableContent["columns"], $regionColumn);
        }

        return $tableContent;
    }
}