<?php

interface ICSVInstance
{
    public function bind(CSVRecord $record);
    public function package();
}