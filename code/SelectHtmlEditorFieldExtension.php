<?php

class SelectHtmlEditorFieldExtension extends Extension {

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
