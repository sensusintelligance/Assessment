<?php

class CSVRecord
{
    private $m_RowNumber;
    private $m_DataBindings;

    public function __construct($rowNumber, $dataBindings)
    {
        $this->m_RowNumber = $rowNumber;
        $this->m_DataBindings = $dataBindings;
    }

    public function hasField($fieldHeading) { return isset($this->m_DataBindings[$fieldHeading]); }

    public function getValue($fieldHeading) { return $this->m_DataBindings[$fieldHeading]; }

    public function getRowNumber() { return $this->m_RowNumber; }
}