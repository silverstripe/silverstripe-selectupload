(function($) {
	$.widget('SelectUploadField.fileupload', $.blueimpUIX.fileupload, {
		_onSend: function (e, data) {
			//check the array of existing files to see if we are trying to upload a file that already exists
			var that = this;
			var config = this.options;
			if (config.overwriteWarning) {
				var request = {'filename': data.files[0].name};
				// Detect the selected folder if the selector exists
				var folder = that.element.find(".FolderSelector input");
				if(folder.length) request[folder.attr('name')] = folder.val();
				$.get(
					config['urlFileExists'],
					request,
					function(response, status, xhr) {
						if(response.exists) {
							//display the dialogs with the question to overwrite or not
							data.context.find('.ss-uploadfield-item-status')
								.text(config.errorMessages.overwriteWarning)
								.addClass('ui-state-warning-text');
							data.context.find('.ss-uploadfield-item-progress').hide();
							data.context.find('.ss-uploadfield-item-overwrite').show();
							data.context.find('.ss-uploadfield-item-overwrite-warning').on('mousedown', function(e){
								data.context.find('.ss-uploadfield-item-progress').show();
								data.context.find('.ss-uploadfield-item-overwrite').hide();
								data.context.find('.ss-uploadfield-item-status')
									.removeClass('ui-state-warning-text');
								//upload only if the "overwrite" button is clicked
								$.blueimpUI.fileupload.prototype._onSend.call(that, e, data);
								
								e.preventDefault(); // Avoid a form submit
								return false;
							});
						} else {    //regular file upload
							return $.blueimpUI.fileupload.prototype._onSend.call(that, e, data);
						}
					}
				);
			} else {
				return $.blueimpUI.fileupload.prototype._onSend.call(that, e, data);
			}
		}
	});
	
	$.entwine('ss', function($) {

		$('div.ss-upload.ss-selectupload').entwine({
			onmatch: function() {
				this._super();
				// Update the 'formData' method
				var self = this;
				var oldOption = this.fileupload('option', 'formData');
				this.fileupload('option', {	
					formData: function(form) {
						var data = oldOption(form);
						self.find(".FolderSelector input[name]").each(function() {
							data.push({
								name: $(this).attr('name'),
								value: $(this).val()
							});
						});
						return data;
					}
				});
			}
		});
		
		$('div.ss-upload.ss-selectupload .change-folder').entwine({
			onclick: function() {
				var folder = $(this)
					.closest('div.ss-upload.ss-selectupload')
					.find('.select-folder-container');
				if(folder.is(":visible")) {
					folder.fadeOut(200);
				} else {
					folder.fadeIn(200);
				}
			}
		});
	});
}(jQuery));
