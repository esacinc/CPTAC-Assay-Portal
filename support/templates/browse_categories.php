{% extends layout_template_name %}
{% block styles_head %}
	{{ parent() }}
	<link href="/{{ core_type }}/javascripts/DataTables-SWPG/css/bootstrap_datatables.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}
<div class="row-fluid">
	<table id="browse_table" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">
		<thead>
			<tr>
				{% for field in categories_browse_fields %}
			    <th><div class="th_header_text">{{ field.label }}</div>
			    	{% if field.filter %}
					    <div class="filter_wrapper">
	    					<input type="text" name="{{ field.handle }}_value" class="filter_value">
	    					<div class="filter_image" id="{{ field.handle }}"></div>
	    					<input type="hidden" class="comparison_value" id="{{ field.handle }}_filter_value" value="{{ field.comparison_default }}">
	    				</div>
					{% endif %}
				</th>
			  	{% endfor %}
			</tr>
		</thead>
	</table> 
</div>
{% endblock %}
{% block js_bottom %}
	{{ parent() }}
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-SWPG/js/datatables_bootstrap.js"></script>
	<script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-SWPG/js/context_menu.js"></script>
	<script type="text/javascript">
	    jQuery(document).ready(function() {
	    	var sortable_fields = new Array();
	    	sortable_fields = JSON.parse('{{ categories_browse_fields|json_encode|raw }}');
	    	var total_columns = new Array();
	    	
	      	jQuery.each(sortable_fields, function(index, field_data){
	        	total_columns.push( {"mDataProp":index} );
	      	});
	      
	  		jQuery('.filter_value, .filter_image').bind('click', function(event){
		  		event.stopPropagation();
	  		});
	      
	      	jQuery('#browse_table th input.filter_value').bind('keyup', function(event){
	    		page_datatable.fnDraw();
	  	  	});
	      
	      	jQuery('.filter_image').each(function(){
	        	jQuery(this).dynamicContextMenu({
	          		id: this.id
	          		,style:{
	            		containerCssClass: "DynamicContextMenuContainer",
	            		subLevelHolderItemCssClass: "DynamicContextMenuSubLevelHolderItem",
	            		itemIconCssClass: "DynamicContextMenuItemIcon"
	          		}
	          		,items: [
	            		{text: '> greater than', value: 'gt'}
	            		,{text: '>= (greater than OR equal to)', value: 'gt_or_eq'}
	            		,{text: '< less than', value: 'lt'}
	            		,{text: '<= (less than OR equal to)', value: 'lt_or_eq'}
	            		,{text: '= (equal to)', value: 'equals'}
	            		,{text: 'Contains', value: 'contains'}
	            		,{text: 'Does not contain', value: 'not_contain'}
	            		,{text: 'Starts with', value: 'start_with'}
	            		,{text: 'Ends With', value: 'end_with'}
	          		]
	          		,openMode: "click"
	          		,onItemClick: function (sender, item) {
	            		jQuery('#'+sender._id+'_filter_value').attr('value', item.value);
	            		page_datatable.fnDraw();
	          		}
	          		,waitTime: 1
	        	});
	      	});
	  	  
	  		var details_page = "{{ path_to_this_module }}/categories/manage/";
		    var page_datatable = jQuery('#browse_table').dataTable({ 
		    	//show processing throbber
	        	"bProcessing": true
	        	//move the info to the top instead of the bottom
	        	//,"sDom":'<"top"fli>rt<"bottom"p<"clear">>'
	        	,"sDom": "<'row'C><<'span4'l><'span4'i><'span4'f>><<'span3 datatables_bulk_actions'><'span3'r><'span5 pull-right'p>>t<'row'<'span6 pull-right'p>>"
	        	//all data management will be done on the server side
	        	,"bServerSide": true
	        	//path to the file that is going to handle the queries
	        	,"sAjaxSource": "{{ path_to_this_module }}/datatables_browse_categories"
	        	//method type
	        	,"sServerMethod": "POST"
	        	//match the html table columns with the fields returned form the query
	        	,"aoColumns": total_columns
	        	//values in the length dropdown
	        	,"aLengthMenu":[5,10,25,50]
	        	//default sort
	        	,"aaSorting":[[0,"desc"]]
	        	//needed for bootstrap
	        	,"sWrapper": "dataTables_wrapper form-inline"
	        	,"sPaginationType":"bootstrap"
	        	//set some widths
	        	,"aoColumnDefs":[
	           		{"sWidth":"150px","aTargets":[0]}
	           		,{"bSortable":false,"aTargets":[0]}
	        	]
	        	,"fnServerParams": function ( aoData ) {
	            	var filter_array = jQuery.map(jQuery("#browse_table th"), function(e, i){ 
	              		var single_filter = {};
	              		single_filter.column = jQuery(e).find('div.filter_image').attr('id');
	              		single_filter.value = jQuery(e).find('input.filter_value').val();
	              		single_filter.comparison = jQuery(e).find('input.comparison_value').val();              
	              		return single_filter; 
	            	});
	            	var newObj = {name: "column_filter", value:JSON.stringify(filter_array)};
	            	aoData.push(newObj);
	        	}
	        	,"fnRowCallback":function(nRow, aData, iDisplayIndex){
	        		//create checkboxes
	        		jQuery(nRow).find('td:eq(0)').html(
		        		"<input type='checkbox' name='manage_checkbox' value='" + aData['manage'] + "' />"
		        	)
		        	.addClass("manage_column");    		
		        }
		    });
		    
		    //send to details page when clicked
		    jQuery('#browse_table tbody').on('click','td',function(event){
		    	if(!jQuery(this).hasClass("manage_column")){
			    	var project_id = jQuery(this).closest("tr").attr('id');
			    	window.location.href = details_page + project_id;
		    	}
		    });

		    var delete_button = jQuery("<div></div>")
		    	.addClass("delete")
		    	.on("click",function(){
		    		var delete_ids = new Array;
		    		jQuery('#browse_table [name="manage_checkbox"]:checked').each(function(){
		    			delete_ids.push(jQuery(this).val());
		    		});
		    		if(delete_ids.length > 0){
		    			var delete_confirm = confirm("Are you sure you want to delete the selected item(s)?");
		    			if(delete_confirm){
			    			jQuery.ajax({
							    type:"POST"
							    ,dateType:"json"
							    ,url: "{{ path_to_this_module }}/categories/delete"
							    ,data: ({id: JSON.stringify(delete_ids)})
							    ,success: function(ajax_return){
							    	page_datatable.fnDraw();
							    }
							});
		    			}
		    		}
		    		
		    		//page_datatable.fnDeleteRow(0);
		    	});
		    $(".datatables_bulk_actions").append(delete_button);
	
		});
	</script>
{% endblock %}