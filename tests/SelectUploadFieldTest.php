<?php

class SelectUploadFieldTest extends FunctionalTest {

	protected static $fixture_file = 'SelectUploadFieldTest.yml';

	protected $extraDataObjects = array('SelectUploadFieldTest_Record');

	/**
	 * Test that an object can be uploaded against an object with a has_one relation
	 */
	public function testUploadRelation() {
		$this->loginWithPermission('ADMIN');

		// Unset existing has_one relation before re-uploading
		$folder1 = $this->objFromFixture('Folder', 'folder1');
		$record = $this->objFromFixture('SelectUploadFieldTest_Record', 'record1');
		$record->FirstFileID = null;
		$record->SecondFileID = null;
		$record->write();

		// Firstly, ensure the file can be uploaded to the default folder
		$tmpFileName = 'testSelectUploadFile1.txt';
		$response = $this->mockFileUpload('FirstFile', $tmpFileName);
		$this->assertFalse($response->isError());
		$this->assertFileExists(ASSETS_PATH . "/SelectUploadFieldTest/FirstDefaultFolder/$tmpFileName");
		$uploadedFile = File::get()->filter('Name', $tmpFileName)->first();
		$this->assertTrue($uploadedFile instanceof File && $uploadedFile->exists(), 'The file object is created');

		// If another folder is selected then a different folder should be used
		$tmpFileName = 'testSelectUploadFile2.txt';
		$response = $this->mockFileUpload('FirstFile', $tmpFileName, $folder1->ID);
		$this->assertFalse($response->isError());
		$this->assertFileExists(ASSETS_PATH . "/SelectUploadFieldTest/$tmpFileName");
		$uploadedFile = File::get()->filter('Name', $tmpFileName)->first();
		$this->assertTrue($uploadedFile instanceof File && $uploadedFile->exists(), 'The file object is created');
		$this->assertEquals(FolderDropdownField::get_last_folder(), $folder1->ID);

		// Repeating an upload without presenting a folder should use the last used folder
		$tmpFileName = 'testSelectUploadFile3.txt';
		$response = $this->mockFileUpload('SecondFile', $tmpFileName);
		$this->assertFalse($response->isError());
		$this->assertFileExists(ASSETS_PATH . "/SelectUploadFieldTest/$tmpFileName");
		$uploadedFile = File::get()->filter('Name', $tmpFileName)->first();
		$this->assertTrue($uploadedFile instanceof File && $uploadedFile->exists(), 'The file object is created');
		$this->assertEquals(FolderDropdownField::get_last_folder(), $folder1->ID);
	}

	/**
	 * Tests that files that don't exist correctly return false
	 */
	public function testFilesDontExist() {
		$this->loginWithPermission('ADMIN');
		$folder1 = $this->objFromFixture('Folder', 'folder1');
		$folder2 = $this->objFromFixture('Folder', 'folder1');
		$nonFile = uniqid().'.txt';

		// Check that sub-folder non-file isn't found
		$responseRoot = $this->mockFileExists('FirstFile', $nonFile, $folder1->ID);
		$responseRootData = json_decode($responseRoot->getBody());
		$this->assertFalse($responseRoot->isError());
		$this->assertFalse($responseRootData->exists);
		$this->assertEquals(FolderDropdownField::get_last_folder(), $folder1->ID);

		// Check that second level sub-folder non-file isn't found
		$responseRoot = $this->mockFileExists('FirstFile', $nonFile, $folder2->ID);
		$responseRootData = json_decode($responseRoot->getBody());
		$this->assertFalse($responseRoot->isError());
		$this->assertFalse($responseRootData->exists);
		$this->assertEquals(FolderDropdownField::get_last_folder(), $folder2->ID);
	}


	/**
	 * Tests that files that do exist correctly return true
	 */
	public function testFilesDoExist() {
		$this->loginWithPermission('ADMIN');
		$folder1 = $this->objFromFixture('Folder', 'folder1');
		$folder2 = $this->objFromFixture('Folder', 'folder2');

		// Check that sub-folder non-file isn't found
		$responseRoot = $this->mockFileExists('FirstFile', 'file1.txt', $folder1->ID);
		$responseRootData = json_decode($responseRoot->getBody());
		$this->assertFalse($responseRoot->isError());
		$this->assertTrue($responseRootData->exists);
		$this->assertEquals(FolderDropdownField::get_last_folder(), $folder1->ID);

		// Check that second level sub-folder non-file isn't found
		$responseRoot = $this->mockFileExists('FirstFile', 'file2.txt', $folder2->ID);
		$responseRootData = json_decode($responseRoot->getBody());
		$this->assertFalse($responseRoot->isError());
		$this->assertTrue($responseRootData->exists);
		$this->assertEquals(FolderDropdownField::get_last_folder(), $folder2->ID);
	}

	/**
	 * @return Array Emulating an entry in the $_FILES superglobal
	 */
	protected function getUploadFile($tmpFileName = 'SelectUploadFieldTest-testUpload.txt') {
		$tmpFilePath = TEMP_FOLDER . '/' . $tmpFileName;
		$tmpFileContent = '';
		for($i=0; $i<10000; $i++) $tmpFileContent .= '0';
		file_put_contents($tmpFilePath, $tmpFileContent);

		// emulates the $_FILES array
		return array(
			'name' => array('Uploads' => array($tmpFileName)),
			'type' => array('Uploads' => array('text/plaintext')),
			'size' => array('Uploads' => array(filesize($tmpFilePath))),
			'tmp_name' => array('Uploads' => array($tmpFilePath)),
			'error' => array('Uploads' => array(UPLOAD_ERR_OK)),
		);
	}


