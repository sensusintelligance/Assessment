<?php

class PageInfo implements ICSVInstance
{
    private $m_PageIdentifier;
    private $m_PageTitle;

    private $m_PageMeta;

    public function bind(CSVRecord $record)
    {
        if ($record->hasField("pageid"))
            $this->m_PageIdentifier = $record->getValue("pageid");

        if ($record->hasField("title"))
            $this->m_PageTitle = $record->getValue("title");
    }

    public function package() {}

    public function getIdentifier() { return $this->m_PageIdentifier; }

    public function getTitle() { return $this->m_PageTitle; }

    public function getMetaTags() { return $this->m_PageMeta; }
}