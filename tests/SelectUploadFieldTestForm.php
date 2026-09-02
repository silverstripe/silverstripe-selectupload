<?php

namespace SilverStripe\SelectUpload\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\ORM\DataObject;
use SilverStripe\SelectUpload\SelectUploadField;

class SelectUploadFieldTestForm extends Form implements TestOnly
{

    public function __construct($controller = null, $name = 'Form')
    {
        if (empty($controller)) {
            $controller = new SelectUploadFieldTestController();
        }
        $fields = new FieldList(
            SelectUploadField::create('FirstFile', File::class)
                ->setFolderName('SelectUploadFieldTest/FirstDefaultFolder/'),
            SelectUploadField::create('SecondFile', File::class)
        );
        $actions = new FieldList(
            new FormAction('submit')
        );
        $validator = new RequiredFieldsValidator();

        parent::__construct($controller, $name, $fields, $actions, $validator);

        $this->loadDataFrom($this->getRecord());
    }

    public function getRecord()
    {
        if (empty($this->record)) {
            $this->record = DataObject::get(SelectUploadFieldTestRecord::class)
                ->filter(['Title' => 'Record1'])
                ->first();
        }
        return $this->record;
    }

    public function submit($data, Form $form)
    {
        $record = $this->getRecord();
        $form->saveInto($record);
        $record->write();
        return json_encode($record->toMap());
    }
}