	/**
	 * Simulates a file upload
	 *
	 * @param string $fileField Name of the field to mock upload for
	 * @param array $tmpFileName Name of temporary file to upload
	 * @param integer $folderID ID of the folder to check in
	 * @return SS_HTTPResponse form response
	 */
	protected function mockFileUpload($fileField, $tmpFileName, $folderID = 0) {
		$upload = $this->getUploadFile($tmpFileName);
		$_FILES = array($fileField => $upload);
		return $this->post(
			"SelectUploadFieldTest_Controller/Form/field/{$fileField}/upload",
			array(
				$fileField => $upload,
				"{$fileField}/folder" => $folderID
			)
		);
	}

	/**
	 * Simulate a check for file exists
	 *
	 * @param string $fileField Name of the field
	 * @param string $fileName Name of the file to check
	 * @param integer $folderID ID of the folder to check in
	 * @return SS_HTTPResponse form response
	 * @return type
	 */
	protected function mockFileExists($fileField, $fileName, $folderID = 0) {
		$request = "SelectUploadFieldTest_Controller/Form/field/{$fileField}/fileexists"
			. "?filename=" . urlencode($fileName)
			. "&" . urlencode("{$fileField}/folder") . "={$folderID}";
		return $this->get($request);
	}

	public function setUp() {
		parent::setUp();

		// Clear saved folder
		FolderDropdownField::set_last_folder(0);

		if(!file_exists(ASSETS_PATH)) mkdir(ASSETS_PATH);

		/* Create a test folders for each of the fixture references */
		$folderIDs = $this->allFixtureIDs('Folder');
		foreach($folderIDs as $folderID) {
			$folder = DataObject::get_by_id('Folder', $folderID);
			if(!file_exists(BASE_PATH."/$folder->Filename")) mkdir(BASE_PATH."/$folder->Filename");
		}

		/* Create a test files for each of the fixture references */
		$fileIDs = $this->allFixtureIDs('File');
		foreach($fileIDs as $fileID) {
			$file = DataObject::get_by_id('File', $fileID);
			$fh = fopen(BASE_PATH."/$file->Filename", "w");
			fwrite($fh, str_repeat('x',1000000));
			fclose($fh);
		}
	}

	public function tearDown() {
		parent::tearDown();

		/* Remove the test files that we've created */
		$fileIDs = $this->allFixtureIDs('File');
		foreach($fileIDs as $fileID) {
			$file = DataObject::get_by_id('File', $fileID);
			if($file && file_exists(BASE_PATH."/$file->Filename")) unlink(BASE_PATH."/$file->Filename");
		}

		/* Remove the test folders that we've crated */
		$folderIDs = $this->allFixtureIDs('Folder');
		foreach($folderIDs as $folderID) {
			$folder = DataObject::get_by_id('Folder', $folderID);
			if($folder && file_exists(BASE_PATH."/$folder->Filename")) {
				Filesystem::removeFolder(BASE_PATH."/$folder->Filename");
			}
		}

		// Remove left over folders and any files that may exist
		if(file_exists(ASSETS_PATH.'/SelectUploadFieldTest')) {
			Filesystem::removeFolder(ASSETS_PATH.'/SelectUploadFieldTest');
		}
	}

}

class SelectUploadFieldTest_Record extends DataObject implements TestOnly {

	private static $db = array(
		'Title' => 'Text',
	);

	private static $has_one = array(
		'FirstFile' => 'File',
		'SecondFile' => 'File'
	);
}


class SelectUploadFieldTestForm extends Form implements TestOnly {

	public function getRecord() {
		if(empty($this->record)) {
			$this->record = DataObject::get_one('SelectUploadFieldTest_Record', '"Title" = \'Record 1\'');
		}
		return $this->record;
	}

	function __construct($controller = null, $name = 'Form') {
		if(empty($controller)) {
			$controller = new UploadFieldTest_Controller();
		}
		$fields = new FieldList(
			SelectUploadField::create('FirstFile', 'File')
				->setFolderName('SelectUploadFieldTest/FirstDefaultFolder'),
			SelectUploadField::create('SecondFile', 'File')
				->setFolderName('SelectUploadFieldTest/SecondDefaultFolder')
		);
		$actions = new FieldList(
			new FormAction('submit')
		);
		$validator = new RequiredFields();

		parent::__construct($controller, $name, $fields, $actions, $validator);

		$this->loadDataFrom($this->getRecord());
	}

	public function submit($data, Form $form) {
		$record = $this->getRecord();
		$form->saveInto($record);
		$record->write();
		return json_encode($record->toMap());
	}
}


class SelectUploadFieldTest_Controller extends Controller implements TestOnly {

	protected $template = 'BlankPage';

	private static $allowed_actions = array('Form');

	public function Form() {
		return new SelectUploadFieldTestForm($this, 'Form');
	}
}
