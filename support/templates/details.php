{% extends layout_template_name %}
{% block content %}
	<div class="row-fluid">
		<div class="detail_item">
			<div class="detail_title">
				Submitter:
			</div>	
			<div class="detail_value">
				{{ support_data.first_name|e }} {{ support_data.last_name|e }} (<a href='mailto:{{ support_data.email|e }}'>{{ support_data.email|e }}</a>)
			</div>
		</div>
		<div class="detail_item">
			<div class="detail_title">
				Title:
			</div>	
			<div class="detail_value">
				{{ support_data.title|e }}
			</div>
		</div>
		<div class="detail_item">
			<div class="detail_title">
				Body:
			</div>	
			<div class="detail_value">
				{{ support_data.body|e }}
			</div>
		</div>
		<div class="detail_item">
			<div class="detail_title">
				Category:
			</div>	
			<div class="detail_value">
				{{ support_data.category_name|e }}
			</div>
		</div>
		<div class="detail_item">
			<div class="detail_title">
				File:
			</div>	
			<div class="detail_value">
				{% if support_file_data %}
					<a href="{{ path_to_this_module }}/download/{{ support_file_data.support_file_id|e }}">{{ support_file_data.file_name|e }}</a> ({{ support_file_data.file_type|e }})
				{% else %}
					N/A
				{% endif %}
			</div>
		</div>
		<div class="detail_item">
			<a href="{{ path_to_this_module }}/browse" title="Back to Browse">&larr; Back to Browse</a>
		</div>
	</div>
{% endblock %}