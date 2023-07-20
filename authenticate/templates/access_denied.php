{% extends layout_template_name %}
{% block content %}
	<div class="row-fluid">
		<div class="hero-unit">
			<h2>OOOOOOOOOOOOOOOOOOOOOOOOOPS...</h2>
			{% if flash.message %}
					<div class="alert alert-block alert-danger">
						<p>{{ flash.message }}</p>
					</div>
			{% else %}
				<div class="alert alert-block alert-danger">
						<p>You do not have sufficient privledges to view this page.  Please contact your administrator.</p>
				</div>
			{% endif %} 
		</div>		
	</div>
{% endblock %}