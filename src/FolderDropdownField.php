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
        $this->setValue(self::get_last_folder());
    }

    /**
     * Set the last folder selected
     *
     * @param int|Folder $folder Folder instance or ID
     */
    public static function set_last_folder($folder)
    {
        if ($folder instanceof Folder) {
            $folder = $folder->ID;
        }
        $request = Controller::curr()->getRequest();
        $session = $request->getSession();
        $session->set(self::class . '.FolderID', $folder);
    }

    /**
     * Get the last folder selected
     *
     * @return int
     */
    public static function get_last_folder()
    {
        $request = Controller::curr()->getRequest();
        $session = $request->getSession();
        return $session->get(self::class . '.FolderID');
    }

    public function setValue($value, $data = null)
    {
        if ($value) {
            self::set_last_folder($value);
        }
        parent::setValue($value);
    }
}
