<?php

namespace SilverStripe\SelectUpload;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Assets\Folder;

/**
 * Represents a TreeDropdownField for folders which remembers the last folder
 * selected.
 */
class FolderDropdownField extends TreeDropdownField
{
    public function __construct(
        $name,
        $title = null,
        $sourceObject = Folder::class,
        $keyField = 'ID',
        $labelField = 'TreeTitle',
        $showSearch = true
    ) {
        parent::__construct($name, $title, $sourceObject, $keyField, $labelField, $showSearch);

        $this->setValue($this->getLastFolderID());
    }

    /**
     * Set the last folder selected
     *
     * @param int $folderID Folder ID
     */
    public function setLastFolderID($folderID)
    {
        $request = Controller::curr()->getRequest();
        $session = $request->getSession();
        $session->set($this->getSessionKey(), $folderID);

        return $this;
    }

    /**
     * Get the last folder selected
     *
     * @return int
     */
    public function getLastFolderID()
    {
        $request = Controller::curr()->getRequest();
        $session = $request->getSession();

        return $session->get($this->getSessionKey());
    }

    public function setValue($value, $data = null)
    {
        if ($value) {
            $this->setLastFolderID($value);
        }
        parent::setValue($value);
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return self::class .'.'. $this->name . '.FolderID';
    }
}
