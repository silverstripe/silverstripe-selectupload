<?php

namespace SilverStripe\SelectUpload\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\File;

class SelectUploadFieldTestRecord extends DataObject implements TestOnly
{

    private static $db = [
        'Title' => 'Text',
    ];

    private static $has_one = [
        'FirstFile'  => File::class,
        'SecondFile' => File::class
    ];
}
