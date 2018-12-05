<?php

namespace SilverStripe\SelectUpload\Tests; 

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Filesystem;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\SelectUpload\FolderDropdownField;

class SelectUploadFieldTest extends FunctionalTest
{

    protected static $fixture_file = 'SelectUploadFieldTest.yml';

    protected static $extra_dataobjects = [SelectUploadFieldTestRecord::class];

    /**
     * Test that an object can be uploaded against an object with a has_one relation
     */
    public function testUploadRelation()
    {
        $controller = Injector::inst()->get(SelectUploadFieldTestController::class);
        $form = $controller->Form();
        $a=1;
        /*$this->loginWithPermission('ADMIN');

        // Unset existing has_one relation before re-uploading
        $folder1 = $this->objFromFixture(Folder::class, 'folder1');
        $record = $this->objFromFixture(SelectUploadFieldTestRecord::class, 'record1');
        $record->FirstFileID = null;
        $record->SecondFileID = null;
        $record->write();
        // Director::publicFolder() . '/assets/uploads'
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
        $this->assertEquals(FolderDropdownField::get_last_folder(), $folder1->ID);*/
    }

    /**
     * Tests that files that don't exist correctly return false
     */
    public function testFilesDontExist()
    {
        $this->loginWithPermission('ADMIN');
        $folder1 = $this->objFromFixture(Folder::class, 'folder1');
        $folder2 = $this->objFromFixture(Folder::class, 'folder1');
        $nonFile = uniqid() . '.txt';

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
    public function testFilesDoExist()
    {
        $this->loginWithPermission('ADMIN');
        $folder1 = $this->objFromFixture(Folder::class, 'folder1');
        $folder2 = $this->objFromFixture(Folder::class, 'folder2');

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
    protected function getUploadFile($tmpFileName = 'SelectUploadFieldTest-testUpload.txt')
    {
        $tmpFilePath = TEMP_FOLDER . '/' . $tmpFileName;
        $tmpFileContent = '';
        for ($i = 0; $i < 10000; $i++) {
            $tmpFileContent .= '0';
        }
        file_put_contents($tmpFilePath, $tmpFileContent);

        // emulates the $_FILES array
        return [
            'name'     => ['Uploads' => [$tmpFileName]],
            'type'     => ['Uploads' => ['text/plaintext']],
            'size'     => ['Uploads' => [filesize($tmpFilePath)]],
            'tmp_name' => ['Uploads' => [$tmpFilePath]],
            'error'    => ['Uploads' => [UPLOAD_ERR_OK]],
        ];
    }


    /**
     * Simulates a file upload
     *
     * @param string $fileField Name of the field to mock upload for
     * @param array $tmpFileName Name of temporary file to upload
     * @param integer $folderID ID of the folder to check in
     * @return HTTPResponse form response
     */
    protected function mockFileUpload($fileField, $tmpFileName, $folderID = 0)
    {
        $upload = $this->getUploadFile($tmpFileName);
        $_FILES = [$fileField => $upload];
        $p = $this->post(
            "SelectUploadFieldTestController/Form/field/{$fileField}/upload",
            [
                $fileField            => $upload,
                "{$fileField}/folder" => $folderID
            ]
        );
        return $p;
    }

    /**
     * Simulate a check for file exists
     *
     * @param string $fileField Name of the field
     * @param string $fileName Name of the file to check
     * @param integer $folderID ID of the folder to check in
     * @return HTTPResponse form response
     * @return type
     */
    protected function mockFileExists($fileField, $fileName, $folderID = 0)
    {
        $request = "SelectUploadFieldTestController/Form/field/{$fileField}/fileexists"
            . "?filename=" . urlencode($fileName)
            . "&" . urlencode("{$fileField}/folder") . "={$folderID}";
        return $this->get($request);
    }

    public function setUp()
    {
        parent::setUp();

        $request = Controller::curr()->getRequest();

        $session = new Session(['key' => 'value']);
        $session->init($request);

        $request->setSession($session);

        // Clear saved folder
        FolderDropdownField::set_last_folder(0);

        if (!file_exists(ASSETS_PATH)) {
            mkdir(ASSETS_PATH);
        }

        /* Create a test folders for each of the fixture references */
        $folderIDs = $this->allFixtureIDs(Folder::class);
        foreach ($folderIDs as $folderID) {
            $folder = DataObject::get_by_id(Folder::class, $folderID);
            $path = Director::publicFolder() . '/' . $folder->Filename;
            if (!file_exists($path)) {
                mkdir($path);
            }
        }

        /* Create a test files for each of the fixture references */
        $fileIDs = $this->allFixtureIDs(File::class);
        foreach ($fileIDs as $fileID) {
            $file = DataObject::get_by_id(File::class, $fileID);
            $path = Director::publicFolder();
            if($file->ParentID) {
                $path .= '/' . $file->Parent()->FileName;
            }
            $path .= $file->Name;
            $fh = fopen($path, "w");
            fwrite($fh, str_repeat('x', 1000000));
            fclose($fh);
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        /* Remove the test files that we've created */
        $fileIDs = $this->allFixtureIDs(File::class);
        foreach ($fileIDs as $fileID) {
            $file = DataObject::get_by_id(File::class, $fileID);
            if ($file && file_exists(ASSETS_PATH . "/$file->Name")) {
                unlink(ASSETS_PATH . "/$file->Name");
            }
        }

        /* Remove the test folders that we've crated */
        $folderIDs = $this->allFixtureIDs(Folder::class);
        foreach ($folderIDs as $folderID) {
            $folder = DataObject::get_by_id(Folder::class, $folderID);
            if ($folder && file_exists(ASSETS_PATH . "/$folder->Name")) {
                Filesystem::removeFolder(ASSETS_PATH . "/$folder->Name");
            }
        }

        // Remove left over folders and any files that may exist
        if (file_exists(ASSETS_PATH . '/SelectUploadFieldTest')) {
            Filesystem::removeFolder(ASSETS_PATH . '/SelectUploadFieldTest');
        }
    }

}
