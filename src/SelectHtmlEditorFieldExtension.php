<?php

namespace SilverStripe\SelectUpload;

class SelectHtmlEditorFieldExtension extends Extension {

	/**
	 * Make the "from cms" folder use the saved folder
	 *
	 * @param int $parentID
	 */
	public function updateAttachParentID(&$parentID) {
		// If given assume that a folder has been posted
		if($parentID) {
			FolderDropdownField::set_last_folder($parentID);
		} else {
			$parentID = FolderDropdownField::get_last_folder();
		}
	}

	/**
	 * Substitute the SelectUploadField in place of the UploadField
	 *
	 * @param Form $form
	 */
	public function updateMediaForm($form) {
		$computerUploadField = SelectUploadField::create('AssetUploadField', '')
			->setPreviewMaxWidth(40)
			->setPreviewMaxHeight(30)
			->addExtraClass('ss-assetuploadfield ss-selectassetuploadfield')
			->removeExtraClass('ss-uploadfield ss-selectuploadfield')
			->setTemplate('HtmlEditorField_SelectUploadField')
			->setForm($form);
		$form->Fields()->replaceField('AssetUploadField', $computerUploadField);
	}
}
