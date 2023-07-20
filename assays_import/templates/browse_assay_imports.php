{% extends layout_template_name %}
{% block styles_head %}
  {{ parent() }}
  <link href="/{{ core_type }}/javascripts/DataTables-SWPG/css/bootstrap_datatables.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}
{% if flash['success'] %}
  <div class="alert alert-success">
    {{ flash['success'] }}
  </div>
{% endif %}
<div class="row-fluid">
  <p><i class="icon-info-sign"></i> Before you begin, please check out the <a href="/tutorials/">Quick Start Guide</a>.</p>
  <hr>
  <table id="browse_table" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">
    <thead>
      <tr>
        {% for field in browse_fields %}
          <th><div class="th_header_text">{{ field.label }}</div>
            {% if field.filter %}
              <div class="filter_wrapper {% if field.filter_type == 'select' %} filter_wrapper_select {% endif %}">
                {% if field.filter_type == 'select' %}
                  <select name="{{ key }}_value" class="filter_value">
                  <option value="">Select...</option>
                  {% for single_option in field.filter_type_options %}
                    <option value="{{ single_option.reagent_types_id }}">{{ single_option.label }}</option>
                  {% endfor %}
                </select>
              {% else %}
                <input type="text" name="{{ key }}_value" class="filter_value">
                {% endif %}
                <div class="filter_image" data-data_type="{{ field.data_type }}" id="{{ key }}"></div>
                <input type="hidden" class="comparison_value" id="{{ key }}_filter_value" value="{{ field.comparison_default }}">
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
      $(document).ready(function() {
        var sortable_fields = new Array();
        sortable_fields = JSON.parse('{{ browse_fields|json_encode|raw }}');
        var total_columns = new Array();
        var hidden_columns = new Array();
        var exclude_columns_for_hiding = new Array();

        var user_updated_visible_columns = $.cookie("browse_table_column_cookie");
        if(typeof user_updated_visible_columns === "undefined" || user_updated_visible_columns === null){
          user_updated_visible_columns = {};
        }else{
          user_updated_visible_columns = JSON.parse(user_updated_visible_columns);
        }
        var counter = 0;
        $.each(sortable_fields, function(index, field_data){

          total_columns.push( {"mDataProp":index} );
          if(field_data.initially_hidden == true){
            if(user_updated_visible_columns[counter] !== true){
              hidden_columns.push(counter);
            }
          } else {
            if(user_updated_visible_columns[counter] === false){
              hidden_columns.push(counter);
            }
          }
          if(field_data.show_column_toggle == false){
            exclude_columns_for_hiding.push(counter);
          }
          counter++;

        });
        
        $('.filter_value, .filter_image').bind('click', function(event){
          event.stopPropagation();
        });
        
        $('#browse_table th input.filter_value').bind('keyup', function(event){
          page_datatable.fnDraw();
        });
      
        $('.filter_image').each(function(){
          $(this).dynamicContextMenu({
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
                $('#'+sender._id+'_filter_value').attr('value', item.value);
                page_datatable.fnDraw();
              }
              ,waitTime: 1
          });
        });
        
        var details_page = "{{ path_to_this_module }}/insert_update/";
        var page_datatable = $('#browse_table').dataTable({ 
          //show processing throbber
            "bProcessing": true
            //move the info to the top instead of the bottom
            //,"sDom":'<"top"fli>rt<"bottom"p<"clear">>'
            ,"sDom": "<'row'C><<'span4'l><'span4'i><'span4'f>><<'span3 datatables_bulk_actions'><'span3'r><'span5 pull-right'p>>t<'row'<'span6 pull-right'p>>"
            //all data management will be done on the server side
            ,"bServerSide": true
            //path to the file that is going to handle the queries
            ,"sAjaxSource": "{{ path_to_this_module }}/datatables_browse_assay_imports"
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
              {"sWidth":"160px","aTargets":[4]}
              ,{"bSortable":false,"aTargets":[0]}
              ,{"bVisible":false,"aTargets":hidden_columns}
            ]
            ,"fnServerParams": function ( aoData ) {
                var filter_array = $.map($("#browse_table th"), function(e, i){ 
                    var single_filter = {};
                    single_filter.column = $(e).find('div.filter_image').attr('id');
                    single_filter.value = $(e).find('input.filter_value').val();
                    single_filter.comparison = $(e).find('input.comparison_value').val();              
                    return single_filter; 
                });
                var newObj = {name: "column_filter", value:JSON.stringify(filter_array)};
                aoData.push(newObj);
            }
            ,"fnRowCallback":function(nRow, aData, iDisplayIndex){
              
              $(nRow).find('td:eq(1)').attr("width","35%");
              // Create execute button.
              $(nRow).find('td:eq(4)').html(
                '<div class="btn-container"><a href="{{ path_to_this_module }}/insert_update/'+aData.import_log_id+'" class="btn btn-small btn-default"><i class="icon-pencil"></i> Manage Metadata</a>&nbsp;<a href="{{ path_to_this_module }}/execute/?import_log_id='+aData.import_log_id+'" class="btn btn-small btn-default"><i class="icon-cog"></i> Manage Import</a></div>'
              )
              .addClass("manage_column");
            }
        });
        
        //send to details page when clicked
        $('#browse_table tbody').on('click','td',function(event){
          if(!$(this).hasClass("manage_column")){
            var project_id = $(this).closest("tr").attr('id');
            window.location.href = details_page + project_id;
          }
        });
        
     
  
    });
    
    // setInterval(function() {
    //   window.location.reload();
    // }, 300000);
  </script>
{% endblock %}