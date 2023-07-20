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
		<p><i class="icon-info-sign"></i> Before submitting a support ticket, please visit the <a href="/tutorials/">Quick Start Guide</a>.</p>
	 	<p>Fill out the following form to send your comments/suggestions to the site administrator.  Any necessary action will be taken as soon as possible.</p>
		<form id="contact-form" 
			  method="post" 
			  class="form-horizontal">
			{% if session[session_key] is empty %}
				<div class="control-group">
					<label class="control-label" for="first_name">First Name:</label>
					<div class="controls">
						<input class="textbox" type="text" name="first_name" id="first_name" value="{{ support_data.first_name|e }}"/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_name">Last Name:</label>
					<div class="controls">
						<input class="textbox" type="text" name="last_name" id="last_name" value="{{ support_data.last_name|e }}" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="phone">Phone:</label>
					<div class="controls">
						<input class="textbox" type="text" name="phone" id="phone" value="{{ support_data.phone|e }}" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="email">Email:</label>
					<div class="controls">
						<input class="textbox" type="text" name="email" id="email" value="{{ support_data.email|e }}" />
					</div>
				</div>
			{% endif %}
			<div class="control-group">
				<label class="control-label" for="support_category_id">Subject:</label>
				<div class="controls">
					<select name="support_category_id" id="support_category_id">
						{% for single_category in categories %}
							<option {% if single_category.support_category_id == support_data.support_category_id %} selected='selected' {% endif %} value="{{ single_category.support_category_id }}">{{ single_category.category_name|e }}</option>
						{% endfor %}
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="title">Title:</label>
				<div class="controls">
					<input class="textbox" type="text" name="title" id="title" value="{{ support_data.title|e }}" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="body">Body:</label>
				<div class="controls">
					<textarea class="textbox" name="body" id="body" rows="5" cols="">{{ support_data.body|e }}</textarea>
				</div>
			</div>
			{% if configuration.show_file_upload %}
				<div class="control-group">
					<label class="control-label" for="support_file">File:</label>
					<div class="controls">
						<input class="textbox" type="file" name="support_file" id="support_file" />
					</div>
				</div>
			{% endif %}
			{% if session[session_key] is empty %}
				<img id="captcha" src="{{ captcha_generation }}" alt="CAPTCHA Image" />
				<div class="control-group">
					<label class="control-label" for="captcha">Security Code:</label>
					<div class="controls">
						<input type="text" name="captcha" size="10" maxlength="6" />
						<a href="javascript:void(0);" onclick="document.getElementById('captcha').src = '{{ captcha_generation }}?' + Math.random(); return false">[ Different Image ]</a>
					</div>
				</div>
			{% endif %}
			<div id="support_submit_wrapper" class="control-group">
				<input type="submit" name="submit_support" value="Submit" class="btn btn-primary" id="submit_support" />
			</div>
		</form>
	</div>
{% endblock %}