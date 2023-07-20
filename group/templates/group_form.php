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
		<form method="POST" class="form-horizontal">
	    	<div class="control-group">
		    	<label class="control-label" for="name"><span style="color:red;">*</span>Name:</label>
		    	<div class="controls">
		    		<input name="name" id="name" type="text" value="{{ group_data.name|e }}"/>
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="abbreviation"><span style="color:red;">*</span>Abbreviation:</label>
		    	<div class="controls">
		    		<input name="abbreviation" id="abbreviation" type="text" value="{{ group_data.abbreviation|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="description">Description:</label>
		    	<div class="controls">
		    		<textarea name="description" rows="5" cols="50">{{ group_data.description|e }}</textarea>
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="address_1">Address 1:</label>
		    	<div class="controls">
		    		<input name="address_1" id="address_1" type="text" value="{{ group_data.address_1|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="address_2">Address 2:</label>
		    	<div class="controls">
		    		<input name="address_2" id="address_2" type="text" value="{{ group_data.address_2|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="city">City:</label>
		    	<div class="controls">
		    		<input name="city" id="city" type="text" value="{{ group_data.city|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="state">State:</label>
		    	<div class="controls">
		    		<input name="state" id="state" type="text" value="{{ group_data.state|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="zip">Zip Code:</label>
		    	<div class="controls">
		    		<input name="zip" id="zip" type="text" value="{{ group_data.zip|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="primary_contact_name">Primary Contact Name:</label>
		    	<div class="controls">
		    		<input name="primary_contact_name" id="primary_contact_name" type="text" value="{{ group_data.primary_contact_name|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="primary_contact_email_address">Primary Contact Email Address:</label>
		    	<div class="controls">
		    		<input name="primary_contact_email_address" id="primary_contact_email_address" type="text" value="{{ group_data.primary_contact_email_address|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="secondary_contact_name">Secondary Contact Name:</label>
		    	<div class="controls">
		    		<input name="secondary_contact_name" id="secondary_contact_name" type="text" value="{{ group_data.secondary_contact_name|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="secondary_contact_email_address">Secondary Contact Email Address:</label>
		    	<div class="controls">
		    		<input name="secondary_contact_email_address" id="secondary_contact_email_address" type="text" value="{{ group_data.secondary_contact_email_address|e }}" />
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="disclaimer">Disclaimer:</label>
		    	<div class="controls">
		    		<textarea name="disclaimer" rows="5" cols="50">{{ group_data.disclaimer|e }}</textarea>
	    		</div>
	    	</div>
	    	<div class="control-group">
		    	<label class="control-label" for="group_parent">Direct Parent Group:</label>
	    		<div class="controls">
		    		<select id="group_parent" name="group_parent" size="15" style="width:420px;">
		    			{% for single_group in groups %}
		    				{% if single_group.group_id != group_data.group_id %}
			    				<option {% if single_group.group_id == group_data.group_parent %} selected='selected' {% endif %} value="{{ single_group.group_id }}">{{ single_group.indent }}{{ single_group.name }} ({{ single_group.abbreviation }})</option>
		    				{% endif %}
		    			{% endfor %}
		    		</select>
		    	</div>
	    	</div>
	    	
	    	<div class="control-group">
	    		<input class="btn btn-primary" type="submit" value="Submit" />
	    	</div>
	    </form>
   </div>
{% endblock %}
{% block js_bottom %}
	{{ parent() }}
	<script type="text/javascript">
    	$(document).ready(function(){
    		$("#group_parent").on("click",function(event){
    			if(event.ctrlKey){
    				$(this).find(":selected").removeAttr("selected");
    			}
    		});
    	});
    </script>
{% endblock %}