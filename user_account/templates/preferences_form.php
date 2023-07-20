{% extends layout_template_name %}
{% block content %}
<div class="row-fluid">
	{% if flash['success'] %}
		<div class="alert alert-success">
			{{ flash['success'] }}
		</div>
	{% endif %}
	<form id="user_account_search" class="form-horizontal" method="POST">
    	<div class="control-group">
	    	<label class="control-label" for="user">Receive System E-Mails:</label>
	    	<div class="controls">
	    		<input id="send_emails" type="checkbox" name="send_emails" value="1" {% if data.send_emails == 1 %}checked='checked'{% endif %} />
	    	</div>
    	</div>
    	<div class="field-group" style="clear:both;">
	    	<input class="btn btn-primary" type="submit" value="Submit" />
	    </div>
	</form>
</div>
{% endblock %}
{% block js_bottom %}
	{{ parent() }}
	<script type="text/javascript">
		$(document).ready(function(){
		});
	</script> 
{% endblock %}