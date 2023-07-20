{% extends layout_template_name %}
{% block styles_head %}
  {{ parent() }}
  <link href="/{{ core_type }}/javascripts/DataTables-SWPG/css/bootstrap_datatables.css" rel="stylesheet" type="text/css" />
  <link href="/{{ core_type }}/javascripts/DataTables-1.9.0/extras/ColVis/media/css/ColVis.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}

<div class="row-outer">

  <div class="row-fluid" style="clear:both;">
    {% if flash['success'] %}
      <div class="alert alert-success">
        {{ flash['success'] }}
      </div>
    {% endif %}

    {% for single_datatable in datatables %}

      <div class="row-fluid" style="clear:both;">
        <div class="span6">
          <label for="labs"><i class="icon-hand-right"></i> Choose a Laboratory</label>
          <select id="labs" name="labs" class="labs_select_menu">
            <option value="">Select</option>
            {% for laboratory in laboratories %}
              <option value="{{ laboratory.laboratories_id }}">{{ laboratory.laboratory_name }} ({{ laboratory.abbreviation }})</option>
            {% endfor %}
          </select>
        </div>
        <div class="span3">
          <label id="import_set_label" for="import_set"><i class="icon-hand-right"></i> Metadata Date</label>
          <select id="import_set" name="import_set" class="import_set_select_menu">
          </select>
        </div>
      </div>

      <hr />

      <div id="notices">
        <h4><i class="icon-info-sign"></i> Remember to hit the submit button to apply changes&hellip;</h4>
        <img src="{{ path_to_this_module }}/library/images/submit_button_screenshot.jpg" width="500" height="265" alt="Submit button screenshot">
      </div>

      <div id="additional_filters" class="row-fluid" style="clear:both;">
        <div class="span4">
          <label for="status_filter"><i class="icon-eye-open"></i> Toggle Approved/Disapproved</label>
          <select id="status_filter" name="status_filter" class="status_filter_select_menu">
            <option value="">Display All</option>
            <option value="0">Display Disapproved</option>
            <option value="1">Display Approved</option>
            <option value="2">Display Pending</option>
            
          </select>
        </div>
      </div>

        <table id="browse_table" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">
          <thead>
            <tr>
              {% for key, field in single_datatable.fields %}
                <th><div class="th_header_text">{{ field.label }}</div>
                  {% if field.filter %}
                    <div class="filter_wrapper {% if field.filter_type == 'select' %} filter_wrapper_select {% endif %}">
                      {% if field.filter_type == 'select' %}
                        <select name="{{ key }}_value" class="filter_value">
                        <option value="">Select</option>
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

    {% endfor %}
  </div>
</div>

<!-- Notes modal -->
<div id="notes_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addNotesModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">Ã—</button>
      <h3 id="myModalLabel"><i class="icon-comment-alt"></i> Add a Note</h3>
    </div>
    <div class="modal-body">
      <div class="control-group">
        <label for="comment">Note</label>
        <textarea id="comment" name="comment"></textarea>
      </div>
      <div class="control-group">
        <label><input type="checkbox" id="apply_to_all" name="apply_to_all" value="1"> Apply this note to all records in the current table view</label>
      </div>
      <div class="control-group">
        <label><input type="checkbox" id="send_email" name="send_email" value="1"> Email this note to the primary laboratory contact</label>
      </div>
    </div>
    <div class="modal-footer">
      <input type="hidden" name="all_protein_ids" id="all_protein_ids" value=""/>
      <input type="hidden" name="this_protein_id" id="this_protein_id" value=""/>
      <input type="submit" name="submit" value="Submit" id="note_submit_button" class="btn btn-small btn-primary" />
      <button class="btn btn-small" data-dismiss="modal" aria-hidden="true">Cancel</button>
    </div>
</div>

<!-- View Notes modal -->
<div id="view_notes_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="viewNotesModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">Ã—</button>
      <h3 id="myModalLabel"><i class="icon-list"></i> Notes</h3>
    </div>
    <div class="modal-body">
      <div class="control-group">
        <div id="notes_container">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-small" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<!-- Email CSV modal -->
<div id="email_csv_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="emailCsvModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">Ã—</button>
      <h3 id="myModalLabel"><i class="icon-envelope"></i> Email CSV of All Notes</h3>
    </div>
    <div class="modal-body">
      <div class="control-group">
        <div id="email_csv_container">
          <p id="email_csv_instructions"></p>
          <div class="control-group">
            <label for="email_csv_comment">Add a Message <span class="muted">(optional)</span></label>
            <textarea id="email_csv_comment" name="email_csv_comment"></textarea>
          </div>
          <div class="control-group">
            <label><input type="checkbox" id="email_csv_comment_to_lab" name="email_csv_comment_to_lab" value="1" required="true"> Email a CSV link to the primary laboratory contact (above)</label>
          </div>
          <div class="control-group">
            <label><input type="checkbox" id="email_csv_comment_to_self" name="email_csv_comment_to_self" value="1" required="true"> Email a CSV link to yourself</label>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <input type="submit" name="submit" value="Submit" id="email_csv_submit_button" class="btn btn-small btn-primary" />
      <button id="cancel_close_button" class="btn btn-small" data-dismiss="modal" aria-hidden="true">Cancel</button>
    </div>
