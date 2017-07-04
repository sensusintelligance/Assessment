<?php

class CSVProcessor
{
    private $m_CSVFilePath;
    private $m_Columns;
    private $m_Records;

    public function __construct($csvFilePath)
    {
        $this->m_CSVFilePath = $csvFilePath;
        $this->m_Records = array();
        $this->m_Columns = array();
    }

    public function load()
    {
        if(array_count_values($this->m_Records) > 0)
        {
            $this->m_Records = array();
            $this->m_Columns = array();
        }

        if (!file_exists($this->m_CSVFilePath))
            throw new Exception($this->m_CSVFilePath." does not exist");

        $fileHandle = fopen($this->m_CSVFilePath, "r");

        if ($fileHandle == false)
            throw new Exception($this->m_CSVFilePath." could not be opened");

        $line = fgetcsv($fileHandle);

        if ($line == false)
        {
            fclose($fileHandle);
            throw new Exception($this->m_CSVFilePath . " could not read csv format");
        }

        $this->m_Columns = $line;

        $numberOfColumns = count($this->m_Columns);

        $rowNumber = 0;

        while(!feof($fileHandle))
        {
            $line = fgetcsv($fileHandle);

            if ($line == false)
                break;

            $numberOfFields = count($line);

            if ($numberOfFields == 1 && $line[0] == null)
                continue;

            $csvRecordBindings = array();

            for($fieldIndex = 0; $fieldIndex != $numberOfFields; ++$fieldIndex)
            {
                if ($numberOfColumns <= $fieldIndex)
                    break;

                $csvRecordBindings[$this->m_Columns[$fieldIndex]] = $line[$fieldIndex];
            }

            $this->m_Records[$rowNumber] = new CSVRecord($rowNumber, $csvRecordBindings);
            $rowNumber++;
        }

        fclose($fileHandle);

        return count($this->m_Records);
    }

    public function pullFromPrimary(ICSVInstance &$instance, $primaryIndex, $searchValue)
    {
        $headingFound = false;

        foreach($this->m_Columns as $fieldHeading)
            if ($fieldHeading == $primaryIndex)
            {
                $headingFound = true;
                break;
            }

        if (!$headingFound)
            return -1;

        foreach($this->m_Records as $record)
            if ($record->getValue($primaryIndex) == $searchValue)
            {
                $instance->bind($record);
                return $record->getRowNumber();
            }

        return -1;
    }

    public function pullFromRowNumber(ICSVInstance &$instance, $rowNumber)
    {
        if(count($this->m_Records) <= $rowNumber)
            return false;

        $instance->bind($this->m_Records[$rowNumber]);

        return true;
    }

    public function pushRecord(ICSVInstance $instance)
    {
        $bindings = $instance->package();
        $exportArray = array();
        $insertHeader = false;

        if (!file_exists($this->m_CSVFilePath))
        {
            $this->m_Columns = array_keys($bindings);

            $exportArray = array_values($bindings);

            $this->m_Records[0] = new CSVRecord(0, $bindings);
            $insertHeader = true;
        }
        else
        {
            $numberOfRecords = $this->load();

            foreach($this->m_Columns as $fieldName)
            {
                if (isset($bindings[$fieldName]))
                    array_push($exportArray, $bindings[$fieldName]);
                else
                    array_push($exportArray, "");
            }

            $this->m_Records[$numberOfRecords] = new CSVRecord($numberOfRecords, $bindings);
        }

        $fileHandle = fopen($this->m_CSVFilePath, "a");

        if ($fileHandle == false)
            throw new Exception($this->m_CSVFilePath." could not be opened");

        if ($insertHeader)
        {
            if (fputcsv($fileHandle, $this->m_Columns) == false)
            {
                fclose($fileHandle);
                throw new Exception($this->m_CSVFilePath." could not insert entry");
            }
        }

        if (fputcsv($fileHandle, $exportArray) == false)
        {
            fclose($fileHandle);
            throw new Exception($this->m_CSVFilePath." could not insert entry");
        }

        fclose($fileHandle);
    }

    public function getNumberOfLoadedRecords() { return count($this->m_Records); }
}