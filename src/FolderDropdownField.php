<?php

namespace SilverStripe\SelectUpload;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Session;

/**
 * Represents a TreeDropdownField for folders which remembers the last folder selected
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
    public function setLastFolderID(int $folderID)
    {
        $request = Controller::curr()->getRequest();
        $session = $request->getSession();
        $session->set(get_class() . '.FolderID', $folderID);
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
        return $session->get(self::class . '.FolderID');
    }

    public function setValue($value, $data = null)
    {
        if ($value) {
            $this->setLastFolderID($value);
        }
        parent::setValue($value);
    }
}
