{% extends layout_template_name %} 
{% block styles_head %}
	{{ parent() }}
	<link href="//cdnjs.cloudflare.com/ajax/libs/chosen/0.9.11/chosen.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}
	<div class="row-fluid">
		<h3>{{ account_info.displayname }}</h3>
		<p><strong>Email address:</strong> <a href="mailto:{{ account_info.email }}">{{ account_info.email }}</a></p>
		<hr>
		{% if errors %}
				<div class="alert alert-block">
					<h4>Form Errors</h4>
					{% for single_error in errors %}
					<p>{{ single_error }}</p>
					{% endfor %}
				</div>
		{% endif %} 
		<form id="user_account_form" class="form-horizontal" method="POST">
			<div class="field-group" style="clear:both;">
		    	<label for="group" style="float:left;">Add Group:</label>
		    	<div class="field" style="float:left;">
		    		<select id="group" name="group">
		    			<option value="0">Select..</option>
		    			{% for single_group in groups %}
			    			<option {% if single_group.admin == false %} disabled='disabled' {% else %} style="color:black;" {% endif %} value="{{ single_group.group_id }}">{{ single_group.indent }}{{ single_group.name }} ({{ single_group.abbreviation }})</option>
		    			{% endfor %}
		    		</select>
		    	</div>
	    	</div>
			<div id="selected_groups_title">Selected Groups:</div>
			<ul id="selected_groups">
				
			</ul>
			<input type="hidden" name="group_data" id="group_data" />
			<div class="field-group" style="clear:both;">
	        	<input class="btn btn-primary" type="submit" value="Submit" />
	       </div>
	    </form>
   </div>
{% endblock %}
{% block js_bottom %}
	{{ parent() }}
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.2/underscore-min.js"></script>
	<!-- <script src="//cdnjs.cloudflare.com/ajax/libs/chosen/0.9.11/chosen.jquery.min.js"></script> -->
	<script type="text/javascript" src="/{{ core_type }}/javascripts/chosen/chosen-0.9.11/chosen.jquery.min.js"></script>
	<script id="single_group_template" type="text/template">
    	<li id="<%- single_group_container_id %>" class="single_group_container" data-group="<%- group_id %>">
    		<div class="group_description" style="float:left;"><%- group_name %></div>
    		<div class="remove_group_container">
    			<a href="javascript:void(0);" title="Remove this Group" style="float:right;">remove</a>
    		</div>
    		<div style="clear:both; margin-top:8px;">
    			<div style="clear:both; float:left; margin-right:15px;"><strong>Roles: </strong></div>
    			<div style="margin-left:10px; float:left;" class="div_group_select">
	    			<select multiple="true" size="4" data-placeholder="Select Role(s)..." class="role_select chzn-select">
	    				<% for (var key in role_choices){ %> 
	    					<option value="<%- role_choices[key]['role_id'] %>" showOnSelect="<%- role_choices[key]['label'] %>"><%- role_choices[key]['label'] %></option>
	    				<% }; %>
	    			</select>
	    		</div>
    		</div>
    		<div class="proxy_container" style="clear:both;">
    		</div>
    	</li>
    </script>
    <script id="proxy_template" type="text/template">
		<label>Proxy for: </label>
		<ul class="proxy_list"></ul>
		<input class="user_lookup" placeholder="Start typing to add user.." role="typeahead" type="text" />
    </script>
    <script id="proxy_user_template" type="text/template">
    	<li class="proxy_user" data-id="<%- account_id %>"><%- displayname %> <a href="javascript:void(0);" role="remove_proxy_user" class="icon-remove-circle"></a></li>
    </script>
    <script type="text/javascript">
    	$(document).ready(function(){
			var role_choices = JSON.parse('{{ roles|json_encode|raw }}');
			$("#group").on("change",function(){
				var selected_option = $(this).find(":selected");
				var group_id = selected_option.val();
				var group_name = selected_option.text();
				var exists = $("#selected_groups li.single_group_container[data-group='" + group_id + "']");
				if(group_id != 0 && exists.length == 0){
					add_group(group_id,group_name);
				}
			});
			
			//remove group link
			$("#user_account_form").on("click.remove_group", ".remove_group_container",function(){
				$(this).closest("li.single_group_container").remove();
			});
			
			//remove proxy user
			$("#user_account_form").on("click.remove_proxy_user","[role='remove_proxy_user']", function(){
	    		$(this).closest(".proxy_user").fadeOut("fast",function(){
	    			$(this).remove();
	    		});
	    	});
			
			//listen for proxys
			$("#user_account_form").on("change.manage_proxy", ".role_select",function(event, existing_proxy_users){
				if($(this).find("option[value='{{ proxy_id }}']:selected").length > 0 && $(this).closest(".single_group_container").find(".proxy_container .proxy_list").length == 0){
					var proxy_template = _.template($("#proxy_template").html());
					var proxy_markup = proxy_template({});
					var proxy_container = $(this).closest("li.single_group_container").find(".proxy_container");
					proxy_container.html(proxy_markup);
					if(typeof existing_proxy_users !== "undefined" && $.isArray(existing_proxy_users)){
						$.each(existing_proxy_users,function(index,single_proxy){
							add_proxy_user(single_proxy.account_id, single_proxy.displayname, proxy_container);
						});
					}
					$(this).closest("li.single_group_container").find("input[role='typeahead']").each(function(){
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
								add_proxy_user(mapped[item], item, $this.closest(".proxy_container"));
			            		return '';
							}
						});
					});
				}else{
					//$(this).closest("li.single_group_container").find(".proxy_container").empty();
				}
			});
			
			function add_proxy_user(account_id, displayname, proxy_container){
				var proxy_user_template = _.template(jQuery("#proxy_user_template").html());
				var proxy_user_markup = proxy_user_template({
					account_id: account_id
					,displayname: displayname
				});
				proxy_container.find(".proxy_list").append(proxy_user_markup);
			}
			
			function add_group(group_id,group_name,roles,proxy_users){
				var single_group_container_id = _.uniqueId("single_group_container_");
				var single_group_template = _.template($("#single_group_template").html());
				var single_group_markup = single_group_template({
					group_id: group_id
					,group_name: group_name
					,role_choices: role_choices
					,single_group_container_id: single_group_container_id
				});
	
				$("#selected_groups").append(single_group_markup);
				if(roles){
					$("#" + single_group_container_id).find("select.role_select").val(roles);
					$("#" + single_group_container_id).find("select.role_select").trigger("change", [proxy_users]);
				}
				
				$("#selected_groups").animate({scrollTop: $("#selected_groups")[0].scrollHeight},1000);					
				$("#" + single_group_container_id).find("select.role_select").chosen();
			}
				
			//gather author data
			$("#user_account_form").submit(function(event){
				var group_data = {};
				var counter = 0;
				$("#selected_groups > li").each(function(){
					group_data[counter] = {
						"group_id":$(this).attr("data-group")
						,"roles": $(this).find("select.role_select").val()
					};
					group_data[counter].proxy_users = $(this).find(".proxy_list .proxy_user").map(function(index,el){
	    				return {
	    					account_id: $(el).data("id")
	    					,displayname: $(el).text()
	    				};
	    			}).get();
					counter++;
				});
				$("#group_data").attr("value",JSON.stringify(group_data));
			});
		
			//populate with existing data
			var existing_group_data = JSON.parse('{{ user_account_groups|json_encode|raw }}');
			$.each(existing_group_data,function(key,value){
				add_group(value.group_id,value.group_name,value.roles,value.proxy_users);
			});
    	});
    </script>
{% endblock %}