</div>

{% endblock %}
{% block js_bottom %}
  {{ parent() }}
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-SWPG/js/datatables_bootstrap.js"></script>
  <script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-SWPG/js/context_menu.js"></script>
  <script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-1.9.0/extras/ColVis-1.0.8/media/js/ColVis.js"></script>
  <script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-SWPG/js/fnGetTds.js"></script>
  <script type="text/javascript" src="/{{ core_type }}/javascripts/DataTables-SWPG/js/jquery.dataTables.rowGrouping.js"></script>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-tour/0.7.3/js/bootstrap-tour.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {

      var datatables_data = JSON.parse('{{ datatables|json_encode|raw }}');

      $.each(datatables_data, function(index, single_datatable){
        var sortable_fields = single_datatable.fields;
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

        $('#browse_table th .filter_value').bind('keyup change', function(event){
          page_datatable.fnDraw();
        });

        $('.filter_image').each(function() {
          var filter_options = [
          //{text: '> greater than', value: 'gt'}
          //,{text: '>= (greater than OR equal to)', value: 'gt_or_eq'}
          //,{text: '< less than', value: 'lt'}
          //,{text: '<= (less than OR equal to)', value: 'lt_or_eq'}
           {text: '= (equal to)', value: 'equals'}
          ,{text: 'Contains', value: 'contains'}
          ,{text: 'Does not contain', value: 'not_contain'}
          ,{text: 'Starts with', value: 'start_with'}
          ,{text: 'Ends With', value: 'end_with'}
          ];
          var column_data_type = $(this).data("data_type");
          switch(column_data_type) {
            case "text":
              filter_options = [
              {text: '= (equal to)', value: 'equals'}
              ,{text: 'Contains', value: 'contains'}
              ,{text: 'Does not contain', value: 'not_contain'}
              ,{text: 'Starts with', value: 'start_with'}
              ,{text: 'Ends With', value: 'end_with'}
              ];
            break;
          }

          $(this).dynamicContextMenu({
              id: this.id
              ,style:{
                containerCssClass: "DynamicContextMenuContainer",
                subLevelHolderItemCssClass: "DynamicContextMenuSubLevelHolderItem",
                itemIconCssClass: "DynamicContextMenuItemIcon"
              }
              ,items: filter_options
              ,openMode: "click"
              ,onItemClick: function (sender, item) {
                $('#'+sender._id+'_filter_value').attr('value', item.value);
                page_datatable.fnDraw();
              }
              ,waitTime: 1
          });
        });

        var page_datatable = $('#' + single_datatable.dom_table_id).dataTable({

          // Groups genes begins
          "fnDrawCallback": function ( oSettings ) {
              var trs = $("#" + single_datatable.dom_table_id + " tbody tr");
              $.each(trs, function(index, tr){
                var td = $(tr).find('td.group');
                if( td.length ) {
                  $(td).addClass('no_row_click');
                }
              });
          }
          // Groups genes ends

          // Remember the user's settings
          ,"bStateSave": true
          //show processing throbber
          ,"bProcessing": true
          //move the info to the top instead of the bottom
          //,"sDom":'C<"clear"><"top"flip>rt<"bottom"p<"clear">>'
          ,"sDom": "<'row'C><<'span4'l><'span4'i><'span4'f>><<'span3 datatables_bulk_actions'><'span3'r><'span5 pull-right'p>>t<'row'<'span6 pull-right'p>>"
          //all data management will be done on the server side
          ,"bServerSide": true
          //path to the file that is going to handle the queries
          ,"sAjaxSource": "{{ path_to_this_module }}/datatables_browse_assays_manage"
          //method type
          ,"sServerMethod": "POST"
          //match the html table columns with the fields returned from the query
          ,"aoColumns": total_columns
          ,"aLengthMenu": [[10, 25, 50, 100, 1000000], [10, 25, 50, 100, "All"]]
          //default length
          ,"iDisplayLength": 50
          //default sort
          ,"aaSorting":[[1,"asc"]]
          //needed for bootstrap
          ,"sWrapper": "dataTables_wrapper form-inline"
          ,"sPaginationType":"bootstrap"
          ,"bAutoWidth": false
          //set some widths
          ,"aoColumnDefs":[
            {"sWidth":"50px","aTargets":[1]}
            ,{"bSortable":false,"aTargets":[0]}
            ,{"bVisible":false,"aTargets":hidden_columns}
          ]
          ,"fnCookieCallback": function (sName, oData, sExpires, sPath) {
            // Customise oData or sName or whatever else here
            sNameNew = sName+'new_';
            return sNameNew + "="+JSON.stringify(oData)+"; expires=" + sExpires +"; path=" + sPath;
          }
          ,"fnServerParams": function ( aoData ) {

              var filter_array = $.map($("#" + single_datatable.dom_table_id + " th"), function(e, i){
                  var single_filter = {};
                  single_filter.column = $(e).find('div.filter_image').attr('id');
                  single_filter.value = $(e).find('.filter_value').val();
                  single_filter.comparison = $(e).find('input.comparison_value').val();
                  return single_filter;
              });
              var newObj = {name: "column_filter", value:JSON.stringify(filter_array)};
              aoData.push(newObj);

              var sidebar_filter = {};

              // Import date
              var import_set_filter = [];
              var lab_id = $("#import_set").attr('data-lab-id');
              if( $("#import_set").val() != 0 ) {
                // Returns a date stamped set of records for a lab
                import_set_filter.push( $("#import_set").val() );
                sidebar_filter['import_set_filter'] = import_set_filter;
              } else {
                // Returns all records for a lab
                sidebar_filter['import_set_filter_lab_id'] = lab_id;
              }
              // Status (approved / disapproved / pending / display all)
              if( $("#status_filter").val() != '' ) {
                var status_filter = [];
                status_filter.push( $("#status_filter").val() );
                sidebar_filter['status_filter'] = status_filter;
              };

              var sidebar_filter_obj = {name: "sidebar_filter", value:JSON.stringify(sidebar_filter)};
              aoData.push(sidebar_filter_obj);

          }
          ,"fnRowCallback":function(nRow, aData, iDisplayIndex){

            // Highlight <td>s accordingly
            var table_datas = $(nRow).find('td');

            $(table_datas).each(function(index, single) {

              var this_record_id = $(this).parent().prop('id');
              var this_cptac_id = aData['cptac_id'];

              if(aData['approval_status'] == 0) {
                $(this).next('td:nth-child(2)').append('<p><i class="icon-eye-open"></i> <a href="/'+this_cptac_id+'" title="View on the portal" target="_blank">View on the Portal</a></p>');
                $(this).addClass('gray-background');
              }
              if(aData['approval_status'] == 1) {
                $(this).next('td:nth-child(2)').append('<p><i class="icon-eye-open"></i> <a href="/'+this_cptac_id+'" title="View on the portal" target="_blank">View on the Portal</a></p>');
                $(this).addClass('green-background');
                $(this).next('td:nth-child(2)').append('<div class="alert alert-warning" style="margin-top:20px;">Public</div>');
              }
              if(aData['approval_status'] == 2) {
                $(this).next('td:nth-child(2)').append('<p><i class="icon-eye-open"></i> <a href="/'+this_cptac_id+'" title="View on the portal" target="_blank">View on the Portal</a></p>');
                $(this).next('td:nth-child(2)').append('<div class="alert alert-warning" style="margin-top:20px;">Awaiting Approval</div>');
              }
              if(aData['approval_status'] == 4) {
                $(this).next('td:nth-child(2)').append('<p><i class="icon-eye-open"></i> <a href="/'+this_cptac_id+'" title="View on the portal" target="_blank">View on the Portal</a></p>');
                $(this).addClass('green-background');
                $(this).next('td:nth-child(2)').append('<div class="alert alert-warning" style="margin-top:20px;">Approved</div>');
              }
            });

            // Create Approve and Disapprove checkboxes
            $(nRow).find('td:eq(0)').html(
              "<div class='checkbox-input-wrapper'><i class='icon-thumbs-up'></i><label><input type='checkbox' name='approve_checkbox' class='approve_checkbox' value='" + aData['manage'] + "' /> Approve</label></div><div class='checkbox-input-wrapper'><i class='icon-thumbs-down'></i><label><input type='checkbox' name='disapprove_checkbox' class='disapprove_checkbox' value='" + aData['manage'] + "' /> Disapprove</label></div>"
            )
            .addClass("manage_column");

            // Create "Add a Note" buttons
            var add_note_container = $('<div class="manage-links" />');
            var add_note_icon = $("<i/>").addClass("icon-plus");
            var add_note_button = $("<a/>")
              .addClass("note-link")
              .attr("href","#notes_modal")
              .attr("title","Add a Note")
              .attr("data-toggle","modal")
              .attr("data-id",$(nRow).find('td:eq(0)').parent().attr('id'))
              .text("Add a Note");
            $(add_note_container).append(add_note_icon);
            $(add_note_container).append(add_note_button);
            $(nRow).find('td:eq(0)').append(add_note_container);

            // Create "View Notes" buttons
            var view_note_container = $('<div class="manage-links" />');
            var view_note_icon = $("<i/>").addClass("icon-list");
            var view_note_button = $("<a/>")
              .addClass("note-link view-note")
              .attr("href","#view_notes_modal")
              .attr("title","View Notes")
              .attr("data-toggle","modal")
              .text("View Notes")
              .on('click', function() {
                // Get all notes for this record
                var protein_id = $(this).parent().parent().parent().attr('id');
                jQuery.ajax({
                  type:"POST"
                  ,dateType:"json"
                  ,url: "{{ path_to_this_module }}/get_notes"
                  ,data: ( {protein_id: protein_id} )
                  ,success: function(ajax_return){
                    // Clear any data from previous population
                    $('#notes_container ul').remove();
                    $('#notes_container div').remove();

                    if( JSON.parse(ajax_return).length > 0 ) {
                      // Loop through each result and create an unordered list
                      $( JSON.parse(ajax_return) ).each(function(index, single) {
                        var emailed_text = (single.has_been_emailed == 1) ? 'yes' : 'no';
                        var email_link_text = (single.has_been_emailed == 1) ? 'resend email' : 'send email';
                        var notes_div_ul = $('<ul />').attr('id',single.approval_moderation_notes_id);
                        var created_date = $('<li><strong>Posted on:</strong> '+single.note_created_date+'</li>');
                        var created_by_data = $('<li><strong>By:</strong> '+single.given_name+' '+single.sn+'</li>');
                        var emailed_data = $('<li><strong>Emailed to primary lab contact:</strong> '+emailed_text+'</li>');
                        var email_button_link = $('<li data-protein-id="'+protein_id+'"><i class="icon-envelope"></i> <a href="javascript:void(0);" class="email-note-link">'+email_link_text+'</a> | <i class="icon-remove"></i> <a href="javascript:void(0);" class="delete-note-link">delete this note</a></li>');
                        var note_content_list_item = $('<li class="note-label"><strong>Note</strong><br />'+nl2br(single.note_content, false)+'</li>');
                        $(notes_div_ul).append(created_date);
                        $(notes_div_ul).append(created_by_data);
                        $(notes_div_ul).append(emailed_data);
                        $(notes_div_ul).append(email_button_link);
                        $(notes_div_ul).append(note_content_list_item);
                        $('#notes_container').append(notes_div_ul);
                      });
                    } else {
                      $('#notes_container').append('<div class="alert fade in">No notes submitted for this record.</div>');
                    }
                  }
                });
              });
            $(view_note_container).append(view_note_icon);
            $(view_note_container).append(view_note_button);
            $(nRow).find('td:eq(0)').append(view_note_container);

            // Add totals to "View Notes" links
            var protein_id = aData['DT_RowId'];
            $.ajax({
              type:"POST"
              ,dataType:"json"
              ,url: "{{ path_to_this_module }}/get_notes_totals"
              ,data: { protein_id: protein_id }
              ,success: function(ajax_return){
                var total = ' (<span class="notes-total">'+ajax_return+'</span>)';
                $('tr#'+protein_id+' .manage_column').find('.view-note').append(total)
              }
            });

          }
          ,"oColVis":{
            "fnLabel":function(index, title, th){
              return $(th).find(".th_header_text").text();
            }
            ,"aiExclude" : exclude_columns_for_hiding
            ,"fnStateChange" : function(iColumn, bVisible){
              var previous_values = $.cookie("browse_table_column_cookie");
              if(typeof previous_values === "undefined" || previous_values === null){
                previous_values = {};
              }else{
                previous_values = JSON.parse(previous_values);
              }
              previous_values[iColumn] = bVisible;
              $.cookie('browse_table_column_cookie', JSON.stringify(previous_values), { expires: 365 });
            }
          }

        }).rowGrouping({
          iGroupingColumnIndex: 1
          ,bHideGroupingColumn: true
          ,bExpandableGrouping: true
      });

      // Approve checkboxes
      $('#' + single_datatable.dom_table_id + ' tbody').on('click','td div label input.approve_checkbox',function(event) {
        event.stopPropagation();
        var parent_row_id = $(this).parent().parent().parent().parent().attr('id');
        $("#"+parent_row_id+" td div label input.disapprove_checkbox:checked").removeAttr('checked');
        $("#"+parent_row_id+" td").removeClass('gray-background');
        $("#"+parent_row_id+" td").addClass('green-background');
        // If nothing is checked, remove background color classes
        if($("#"+parent_row_id+" td div label input.approve_checkbox:checked").length == 0) {
          $("#"+parent_row_id+" td").removeClass('gray-background');
          $("#"+parent_row_id+" td").removeClass('green-background');
        }
      })

      // Disapprove checkboxes
      $('#' + single_datatable.dom_table_id + ' tbody').on('click','td div label input.disapprove_checkbox',function(event) {
        event.stopPropagation();
        var parent_row_id = $(this).parent().parent().parent().parent().attr('id');
        $("#"+parent_row_id+" td div label input.approve_checkbox:checked").removeAttr('checked');
        $("#"+parent_row_id+" td").removeClass('green-background');
        $("#"+parent_row_id+" td").addClass('gray-background');
        // If nothing is checked, remove background color classes
        if($("#"+parent_row_id+" td div label input.disapprove_checkbox:checked").length == 0) {
          $("#"+parent_row_id+" td").removeClass('gray-background');
          $("#"+parent_row_id+" td").removeClass('green-background');
        }
      });

      // Add a Note - onclick, populate hidden inputs
      $('#' + single_datatable.dom_table_id + ' tbody').on('click','td .note-link',function(event) {
        // Get all of the protein ids
        var all_protein_ids = $('#' + single_datatable.dom_table_id + ' tbody')
          .find('tr.group-item')
          .map(function() {
            return this.id;
          }).get();
        // This protein id
        var this_protein_id = $(this).data('id');
        // Populate the hidden inputs accordingly
        $('#all_protein_ids').attr('value',all_protein_ids);
        $('#this_protein_id').attr('value',this_protein_id);
      });

      // Prevent propagation on labels 
      $('#' + single_datatable.dom_table_id + ' tbody').on('click','label',function(event) {
        event.stopPropagation();
      });

    });
    
    // Mark All as Approved button
    var checkall_approve_button = $("<div id='approve_container' class='check-all-wrapper'><a href='javascript:void(0);' class='btn btn-small checkall_approve'> Mark All as Approved</a></div>")
      .on("click",function(){
      // Remove checked (uncheck) from checkall_disapprove
      $(".checkall_disapprove").removeAttr('checked');
      // Remove checked from all disapprove_checkbox elements
      $("input.disapprove_checkbox").removeAttr('checked');

      if($(".checkall_approve").prop('checked', true)) {
        $("input.approve_checkbox").prop('checked', true);
        $("input.disapprove_checkbox").parent().parent().parent().parent().find('td').removeClass('gray-background');
        $("input.approve_checkbox").parent().parent().parent().parent().find('td').addClass('green-background');
      } else {
        $("input.disapprove_checkbox").parent().parent().parent().parent().find('td').removeClass('gray-background');
        $("input.disapprove_checkbox").parent().parent().parent().parent().find('td').removeClass('green-background');
      }
    });

    // Mark All as Disapproved button
    var checkall_disapprove_button = $("<div id='disapprove_container' class='check-all-wrapper'><a href='javascript:void(0);' class='btn btn-small checkall_approve'> Mark All as Disapproved</a></div>")
      .on("click",function(){
      // Remove checked (uncheck) from checkall_approve
      $(".checkall_approve").removeAttr('checked');
      // Remove checked from all approve_checkbox elements
      $("input.approve_checkbox").removeAttr('checked');

      if($(".checkall_disapprove").prop('checked', true)) {
        $("input.disapprove_checkbox").prop('checked', true);
        $("input.approve_checkbox").parent().parent().parent().parent().find('td').removeClass('green-background');
        $("input.disapprove_checkbox").parent().parent().parent().parent().find('td').addClass('gray-background');
      } else {
        $("input.approve_checkbox").parent().parent().parent().parent().find('td').removeClass('green-background');
        $("input.approve_checkbox").parent().parent().parent().parent().find('td').removeClass('gray-background');
      }
    });

    // Start Over button
    var uncheck_all_button = $("<div class='check-all-wrapper'><a href='javascript:void(0);' class='btn btn-small uncheck_all'> Start Over (reloads page)</a></div>")
      .on("click",function(){
        location.reload();
    });

    // Email CSV of Notes button
    var email_csv_button = $("<div class='check-all-wrapper'><a href='#email_csv_modal' data-toggle='modal' class='btn btn-small email_csv'> Email a CSV of All Notes</a></div>");

    // Submit button
    var submit_button = $('<div><button id="submit_status" class="btn btn-small btn-inverse" title="Submit status on the selected item(s)">Submit</button></div>')
      .addClass("check-all-wrapper")
      .on("click",function(){
        var approved_ids = new Array;
        var disapproved_ids = new Array;
        $('#browse_table [name="approve_checkbox"]:checked').each(function(){
          approved_ids.push($(this).val());
        });
        $('#browse_table [name="disapprove_checkbox"]:checked').each(function(){
          disapproved_ids.push($(this).val());
        });
        if( (approved_ids.length > 0) || (disapproved_ids.length > 0) ) {
          var confirmation = confirm("Are you sure you want to submit the selected item(s)?");
          if(confirmation){
            jQuery.ajax({
              type:"POST"
              ,dateType:"json"
              ,url: "{{ path_to_this_module }}/approval_process"
              ,data: ( {ids_approved: JSON.stringify(approved_ids), ids_disapproved: JSON.stringify(disapproved_ids) })
              ,success: function(ajax_return){
                // page_datatable.fnDraw();
                $('#success-message').remove();
                $('#browse_table_wrapper .row .ColVis').append('<div id="success-message">Records successfully updated</div>');
                setTimeout(function(){ $('#success-message').fadeOut(); }, 2000);
                // Remove "Awaiting Approval" labels
                $('#browse_table [name="approve_checkbox"]:checked').each(function(){
                  var next_td = $(this).parent().parent().parent().next('td');
                  $(next_td).find('div').remove();
                });
                $('#browse_table [name="disapprove_checkbox"]:checked').each(function(){
                  var next_td = $(this).parent().parent().parent().next('td');
                  $(next_td).find('div').remove();
                });
              }
            });
          }
        } else {
          alert("Please select at least one record.")
        }
    });

    // Create a new wrapper div for the buttons
    var additional_bulk_actions = $("<div>").addClass("additional_bulk_actions").addClass("span12");
    // Add buttons to the new wrapper div
    $(additional_bulk_actions).append(checkall_approve_button);
    $(additional_bulk_actions).append(checkall_disapprove_button);
    $(additional_bulk_actions).append(uncheck_all_button);
    $(additional_bulk_actions).append(email_csv_button);
    $(additional_bulk_actions).append(submit_button);
    // Add the new wrapper div, populated with buttons, to the DOM
    $(".datatables_bulk_actions").after(additional_bulk_actions);

    // Notes - on submit
    $('.modal .modal-footer').on('click', '#note_submit_button', function() {
      // Set defaults
      var comment_text = 0;
      var apply_to_all = 0;
      var send_email = 0;
      // Get all of the protein ids for records displayed in the table
      var all_protein_ids = $("#all_protein_ids").val();
      // Get this protein id
      var this_protein_id = $("#this_protein_id").val();
      // Make sure there's comment data
      if($("#comment").val()) {
        comment_text = $("#comment").val();
      }
      // Check if "Apply to all" is checked
      if($('#apply_to_all:checked').length > 0) {
        apply_to_all = $('#apply_to_all:checked').length;
      }
      // Check if "Send Email" is checked
      if($('#send_email:checked').length > 0) {
        send_email = $('#send_email:checked').length;
      }
      // The lab id
      var laboratory_id = $('#labs').val();
      // Import set id
      var import_set_id = $('#import_set').val();
      // If note text has been entered, enter the note into the database
      if(comment_text.length > 0) {
        jQuery.ajax({
          type: "POST"
          ,dateType: "json"
          ,url: "{{ path_to_this_module }}/add_approval_process_note"
          ,data: ({ 
            this_protein_id: this_protein_id
            ,all_protein_ids: all_protein_ids
            ,import_set_id: import_set_id
            ,comment_text: comment_text
            ,apply_to_all: apply_to_all
            ,laboratory_id: laboratory_id
            ,send_email: send_email
          })
          ,success: function(data) {

            // page_datatable.fnDraw();
            if(data) {
              var d = JSON.parse(data);
              var sent_email_message = (d.email_sent) ? ' and an email has been sent to the primary laboratory contact (you have been BCC-ed, as well)' : '';
              var notes_success_message = $('<div />')
                .addClass('success')
                .attr('class','alert alert-success fade in')
                .text('Your note has been successfuly entered into the database'+sent_email_message+'.');
              $('#notes_modal .modal-body .control-group, #notes_modal .modal-footer input').hide();
              $('#notes_modal .modal-body').append( notes_success_message );
              $('#notes_modal .modal-footer button').text('Close');
              var current_total = $('tr#'+this_protein_id+' .manage_column').find('.notes-total').text();
              var new_total = parseInt(current_total) + 1;
              $('tr#'+this_protein_id+' .manage_column').find('.notes-total').text(new_total);
            } else {
              var notes_error_message = $('<span />')
                .attr('style','margin-top:10px;color:red;')
                .html('Something went wrong&hellip;');
              $('#notes_modal .modal-body').prepend( notes_error_message );
            }
          }
          
        });
      }
      
    });

    // Clean up the modal before adding notes
    $('#browse_table_wrapper').on('click', '.note-link', function() {
      // Clear any data from previous population
      $('#notes_modal .modal-body').find('div.alert').remove();
      $('#notes_modal .modal-body .control-group textarea').val('');
      $('#notes_modal .modal-body .control-group input').removeAttr('checked');
      // Show the form fields
      $('#notes_modal .modal-body .control-group, #notes_modal .modal-footer input').show();
    });

    // Email existing note
    $('#notes_container').on('click', '.email-note-link', function(){

      var this_button = $(this);
      var note_id = $(this).parent().parent().attr('id');
      var laboratory_id = $('#labs').val();

      jQuery.ajax({
        type:"POST"
        ,dateType:"json"
        ,url: "{{ path_to_this_module }}/send_email"
        ,data: ( { note_id: note_id, laboratory_id: laboratory_id })
        ,success: function(ajax_return){

          if(ajax_return) {
            // Display a message that the note has been emailed
            var message = $('<div />').addClass('email-successful-message').text('Email sent successfully');
            $('#view_notes_modal .modal-header button').after( message );
            $(this_button).text('resend email');
            // Remove the message after 1.5 seconds
            setTimeout(function(){
              $(message).fadeOut();
            }, 1500);
          }

        }
      });

    });

    // Delete existing note
    $('#notes_container').on('click', '.delete-note-link', function(){

      var confirmation = confirm("Are you sure you want to delete this note?");
      
      if(confirmation){

        var this_button = $(this);
        var note_id = $(this).parent().parent().attr('id');
        var protein_id = $(this).parent().data('protein-id');

        $.ajax({
          type: "POST"
          ,dateType: "json"
          ,url: "{{ path_to_this_module }}/delete_note"
          ,data: ( { note_id: note_id })
          ,success: function(ajax_return){

            if(ajax_return) {
              // Hide the deleted note
              $(this_button).parent().parent().slideUp();
              // Display a message that the note has been deleted
              var message = $('<div />').addClass('notes-success-message').text('Note deleted successfully');
              $('#view_notes_modal .modal-header button').after( message );
              // Subtract from the notes total in the browse table
              var current_total = $('tr#'+protein_id+' .manage_column').find('.notes-total').text();
              var new_total = parseInt(current_total) - 1;
              $('tr#'+protein_id+' .manage_column').find('.notes-total').text(new_total);
              // Remove the message after 1.5 seconds
              setTimeout(function(){
                $(message).fadeOut();
              }, 1500);
            }

          }
        });
      }

    });

    // Show the table after the import set is chosen from the "Choose an Import Set" menu
    $('#labs').on('change', function() {

      var this_lab_id = $(this).val();

      jQuery.ajax({
        type:"POST"
        ,dateType:"json"
        ,url: "{{ path_to_this_module }}/get_import_logs_by_lab_id"
        ,data: ( {laboratory_id: this_lab_id })
        ,success: function(ajax_return){

          if( JSON.parse(ajax_return).length > 0 ) {
            // Clear the menu
            $('#import_set option').remove();
            $('#import_set').removeAttr('data-lab-id');
            $('#import_set').attr('data-lab-id',this_lab_id);
            $('#import_set').append('<option value="">Select</option>');
            // Loop through each result and create an unordered list
            $( JSON.parse(ajax_return) ).each(function(index, single) {
              var option = $('<option value="'+single.import_log_id+'">'+ single.import_date + ' ' + single.folder +'</option>');
              $('#import_set').append(option);
            });
            // Add lab info to the email csv modal
            // $('#email_csv_instructions').text('Email '++' ('++') a CSV of all disapproved assay notes.');
          } else {
            // maybe?
          }

        }
      });

      $('#import_set_label, #import_set').show();
    });

    // Populate the Email CSV Modal with Laboratory-based information
    $('.email_csv').on('click', function() {

      var this_lab_id = $('#labs').val();
      var import_set_id = $('#import_set').val();

      jQuery.ajax({
        type: "POST"
        ,dateType: "json"
        ,url: "{{ path_to_this_module }}/get_laboratory_by_id"
        ,data: ( {laboratory_id: this_lab_id } )
        ,success: function(ajax_return){

          if(ajax_return) {
            var returned_data = JSON.parse(ajax_return);
            $('#email_csv_container p').empty();
            $('#email_sent_message').remove();
            $('#email_csv_container p').append('Send an email with a link to a CSV of all assay notes to '+returned_data.primary_contact_name+' ('+returned_data.primary_contact_email_address+'), yourself, or both.');
            $('#email_csv_comment').val('');
            $('#email_csv_container').show();
            $('#email_csv_submit_button').show();
            $('#cancel_close_button').text('Cancel');
          }

        }
      });
    });

    // Email CSV submit button action
    $('#email_csv_submit_button').on('click', function() {

      var this_lab_id = $('#labs').val();
      var import_set_id = $('#import_set').val();
      var message = $('#email_csv_comment').val();
      var email_csv_comment_to_lab = $('#email_csv_comment_to_lab:checked').length;
      var email_csv_comment_to_self = $('#email_csv_comment_to_self:checked').length;

      jQuery.ajax({
        type: "POST"
        ,dataType: "json"
        ,url: "{{ path_to_this_module }}/email_csv"
        ,data: ( { laboratory_id: this_lab_id, import_set_id: import_set_id, message: message, email_csv_comment_to_lab: email_csv_comment_to_lab, email_csv_comment_to_self: email_csv_comment_to_self } )
        ,success: function(ajax_return){

          if(ajax_return == 'sent') {
            $('#email_sent_message').remove();
            $('#email_csv_container').hide();
            $('#email_csv_submit_button').hide();
            $('#cancel_close_button').text('Close');
            $('#email_csv_container').parent().before('<div id="email_sent_message" class="alert alert-success">Email sent successfully.</div>');
          } else {
            $('#email_csv_container').parent().before('<div id="email_sent_message" class="alert alert-error">Emails not sent. Make sure at least one checkbox is selected.</div>');
          }

        }
      });
    });

    // Always hide the browse table and reset the "Toggle Approved/Disapproved" menu on change of the "Choose a Laboratory" menu.
    $('#labs').on('change', function() {
      $('#additional_filters, #browse_table_wrapper').hide();
      $('#status_filter').val('');
      $('#browse_table').dataTable().fnDraw();
    });

    // MENU: Import Date
    $('#import_set').on('change', function() {

      $('#browse_table').dataTable().fnDraw();

      // Since DataTables takes a split second to reload, set a timeout to prevent the old data from displaying
      setTimeout(function(){
        if( $('#import_set').val().length ) {
          $('#additional_filters, #browse_table_wrapper').show();
          $('#notices').hide();
        } else {
          $('#additional_filters, #browse_table_wrapper').hide();
        }
      }, 750);

    });

    // MENU: Approved / Disapproved / Display All
    $('#status_filter').on('change', function() {
      $('#browse_table').dataTable().fnDraw();
    });

    // Tooltips
    $("button").tooltip();

    // Remove the onclick on table headers
    $('#browse_table thead tr th').unbind('click');

    // Modify the width of the pagination container
    $('#browse_table_wrapper').find('div.span5').removeClass('span5').addClass('span12');
    $('#browse_table_wrapper').find('div.span3').remove();

    // Add the tour link / button
    $('.page-header').after('<div id="tour_button_container"><button class="btn btn-small">Need Help? Take a tour...</button></div>');


    /* TOUR STUFF */
    $('#tour_button_container').on('click', 'button', function() {
      // Instance the tour
      var tour = new Tour({
        storage: false
      });

      // console.log($('#browse_table tbody').find('tr:eq(2)'));

      // Add steps
      tour.addSteps([
        {
          element: "#labs",
          title: "Choose a Laboratory",
          content: "Choose a laboratory from the menu. If the selected laboratory is changed, the browse table will disappear until a date is chosen from the adjacent menu.",
          placement: "bottom"
        },
        {
          element: "#import_set",
          title: "Choose a Date",
          content: "Choose the date of the import.",
          placement: "bottom"
        },
        {
          element: "#status_filter",
          title: "Filter By Approval State",
          content: "Narrow results by approval state.",
          placement: "bottom"
        },
        {
          element: "#approve_container",
          title: "Bulk Actions",
          content: "Mark all records in the current view as approved, disapproved, or start over.",
          placement: "bottom"
        },
        {
          element: ".email_csv",
          title: "Email CSV",
          content: "Send an email to the primary laboratory contact, with a link to a CSV of all notes.",
          placement: "bottom" // left, right, top, bottom
        },
        {
          element: "#submit_status",
          title: "Submit Changes",
          content: "Submit all changes to the database.",
          placement: "bottom" // left, right, top, bottom
        },
        {
          element: $('#browse_table tbody').find('tr:eq(1)'),
          title: "Approve/Disapprove Assay",
          content: "Approve or disapprove a single assay.",
          placement: "left" // left, right, top, bottom
        },
        {
          element: $('#browse_table tbody').find('tr:eq(1)'),
          title: "Add Note / View Notes",
          content: "Add a note to one record, add a note to all records in the current view, or view all notes applied to this record. (Helpful Tip: Change the total records showing if you wish to apply a note to all of the records in an import set.)",
          placement: "left" // left, right, top, bottom
        }

      ]);
      // Initialize the tour
      tour.init();
      // Start the tour
      tour.start();
    });

  });

  function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
  }

  </script>
{% endblock %}
