{% extends layout_template_name %}
{% block styles_head %}
	{{ parent() }}
	<link href="/{{ core_type }}/javascripts/aloha-editor/alohaeditor-0.22.7/aloha/css/aloha.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}
	<div class="row-fluid">
		<form class="form-horizontal" method="POST">
			<div class="control-group">
		    	<label class="control-label" for="admin_emails">Admin Emails:</label>
		    	<div class="controls">
		    		<input name="admin_emails" id="admin_emails" type="text" value="{{ settings_data.admin_emails|e }}"/>
		    	</div>
	    	</div>
            
            <div class="control-group">
		    	<label class="control-label" for="show_file_upload">Show File Upload:</label>
		    	<div class="controls">
		    		<input name="show_file_upload" id="show_file_upload" type="checkbox" value="1" {% if settings_data.show_file_upload %} checked='checked' {% endif %} />
		    	</div>
	    	</div>
	    	
            <div class="control-group">
		    	<label class="control-label" for="confirmation_email">Confirmation Email:</label>
		    	<div class="controls">
		    		<input name="confirmation_email" id="confirmation_email" type="checkbox" value="1" {% if settings_data.confirmation_email %} checked='checked' {% endif %} />
		    	</div>
	    	</div>
	    	
            <div class="control-group">
		    	<label class="control-label" for="email_from">Email From:</label>
		    	<div class="controls">
		    		<input name="email_from" id="email_from" type="text" value="{{ settings_data.email_from|e }}"/>
		    	</div>
	    	</div>
	    	
	    	<div class="control-group">
		    	<label class="control-label" for="email_subject">Email Subject:</label>
		    	<div class="controls">
		    		<input name="email_subject" id="email_subject" type="text" value="{{ settings_data.email_subject|e }}"/>
		    	</div>
	    	</div>
	    	
	    	<div class="control-group">
		    	<label class="control-label" for="email_body">Email Body:</label>
		    	<div class="controls">
		    		<textarea placeholder="Type Email Body.." name="email_body" rows="5" cols="50">{{ settings_data.email_body|e }}</textarea>
		    	</div>
	    	</div>
	    	
	    	<div class="control-group">
		    	<label class="control-label" for="email_signature">Email Signature:</label>
		    	<div class="controls">
		    		<textarea placeholder="Type Email Signature.." name="email_signature" rows="5" cols="50">{{ settings_data.email_signature|e }}</textarea>
		    	</div>
	    	</div>
			<div class="control-group">
	    		<input class="btn btn-primary" type="submit" name="update_settings" value="Submit" />
	    	</div>
		</form>
	</div>
{% endblock %}
{% block js_bottom %}
	{{ parent() }}
	<script type="text/javascript" src="/{{ core_type }}/javascripts/aloha-editor/alohaeditor-0.22.7/aloha/lib/vendor/jquery-1.7.2.js"></script>
	<script>
		Aloha = window.Aloha || {};
		Aloha.settings = Aloha.settings || {};
		// Restore the global $ and jQuery variables of your project's jQuery
		Aloha.settings.jQuery = window.jQuery.noConflict(true);
	</script>
	<script type="text/javascript" src="/{{ core_type }}/javascripts/aloha-editor/alohaeditor-0.22.7/aloha/lib/require.js"></script>
	<script src="/{{ core_type }}/javascripts/aloha-editor/alohaeditor-0.22.7/aloha/lib/aloha.js"
	       data-aloha-plugins="common/ui,
	                            common/format,
	                            common/characterpicker">
	</script>
	<script type="text/javascript">
		Aloha.settings.sidebar = {disabled:true};
		Aloha.ready( function() {
			Aloha.jQuery('textarea').aloha();
        });
	</script>
{% endblock %}