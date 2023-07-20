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
		<form class="form-horizontal" method="POST">
			<div class="control-group">
		    	<label class="control-label" for="category_name"><span style="color:red;">*</span>Category Name:</label>
		    	<div class="controls">
		    		<input name="category_name" id="category_name" size="40" type="text" value="{{ category_data.category_name|e }}"/>
		    	</div>
	    	</div>
			<div class="control-group">
	    		<input class="btn btn-primary" type="submit" value="Submit" />
	    	</div>
		</form>
	</div>
{% endblock %}