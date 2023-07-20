// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {

	// undefined is used here as the undefined global variable in ECMAScript 3 is
	// mutable (ie. it can be changed by someone else). undefined isn't really being
	// passed in so we can ensure the value of it is truly undefined. In ES5, undefined
	// can no longer be modified.

	// window and document are passed through as local variable rather than global
	// as this (slightly) quickens the resolution process and can be more efficiently
	// minified (especially when both are regularly referenced in your plugin).

	// Create the defaults once
	var pluginName = "uploadFileManager",
		defaults = {
			allowMultiple: true
			,addFileLabel: "Add another file"
			,fileNamePrepend: "file"
			,previouslyUploadedFiles: []
			,previouslyUploadedFileNamePrepend: "uploaded"
			,downloadFileRoute: false //use :file_id as placeholder for the file_id
		},
		templates = {
			container: '\
<div class="ufm_previously_uploaded_files">\
</div>\
<div class="ufm_total_file_upload_div">\
</div>\
<div class="ufm_add_file_button_wrapper">\
	<a href="javascript:void(0);" class="ufm_add_file btn btn-small"><i class="icon-plus"></i><%- addFileLabel %></a>\
</div>',
			addFileUpload: '\
<div class="ufm_single_upload_container">\
    	<div class="control-group">\
    		<input name="<%- fileNamePrepend %>[]"\
    		 	   type="file"/>\
    		<span class="help-inline"><a href="javascript:void(0);" class="btn btn-small ufm_remove_upload">Remove <i class="icon-remove-sign"></i></a></span>\
    	</div>\
</div>',
			addFileDisplay: '\
<div class="ufm_single_file_display_container">\
    	<div class="control-group">\
    		<%- file.fileName %>\
    		<input type="hidden" name="<%- settings.previouslyUploadedFileNamePrepend %>[]" value="<%- file.fileId %>" />\
    		<span class="help-inline"><a href="javascript:void(0);" class="btn btn-small btn-success ufm_download_previous_file" data-fileId="<%- file.fileId %>">Download <i class="icon-download"></i></a></span>\
    		<span class="help-inline"><a href="javascript:void(0);" class="btn btn-small btn-danger ufm_remove_previous_file_display">Remove <i class="icon-remove-sign"></i></a></span>\
    	</div>\
</div>'
		};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	Plugin.prototype = {
		init: function () {
			// Place initialization logic here
			// You already have access to the DOM element and
			// the options via the instance, e.g. this.element
			// and this.settings
			// you can add more functions like the one below and
			// call them like so: this.yourOtherFunction(this.element, this.settings).

			//create initial HTML structure
			var container = _.template(templates.container, this.settings);
			$(this.element).append(container);

			//display any previously uploaded files
			if(this.settings.previouslyUploadedFiles.length !== 0){
				this.addFileDisplay(this.settings.previouslyUploadedFiles);
			}

			//bind all events
			$(this.element).on('click.add_file', '.ufm_add_file', {this_plugin: this}, this.addFileUpload);
			$(this.element).on('click.remove_file', '.ufm_remove_upload', {this_plugin: this}, this.removeFileUpload);
			$(this.element).on('click.remove_previous_file_display', '.ufm_remove_previous_file_display', {this_plugin: this}, this.removePreviousFileDisplay);
			$(this.element).on('click.download_previous_file', '.ufm_download_previous_file', {this_plugin: this}, this.downloadPreviousFile);


			//some events need fired on initialization
			this.addFileUpload();
		},
		addFileUpload: function(event){
			//data is available to this method differently, depending on how it is fired
			var settings = (typeof event !== 'undefined' && event.data.this_plugin.settings) ? event.data.this_plugin.settings : this.settings;
			var element = (typeof event !== 'undefined' && event.data.this_plugin.element) ? event.data.this_plugin.element : this.element;

			var singleFileUpload = _.template(templates.addFileUpload, settings);
			$(".ufm_total_file_upload_div", element).append(singleFileUpload);
		},
		removeFileUpload: function(event){
			$(event.target).closest(".ufm_single_upload_container").remove();
		},
		addFileDisplay: function(files){
			var settings = this.settings;
			var element = this.element;
			$.each(files, function(index, singleFile){
				var data = {settings: settings, file: singleFile};
				var singleFileDisplay = _.template(templates.addFileDisplay, data);
				$(".ufm_previously_uploaded_files", element).append(singleFileDisplay);
			});
		}
		,removePreviousFileDisplay: function(event){
			$(event.target).closest(".ufm_single_file_display_container").remove();
			alert("Please submit this form to delete the file");
		}
		,downloadPreviousFile: function(event){
			var settings = (typeof event !== 'undefined' && event.data.this_plugin.settings) ? event.data.this_plugin.settings : this.settings;
			var element = (typeof event !== 'undefined' && event.data.this_plugin.element) ? event.data.this_plugin.element : this.element;
			
			var fileId = $(this).attr('data-fileId');
			var fileURL = settings.downloadFileRoute.replace(':file_id', fileId);
			window.location.href= fileURL;
		}
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[ pluginName ] = function ( options ) {
		this.each(function() {
			if ( !$.data( this, "plugin_" + pluginName ) ) {
				$.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
			}
		});

		// chain jQuery functions
		return this;
	};

})( jQuery, window, document );