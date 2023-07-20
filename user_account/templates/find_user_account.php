{% extends layout_template_name %}
{% block content %}
<div class="row-fluid">
	<form id="user_account_search" class="form-horizontal" method="POST">
    	<div class="control-group">
	    	<label class="control-label" for="user"><span style="color:red;">*</span>Name:</label>
	    	<div class="controls">
	    		<input role="typeahead" size="50" id="user" type="text" data-typeahead-target="client-id" />
	    		<input type="hidden" id="client-id" name="client-id">
	    		<input class="btn btn-primary" type="submit" value="Submit" />
	    	</div>
    	</div>
	</form>
</div>
{% endblock %}
{% block js_bottom %}
	{{ parent() }}
	<script type="text/javascript">
		$(document).ready(function(){
			$("input[role='typeahead']").each(function(){
				$this = $(this);
				$this.typeahead({
					minLength:2
					,items: 10
					,source: function(query,process){
						$.ajax({
							url:"{{ path_to_this_module }}/find"
							,dataType:"json"
							,type:"post"
							,data: {search:query}
							,success:function(data){
								labels = [];
								mapped = {};
								$.each(data, function(i,item) {
				                    mapped[item.displayname] = item.account_id;
				                    labels.push(item.displayname);
				                });
								process(labels);
							}
						});
					}
					,updater: function(item){
						$('#'+$this.attr('data-typeahead-target')).val(mapped[item]);
	            		return item;
					}
				});
			});
			
			$("#user_account_search").submit(function(event){
				event.preventDefault();
				window.location.href = '{{ path_to_this_module }}/manage/' + $("#client-id").val();
			});
		});
	</script>
{% endblock %}