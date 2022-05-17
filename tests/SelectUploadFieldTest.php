<?php

namespace SilverStripe\SelectUpload\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Filesystem;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\SelectUpload\FolderDropdownField;
use SilverStripe\SelectUpload\SelectUploadField;

class SelectUploadFieldTest extends FunctionalTest
{

    protected static $fixture_file = 'SelectUploadFieldTest.yml';

    protected static $extra_dataobjects = [SelectUploadFieldTestRecord::class];

    protected $form;

    protected function setUp(): void
    {
        parent::setUp();

        $request = Controller::curr()->getRequest();

        $session = new Session(['key' => 'value']);
        $session->init($request);

        $request->setSession($session);

        // Clear saved folder
        $folder = FolderDropdownField::create("test/folder");
        $folder->setLastFolderID(0);

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

        $controller = Injector::inst()->get(SelectUploadFieldTestController::class);
        $this->form = $controller->Form();
    }

    protected function tearDown(): void
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

    /**
     * Test that the SelectUploadField field contains a FolderDropdownField
     */
    public function testFolderSelector()
    {
        $form = $this->form;
        $field = $form->fields[0];
        $this->assertInstanceOf(SelectUploadField::class, $field);

        $folderSelector = $field->FolderSelector();
        $this->assertInstanceOf(FolderDropdownField::class, $folderSelector);
    }

    public function testGetFolderNameDefaultFolder()
    {
        $form = $this->form;
        $field = $form->fields[1];
        $folderPath = $field->getFolderName();
        $this->assertSame('Uploads', $folderPath);
    }

    public function testGetFolderNameDefinedFolder()
    {
        $form = $this->form;
        $field = $form->fields[0];
        $folderPath = $field->getFolderName();
        $this->assertSame('SelectUploadFieldTest/FirstDefaultFolder/', $folderPath);
    }

    public function testGetFolderNameFromDropdown()
    {
        $form = $this->form;
        $field = $form->fields[1];
        $folderSelector = $field->FolderSelector();
        // set value to ID of folder2 from .yml file
        $folderSelector->setValue(3);
        $folderPath = $field->getFolderName();

        $this->assertSame('SelectUploadFieldTest/Subfolder/', $folderPath);
    }

    public function testGetCanSelectFolder()
    {
        $form = $this->form;
        $field = $form->fields[1];
        $this->assertTrue($field->getCanSelectFolder());
    }

    public function testSetCanSelectFolder()
    {
        $form = $this->form;
        $field = $form->fields[1];
        $this->assertTrue($field->getCanSelectFolder());
        $field->setCanSelectFolder(false);
        $this->assertFalse($field->getCanSelectFolder());
    }
}
