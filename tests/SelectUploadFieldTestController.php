<?php

namespace SilverStripe\SelectUpload\Tests;

use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;

class SelectUploadFieldTestController extends Controller implements TestOnly
{

    protected $template = 'BlankPage';

    private static $allowed_actions = ['Form'];

    private static $url_segment = 'blank_page';

    public function Form()
    {
        return new SelectUploadFieldTestForm($this, 'Form');
    }
}
