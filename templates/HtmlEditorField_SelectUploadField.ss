<div class="ss-uploadfield-item ss-uploadfield-addfile field ss-uploadfield">

	<h4>
		<span class="step-label">
			<span class="flyout">1</span><span class="arrow"></span>
			<span class="title">
				<% if $canSelectFolder %>
					<small><% _t('UploadField.ADDTO', 'Add to') %>
						<span class="change-folder">
							<strong class="folder-name">$DisplayFolderName</strong>
							<span class="change">(change)</span>
						</span>
					</small>
				<% else_if $canPreviewFolderx %>
					<small><% _t('UploadField.ADDTO', 'Add to') %> <strong>$DisplayFolderName</strong></small>
				<% else %>
					<small><% _t('AssetUploadField.ChooseFiles', 'Choose files') %></small>
				<% end_if %>
			</span>
		</span>
	</h4>
	
	<% if $canSelectFolder %><% with FolderSelector %>
		<div class="select-folder-container">
			$Field
		</div>
	<% end_with %><% end_if %>

	<div class="ss-uploadfield-item-info">
		<label class="ss-uploadfield-fromcomputer ss-ui-button ss-ui-action-constructive" title="<% _t('AssetUploadField.FROMCOMPUTERINFO', 'Upload from your computer') %>" data-icon="drive-upload-large">
			<% _t('AssetUploadField.TOUPLOAD', 'Choose files to upload...') %>
			<input id="$id" name="$getName" class="$extraClass ss-uploadfield-fromcomputer-fileinput" data-config="$configString" type="file"<% if $multiple %> multiple="multiple"<% end_if %> title="<% _t('AssetUploadField.FROMCOMPUTER', 'Choose files from your computer') %>" />
		</label>

		<div class="clear"><!-- --></div>
	</div>
	<div class="ss-uploadfield-item-uploador">
		<% _t('AssetUploadField.UPLOADOR', 'OR') %>
	</div>
	<div class="ss-uploadfield-item-preview ss-uploadfield-dropzone">
		<div>
			<span><% _t('AssetUploadField.DRAGFILESHERE', 'Drag files here') %></span>
		</div>
	</div>
	<div class="clear"><!-- --></div>
</div>

<div class="ss-uploadfield-editandorganize">
	<ul class="ss-uploadfield-files files"></ul>
</div>