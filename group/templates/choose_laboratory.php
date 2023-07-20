{% extends layout_template_name %}
{% block content %}
	<div class="row-fluid">
		{% if errors %}
			<div class="alert alert-block">
				<h4>Form Errors</h4>
				{% for single_error in errors %}
				<p>{{ single_error }}</p>
				{% endfor %}
			</div>
		{% endif %}
		{% if flash['success'] %}
			<div class="alert alert-success">
				{{ flash['success'] }}
			</div>
		{% else %}
			<div class="alert alert-error alert-preview">
	        	<i class="icon-exclamation-sign"></i> Hello {{ givenname }}! Please choose a laboratory. The administrator will contact you with further instructions.
	      	</div>
			<form method="POST" class="form-horizontal">
		    	<div class="control-group">
			    	<label for="labs"></label>
					<select id="labs" name="labs" class="labs_select_menu">
						<option value="">Select Your Laboratory</option>
						{% for laboratory in laboratories %}
					  		<option value="{{ laboratory.laboratories_id }}">{{ laboratory.laboratory_name }}</option>
						{% endfor %}
					</select>
		    	</div>
		    	<div class="control-group">
		    		<input class="btn btn-primary" type="submit" value="Submit" />
		    	</div>
		    </form>
	    {% endif %}
   </div>
{% endblock %}
{% block js_bottom %}
{{ parent() }}
<script type="text/javascript">
  $(document).ready(function(){
    $("#show_side_nav").remove();
    $(".sidebar-nav").remove();
  });
</script>
{% endblock %}