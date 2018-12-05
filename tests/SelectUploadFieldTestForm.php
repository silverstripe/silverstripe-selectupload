<?php

namespace SilverStripe\SelectUpload\Tests;


use SilverStripe\Assets\File;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;
use SilverStripe\SelectUpload\SelectUploadField;

class SelectUploadFieldTestForm extends Form implements TestOnly
{

    public function getRecord()
    {
        if (empty($this->record)) {
            $this->record = DataObject::get_one(SelectUploadFieldTestRecord::class, '"Title" = \'Record 1\'');
        }
        return $this->record;
    }

    function __construct($controller = null, $name = 'Form')
    {
        if (empty($controller)) {
            $controller = new SelectUploadFieldTestController();
        }
        $fields = new FieldList(
            SelectUploadField::create('FirstFile', File::class)
                ->setFolderName('SelectUploadFieldTest/FirstDefaultFolder'),
            SelectUploadField::create('SecondFile', File::class)
                ->setFolderName('SelectUploadFieldTest/SecondDefaultFolder')
        );
        $actions = new FieldList(
            new FormAction('submit')
        );
        $validator = new RequiredFields();

        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->loadDataFrom($this->getRecord());
    }

    public function submit($data, Form $form)
    {
        $record = $this->getRecord();
        $form->saveInto($record);
        $record->write();
        return json_encode($record->toMap());
    }
}
