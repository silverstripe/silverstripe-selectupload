(function($) {
	$.widget('SelectUploadField.fileupload', $.blueimpUIX.fileupload, {
		_onSend: function (e, data) {
			//check the array of existing files to see if we are trying to upload a file that already exists
			var that = this;
			var config = this.options;
			if (config.overwriteWarning) {
				var request = {'filename': data.files[0].name};
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
							data.context.find('.ss-uploadfield-item-overwrite-warning').on('click', function(e){
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

		$('div.ss-upload.ss-selectuploadfield').entwine({
			onmatch: function() {
				this._super();
				// Update the 'formData' method
				var self = this;
				this.fileupload('option', {	
					formData: function(form) {
						var idVal = $(form).find(':input[name=ID]').val();
						var folder = self.find(".FolderSelector input");
						var data = [{name: 'SecurityID', value: $(form).find(':input[name=SecurityID]').val()}];
						if(idVal) data.push({name: 'ID', value: idVal});
						if(folder.length) data.push({name: folder.attr('name'), value: folder.val()});

						return data;
					}
				});
			}
		});
		
		$('div.ss-upload.ss-selectuploadfield .ss-uploadfield-item-name .change-folder').entwine({
			onclick: function() {
				var folder = $(this)
					.closest('div.ss-upload.ss-selectuploadfield')
					.find('.SelectFolderContainer');
				if(folder.is(":visible")) {
					folder.fadeOut(200);
				} else {
					folder.fadeIn(200);
				}
			}
		});
	});
}(jQuery));
