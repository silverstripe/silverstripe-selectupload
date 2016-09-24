<?php

/**
 * A composite form field which allows users to select a folder into which files may be uploaded
 *
 * @package framework
 * @subpackage forms
 */
class SelectUploadField extends UploadField {

	private static $casting = array(
		'DefaultFolderName' => 'Text',
		'FolderName' => 'Text',
		'DisplayFolderName' => 'Text'
	);

	private static $url_handlers = array(
		'folder/tree/$ID' => 'tree'
	);

	private static $allowed_actions = array(
		'tree'
	);

	/**
	 * List of templates for which to disable folder selection.
	 *
	 * @config
	 * @var array
	 */
	private static $disable_for_templates = array(
		'AssetUploadField' // Disable folder selection if this field is used in the AssetAdmin
	);

	/**
	 * Set default permission for selecting folders
	 *
	 * @var array
	 * @config
	 */
	private static $defaultConfig = array(
		'canSelectFolder' => true
	);

	/**
	 * Folder selector field
	 *
	 * @var FolderDropdownField
	 */
	protected $selectField;

	public function __construct($name, $title = null, \SS_List $items = null) {
		parent::__construct($name, $title, $items);

		$this->addExtraClass('ss-selectupload'); // class, used by js
		$this->addExtraClass('ss-selectuploadfield'); // class, used by css for selectuploadfield onl

		$this->selectField = FolderDropdownField::create("{$name}/folder")
			->addExtraClass('FolderSelector')
			->setTitle('Select a folder to upload into');

		// If we haven't uploaded to a folder yet, set to the default foldername
		if(!$this->selectField->Value()) {
			$folderID = $this->folderIDFromPath($this->getDefaultFolderName());
			if($folderID) $this->selectField->setValue($folderID);
		}
	}

	public function Field($properties = array()) {
		$field = parent::Field($properties);
		// Extra requirements
		$base = basename(dirname(__DIR__));
		Requirements::javascript("{$base}/javascript/SelectUploadField.js");
		Requirements::css("{$base}/css/SelectUploadField.css");
		return $field;
	}

	/**
	 * Get the folder selector field
	 *
	 * @return FolderDropdownField
	 */
	public function FolderSelector() {
		return $this->selectField;
	}

	/**
	 * Return the subtree for a selected folder id
	 *
	 * @param SS_HTTPRequest $request
	 * @return string HTTP Response
	 */
	public function tree($request) {
		return $this->FolderSelector()->tree($request);
	}

	public function setForm($form) {
		$this->selectField->setForm($form);
		return parent::setForm($form);
	}

	public function Type() {
		return 'selectupload upload';
	}
    
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;
        $folderID = $this->folderIDFromPath($folderName);
        if ($folderID) $this->selectField->setValue($folderID);
        return $this;
    }

	/**
	 * Given a request, ensure that the current field is aware of the selected folder
	 *
	 * @param SS_HTTPRequest $request
	 */
	protected function updateFolderName($request) {
		// check if allowed to select folder
		if(!$this->canSelectFolder()) return;

		// Get path from upload
		$folderID = $request->requestVar("{$this->Name}/folder");
		$path = $this->folderPathFromID($folderID);
		if($path !== false) {
			$this->setFolderName($path);
		}
	}

	/**
	 * Get path of a folder relative to /assets/ by id.
	 * This will be a format appropriate for setting setFolderName to
	 *
	 * @param int $folderID
	 * @return string|bool Relative path to the assets directory, or false if not found
	 */
	protected function folderPathFromID($folderID) {
		if(empty($folderID)) return false;
		$folder = Folder::get()->byID($folderID);
		if(!$folder) return false;

		// Translate path
		$path = $folder->getFilename();
		if(stripos($path, ASSETS_DIR) === 0) {
			$path = substr($path, strlen(ASSETS_DIR) + 1);
		}
		return $path;
	}

	/**
	 * Gets the ID of a folder given a path relative to /assets/.
	 *
	 * @param string $path
	 * @return int Folder ID
	 */
	protected function folderIDFromPath($path) {
		$folder = File::find($path);
		if($folder) return $folder->ID;
	}

	public function handleRequest(\SS_HTTPRequest $request, \DataModel $model) {
		$this->updateFolderName($request);
		return parent::handleRequest($request, $model);
	}

	/**
	 * Specify whether the user can select an upload folder.
	 * String values will be treated as required permission codes
	 *
	 * @param boolean|string $canSelectFolder Either a boolean flag, or a required
	 * permission code
	 * @return self Self reference
	 */
	public function setCanSelectFolder($canSelectFolder) {
		return $this->setConfig('canSelectFolder', $canSelectFolder);
	}

	/**
	 * Determine if the current member is allowed to change the folder
	 *
	 * @return boolean
	 */
	public function canSelectFolder() {
		if(!$this->isActive()) return false;
		if($this->template && in_array($this->template, self::config()->disable_for_templates)) return false;
		// Check config
		$can = $this->getConfig('canSelectFolder');
		return (is_bool($can)) ? $can : Permission::check($can);
	}

	public function getFolderName() {
		// Ensure that, if this member is allowed, the persistant folder overrides any default set
		if($this->canSelectFolder()) {
			$path = $this->folderPathFromID($this->selectField->Value());
			if($path !== false) return $path;
		}
		return $this->getDefaultFolderName();
	}

	/**
	 * Get the 'default' folder name
	 *
	 * @return string
	 */
	public function getDefaultFolderName() {
		return parent::getFolderName();
	}

	public function getDisplayFolderName() {
		$name = $this->getFolderName();
		return preg_replace('/\s*\\/\s*/', ' / ', trim($name, '/'));
	}
}
