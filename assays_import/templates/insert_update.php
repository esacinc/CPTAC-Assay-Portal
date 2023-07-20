{% extends layout_template_name %}
{% block styles_head %}
  {{ parent() }}
  <link href="/assays/library/css/jquery.tagit.css" rel="stylesheet" type="text/css" />
{% endblock %}
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
  <form method="POST" enctype="multipart/form-data" class="form-horizontal">

    <h4>Primary Investigators</h4>

    <div class="row-fluid">
      <div class="span12">
        <div class="control-group">
          <label class="control-label" for="primary_investigators">Enter Full Names (First Last) <i class="icon-question-sign" title="Hit the return/enter key to enter each Primary Investigator"></i></label>
          <div class="controls">
            <input id="select_primary_investigators" name="primary_investigators">
          </div>
        </div>
      </div>
    </div>


    <h4>Assay Details</h4>

    <p class="text-info"><i class="icon-info-sign"></i> Laboratories are defined by user association</p>

    <div class="row-fluid">
      <div class="span12">
        <div class="control-group">
          <label class="control-label" for="instrument"><span class="color-red">*</span>Instrument</label>
          <div class="controls">
            <input name="instrument" id="instrument" type="text" value="{{ data.instrument|e }}" required="true" >
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="matrix"><span class="color-red">*</span>Matrix</label>
          <div class="controls">
            <input name="matrix" id="matrix" type="text" value="{{ data.matrix|e }}" required="true">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="matrix_amount_and_units"><span class="color-red">*</span>Matrix Amount (include units)</label>
          <div class="controls">
            <input name="matrix_amount_and_units" id="matrix_amount_and_units" type="text" value="{{ data.matrix_amount_and_units|e }}" required="true">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="quantification_units"><span class="color-red">*</span>Quantification Units (e.g. fmol/ug)</label>
          <div class="controls">
            <input name="quantification_units" id="quantification_units" type="text" value="{{ data.quantification_units|e }}" required="true">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="internal_standard"><span class="color-red">*</span>Internal Standard</label>
          <div class="controls">
            <input name="internal_standard" id="internal_standard" type="text" value="{{ data.internal_standard|e }}" required="true">
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="peptide_standard_purity"><span class="color-red">*</span>Peptide Standard Purity</label>
          
          <div class="controls">
            <select name="peptide_standard_purity_types_id">
              <option>Select</option>
              {% for value in data.peptide_standard_purity_options %}
                <option value="{{ value.peptide_standard_purity_types_id }}" {{ (value.peptide_standard_purity_types_id == data.peptide_standard_purity_types_id) ? 'selected="selected"' : '' }}>{{ value.type }}</option>
              {% endfor %}
            </select> 
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="protein_species_label"><span class="color-red">*</span>Target Organism</label>
        <div class="controls">
          <input name="protein_species_label" id="protein_species_label" type="text" value="{{ data.protein_species_label|e }}">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="data_type"><span class="color-red">*</span>Data Type<br>
          <span class="muted">(e.g. MRM, PRM, SRM)</span>
        </label>
        <div class="controls">
          <input name="data_type" id="data_type" type="text" value="{{ data.data_type|e }}">
        </div>
      </div>
    </div>

    <hr>

    <h4>Assay Parameters</h4>

    <div class="row-fluid">
      <div class="span12">

        <div id="direct_mrm" class="direct_mrm">
          <div class="control-group">
            <label class="control-label" for="lc"><span class="color-red">*</span>LC</label>
            <div class="controls">
              <input name="lc" id="lc" type="text" value="{{ data.lc|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="column_packing"><span class="color-red">*</span>Column Packing</label>
            <div class="controls">
              <input name="column_packing" id="column_packing" type="text" value="{{ data.column_packing|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="column_dimensions"><span class="color-red">*</span>Column Dimensions</label>
            <div class="controls">
              <input name="column_dimensions" id="column_dimensions" type="text" value="{{ data.column_dimensions|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="column_temperature"><span class="color-red">*</span>Column Temperature</label>
            <div class="controls">
              <input name="column_temperature" id="column_temperature" type="text" value="{{ data.column_temperature|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="flow_rate"><span class="color-red">*</span>Flow Rate</label>
            <div class="controls">
              <input name="flow_rate" id="flow_rate" type="text" value="{{ data.flow_rate|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="mobile_phase_a"><span class="color-red">*</span>Mobile Phase A</label>
            <div class="controls">
              <input name="mobile_phase_a" id="mobile_phase_a" type="text" value="{{ data.mobile_phase_a|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="mobile_phase_b"><span class="color-red">*</span>Mobile Phase B</label>
            <div class="controls">
              <input name="mobile_phase_b" id="mobile_phase_b" type="text" value="{{ data.mobile_phase_b|e }}" required="true">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="gradient_description"><span class="color-red">*</span>Gradient Description<br>
              <span class="muted">Example:<br>
                Time, %A, %B<br>
                0, 95, 5<br>
                1, 95, 5<br>
                15, 60, 40</span>
            </label>
            <div class="controls">
              <textarea name="gradient_description" id="gradient_description" required="true">{{ data.gradient_description|e }}</textarea>
            </div>
          </div>
        </div>

        <hr>

        <h4>Assay Type</h4>
        <p class="muted">If not selected, assay will be set to 'direct'. Otherwise, select accordingly.</p>

        <div class="control-group">
          <label for="assay_types_id" style="display: none;">Assay Type</label>
          <select name="assay_types_id" id="assay_type">
            <option value="3">Select</option>
            {% for single_assay_type in data.assay_types %}
              {% set selected = '' %}
              {% if data.assay_types_id == single_assay_type.assay_types_id %}
                {% set selected = ' selected' %}
              {% endif %}
              <option value="{{ single_assay_type.assay_types_id }}"{{ selected }}>{{ single_assay_type.label }}</option>
            {% endfor %}
          </select>
        </div>

        <div id="assay_types_controls">

          <div id="assay_type_1" class="enrichment_mrm" style="display:{{ data.assay_types_id == 1 ? 'block' : 'none' }};">
            <div class="control-group">
              <label class="control-label" for="enrichment_method"><span class="color-red">*</span>Enrichment Method
                <i class="icon-question-sign" title="Example: peptide immunoaffinity, protein immunoprecipitation, IMAC, depletion"></i>
              </label>
              <div class="controls">
                <input name="enrichment_method" id="enrichment_method" type="text" value="{{ data.enrichment_method|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="affinity_reagent_type">Affinity Reagent Type
                <i class="icon-question-sign" title="Example: monoclonal Ab"></i>
              </label>
              <div class="controls">
                <input name="affinity_reagent_type" id="affinity_reagent_type" type="text" value="{{ data.affinity_reagent_type|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="antibody_vendor"><span class="color-red">*</span>Vendor
                <i class="icon-question-sign" title="Example: specific make/model"></i>
              </label>
              <div class="controls">
                <input name="antibody_vendor" id="antibody_vendor" type="text" value="{{ data.antibody_vendor|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="media"><span class="color-red">*</span>Media or Bead Type
                <i class="icon-question-sign" title="Example: make/model of magnetic beads, chromatography packing, etc. "></i><br>
                <span class="muted">(include vendor)</span>
              </label>
              <div class="controls">
                <input name="media" id="media" type="text" value="{{ data.media|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="antibody_portal_url">Link to Antibody Portal
                <i class="icon-question-sign" title="Example: identifier of affinity reagent on antibody portal (if applicable)"></i>
              </label>
              <div class="controls">
                <input name="antibody_portal_url" id="antibody_portal_url" type="text" value="{{ data.antibody_portal_url|e }}">
              </div>
            </div>
          </div>

          <div id="assay_type_2" class="fraction_mrm" style="display:{{ data.assay_types_id == 2 ? 'block' : 'none' }};">
            <div class="control-group">
              <label class="control-label" for="fractionation_approach"><span class="color-red">*</span>Fractionation Approach
                <i class="icon-question-sign" title="Example: bRP, SCX, etc."></i>
              </label>
              <div class="controls">
                <input name="fractionation_approach" id="fractionation_approach" type="text" value="{{ data.fractionation_approach|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="column_material"><span class="color-red">*</span>Column Material</label>
              <div class="controls">
                <input name="column_material" id="column_material" type="text" value="{{ data.column_material|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="conditions"><span class="color-red">*</span>Conditions<br>
                <span class="muted">(e.g. Mobile Phases, Column Dimensions, Flow Rates, Gradient)</span></label>
              <div class="controls">
                <input name="conditions" id="conditions" type="text" value="{{ data.conditions|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="number_of_fractions_collected"><span class="color-red">*</span>Number of Fractions Collected</label>
              <div class="controls">
                <input name="number_of_fractions_collected" id="number_of_fractions_collected" type="text" value="{{ data.number_of_fractions_collected|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="number_of_fractions_analyzed"><span class="color-red">*</span>Number of Fractions Analyzed</label>
              <div class="controls">
                <input name="number_of_fractions_analyzed" id="number_of_fractions_analyzed" type="text" value="{{ data.number_of_fractions_analyzed|e }}">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="fraction_combination_strategy"><span class="color-red">*</span>Fraction Combination Strategy
                <i class="icon-question-sign" title="Example: serial, concatenated, etc."></i>
              </label>
              <div class="controls">
                <input name="fraction_combination_strategy" id="fraction_combination_strategy" type="text" value="{{ data.fraction_combination_strategy|e }}">
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>

    <hr>

    <h4>Panorama Directory</h4>

    <div class="row-fluid">
      <div class="span12">
        <div class="control-group">
          <label class="control-label" for="celllysate_path"><span class="color-red">*</span>Directory Name<br>
            <span class="muted">Example (in bold, no slashes):<br>
              https://panoramaweb.org/
              labkey/project/
              CPTAC%20Assay%20Portal/
              FHCRC_Paulovich/
              <strong>CellLysate_5500QTRAP_directMRM</strong>/</span>
          </label>
          <div class="controls">
            <input name="celllysate_path" id="celllysate_path" type="text" value="{{ data.celllysate_path|e }}" required="true" >
          </div>
        </div>
      </div>
    </div>

    {% if data.sop_files %}
      <hr>
      <h4>Existing SOP Files</h4>
      {% for single_sop_file in data.sop_files %}
        <div id="sop_file_div_{{ single_sop_file.sop_files_id }}" class="sop_single_file_container">
          <span class="icon-eye-open"></span> <strong>{{ single_sop_file.label }}:</strong> <a href="{{ path_to_this_module }}/download_file?file_id={{ single_sop_file.sop_files_id }}" title="Download {{ single_sop_file.file_name }}" class="download-file">{{ single_sop_file.file_name }}</a> <span class="icon-trash" style="margin-left:12px;"></span> <a href="javascript:void(0);" data-sop-id="{{ i }}" data-sop-file-id="{{ single_sop_file.sop_files_id }}" class="sop_delete_link" title="Remove {{ single_sop_file.file_name }}">remove</a>
        </div>
      {% endfor %}
    {% endif %}

    <hr>

    <h4>Upload SOP Files</h4>
    <p class="muted">Multiple file uploads are allowed</p>
    <p><a href="/sop-template/" target="_blank">Download the SOP Template</a> <span class="muted">(Word Document)</span>

    <div id="sop_files_container" class="row-fluid">
      <input
        type="file"
        name="files[]"
        id="fileupload"
        class="span6"
        data-url="{{ path_to_this_module }}/process_file_upload"
        multiple
      >
      <div id="uploading_notification_container" class="span6">
        <div class="uploading"><p class="uploading-text">Uploading&hellip;</p>
          <div id="progress" class="progress progress-success">
            <div class="bar" style="width: 0%;"></div>
          </div>
        </div>
      </div>
      {% if data.uploaded_files %}
        {% for single_uploaded_file in data.uploaded_files %}
          <div class="row-fluid file-controls-wrapper">
            <div id="sop_file_type_menu_div_{{ single_uploaded_file.sop_files_id }}" class="sop-file-type-menu span6">
              <div class="sop_types_menu_template">
                <a href="javascript:void(0);" data-sop-file-id="{{ single_uploaded_file.sop_files_id }}" class="delete_file_pre_post_link" title="Remove this SOP file"><span class="icon-trash" style="margin-right:10px;"></span></a>
                <select
                  id="sop_file_type_{{ single_uploaded_file.sop_files_id }}" 
                  name="sop_file_types[]" 
                  class="sop_type_menu" 
                  data-sop-file-id="{{ single_uploaded_file.sop_files_id }}" 
                  required="true">
                  <option value="">Select SOP Category</option>
                  {% for single in data.sop_file_types %}
                    {% if single_uploaded_file.sop_file_type_id == single.sop_file_type_id %}
                      {% set selected = " selected" %}
                    {% else %}
                      {% set selected = "" %}
                    {% endif %}
                    <option value="{{ single.sop_file_type_id }}"{{ selected }}>{{ single.label }}</option>
                  {% endfor %}
                </select>
              </div>
            </div>
            <div class="single-file-wrapper span6">
              <span class="icon-check"></span> 
              <a href="{{ path_to_this_module }}/download_file?file_id={{ single_uploaded_file.sop_files_id }}" 
                class="single-file download-file" 
                title="View/download {{ single_uploaded_file.file_name }}" 
                target="_blank">{{ single_uploaded_file.file_name }}</a>
              <input type="hidden" name="uploaded_files[]" value="{{ single_uploaded_file.sop_files_id }}">
            </div>
          </div>
        {% endfor %}
      {% endif %}
    </div>

    <div id="duplicateSopFileTypeAlert" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="duplicateSopFileType" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="duplicateSopFileType">Category Already Chosen</h3>
      </div>
      <div class="modal-body">
        <p>This category has already been chosen. Please choose a different category.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">OK</button>
      </div>
    </div>

    {% if data.publications %}
      <hr>
      <h4>Existing Publications</h4>
      {% for single_publication in data.publications %}
        <div id="publication_div_{{ single_publication.publications_id }}" class="publication_single_container">
          <p>{{ single_publication.publication_citation | raw }}</p>
          <p>
            <span class="icon-eye-open"></span> 
            <a href="{{ single_publication.publication_url | raw }}" 
              title="View URL"
              target="_blank">View URL</a>
            <span class="icon-trash" style="margin-left:12px;"></span> 
            <a href="javascript:void(0);" 
              data-publication-id="{{ single_publication.publications_id }}"
              class="publication_delete_link" 
              title="Remove this publication">Remove</a>
          </p>
        </div>
      {% endfor %}
    {% endif %}

    <hr>

    <h4>Add Publications</h4>

    <div id="publications_container" class="row-fluid">
      <div id="publication_div" class="publication_container">

      </div>
      <button
        type="button"
        name="add"
        class="btn btn-small publication_button add_publication"
        title="Add a Publication"
        id="add_publication_button"><i class="icon-plus-sign"></i> Add a Publication</button>
    </div>

    <hr>

    <div class="row-fluid">
      <div id="submit_button_container" class="span12">
        <div class="control-group">
          <input name="import_log_id" type="hidden" value="{{ data.import_log_id|e }}">
          <input class="btn btn-primary" type="submit" value="Submit Import Metadata" >
          {% if data.assay_parameters_id %}
            <a href="{{ path_to_this_module }}/execute/?import_log_id={{ data.import_log_id|e }}" class="btn btn">Manage and Execute Imports</a>
          {% endif %}
          <a href="{{ path_to_this_module }}/" class="btn btn-link">Back to Browse Imports</a>
        </div>
      </div>
    </div>

  </form>
</div>
{% endblock %}
{% block js_bottom %}
  {{ parent() }}
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
  <script src="/site/library/js/jQuery-File-Upload/js/vendor/jquery.ui.widget.js"></script>
  <script src="/site/library/js/jQuery-File-Upload/js/jquery.iframe-transport.js"></script>
  <script src="/site/library/js/jQuery-File-Upload/js/jquery.fileupload.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js" charset="utf-8"></script>
  <script src="/assays/library/js/tag-it.js"></script>

  <script id="single_publication_template" type="text/template">
    <div class="single_publication_template">
      <div class="control-group">
        <label class="control-label" for="publication_citation"><span class="color-red">*</span>Publication Citation</label>
        <div class="controls">
          <textarea
            name="publication_citation[]"
            id="publication_citation_<%- publication_id %>"
            required="true"
            class="editor"><%- publication_citation %></textarea>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="publication_url"><span class="color-red">*</span>Publication URL<br><span class="muted">(include http://)</span></label>
        <div class="controls">
          <input
            type="text"
            name="publication_url[]"
            id="publication_url_<%- publication_id %>"
            class="publication-url"
            required="true"
            value="<%- publication_url %>"
          >
        </div>
      </div>
      <button
        type="button"
        name="remove"
        class="btn btn-small remove_publication_block"
        title="Remove Publication"><i class="icon-remove"></i>Remove</button>
    </div>
  </script>

  <script id="sop_types_menu_template" type="text/template">
    <div class="sop_types_menu_template">
      <a href="javascript:void(0);" data-sop-file-id="<%- sop_file_id %>" class="delete_file_pre_post_link" title="Remove this SOP file"><span class="icon-trash" style="margin-right:10px;"></span></a>
      <select id="sop_file_type_<%- sop_file_id %>" name="sop_file_types[]" class="sop_type_menu" data-sop-file-id="<%- sop_file_id %>" required="true">
        <option value="">Select SOP Category</option>
        <% if (typeof(sop_file_types) === 'object') { %>
          <% sop_file_types.forEach(function(single) { %>
            <option value="<%- single.sop_file_type_id %>"><%- single.label %></option>
          <% }); %>
        <% } %>
      </select>
    </div>
  </script>

  <script type="text/javascript">

    $(document).ready(function(){

      /*
       * Primary Investigators
       */

      $("#select_primary_investigators").tagit({
        removeConfirmation: true,
        maxLength: 6,
        allowSpaces: true
        // ,
        // afterTagAdded: function(event, ui) {
        //   $('#browse_table').dataTable().fnDraw();
        // },
        // afterTagRemoved: function(event, ui) {
        //   $('#browse_table').dataTable().fnDraw();
        // }
      });

      /*
       * Publications
       */

      var submitted_publications = JSON.parse('{{ data.submitted_publications|json_encode|raw }}');

      // Add a new publication record block.
      $("form").on("click", "#add_publication_button", function(event){
        add_publication_block();
      });

      // Return submitted publications on failed validations.
      if(submitted_publications) {
        $.each(submitted_publications, function (index, publication_data) {
          add_publication_block(
              publication_data.publication_citation
            , publication_data.publication_url
          );
        });
      }

      // Remove existing publication record block.
      $("form").on("click", ".remove_publication_block", function(event){
        if( confirm('Are you sure you want to remove this Publication?') )
        {
          $(this).parent('.single_publication_template').fadeOut(300);
        }
      });
      // Remove submitted publication record block.
      $("form").on("click", ".remove_submitted_publication_block", function(event){
        if( confirm('Are you sure you want to remove this Publication?') )
        {
          $(this).parent('.publication_single_container').fadeOut(300);
        }
      });

      // Delete one publication.
      $("form").on("click", ".publication_delete_link", function(event){
        var publication_id = $(this).attr('data-publication-id');
        if( confirm('Are you sure you want to remove this publication?') ) {
          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/delete_publication/"
            ,data: ( {publication_id: publication_id} )
            ,success: function(ajax_return) {
              $('#publication_div_'+publication_id).fadeOut(300);
            }
          });
        }
      });

      /*
       * Assay Types
       */

      var assay_types_id = "{{ data.assay_types_id }}";

      // Toggle the visibility of assay type form field containers.
      $('#assay_type').on('change', function(element) {
        var this_assay_type_id = $(this).val();
        if(this_assay_type_id.length) {
          $('#assay_types_controls').css('height', '360px');
        } else {
          $('#assay_types_controls').removeAttr('style');
        }
        $('div[id^="assay_type_"]').fadeOut('fast');
        setTimeout(function(){
          $('#assay_type_'+this_assay_type_id).fadeIn();
        },500);
      });

      /*
       * SOP File uploads using the jQuery-File-Upload plugin.
       */

      var file_count = 0;
      var sop_file_types = JSON.parse('{{ data.sop_file_types|json_encode|raw }}');
      var existing_sop_files = JSON.parse('{{ data.sop_files|json_encode|raw }}');
      
      $('#fileupload').fileupload({

          dataType: 'json',
          progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('.uploading').fadeIn(100);
            $('#progress .bar').css('width', progress + '%');
            $('#progress').fadeIn(100);
          },
          done: function (e, data) {

            $.each(data.result, function (index, file) {

              $.ajax({
                type:"POST"
                ,dataType:"json"
                ,url: "{{ path_to_this_module }}/insert_sop_file/"
                ,data: ( {file_data: file} )
                ,success: function(ajax_return){

                  var row_fluid = $('<div />').attr('class', 'row-fluid file-controls-wrapper');

                  if(ajax_return) {
                    var hidden_input = $('<input />').attr('type', 'hidden').attr('name', 'uploaded_files[]').attr('value', ajax_return);
                    var sop_file_type_menu_div = $('<div />').attr('id', 'sop_file_type_menu_div_'+ajax_return).attr('class', 'sop-file-type-menu span6');
                    $(row_fluid).append( sop_file_type_menu_div );
                    $(row_fluid).append('<div class="single-file-wrapper span6"><span class="icon-eye-open"></span> <a href="{{ sop_file_upload_directory }}'+file.internal_file_name+'" class="single-file download-file" title="View/download '+file.name+'" target="_blank">'+file.name+'</a></div>');
                    $(row_fluid).append( hidden_input );
                    $('#uploading_notification_container').after( row_fluid );

                    add_sop_file_type_menu_block( ajax_return, sop_file_types )

                  } else {
                    $('#fileupload').after('<div class="single-file-wrapper"><span class="icon-exclamation-sign"></span> An error occurred. If this error persits, please <a href="/support/">contact the administrator.</div>');
                  }

                }
              });

            });
            $('.uploading-text').text('Upload completed');
            setTimeout(function() {
              $('.uploading').fadeOut(1000);
              $('#progress').fadeOut(1000);
            }, 2000 );
            file_count = (file_count + 1);
          }

      });

      // Delete one SOP file.
      $("form").on("click", ".sop_delete_link", function(event){
        var file_id = $(this).attr('data-sop-file-id');
        if( confirm('Are you sure you want to remove this SOP file?') ) {
          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/delete_file/"
            ,data: ( {file_id: file_id} )
            ,success: function(ajax_return) {
              $('#sop_file_div_'+file_id).fadeOut(300);
            }
          });
        }
      });

      // Delete one SOP file pre-post.
      $("form").on("click", ".delete_file_pre_post_link", function(event){
        var this_link = $(this);
        var file_id = $(this).attr('data-sop-file-id');
        if( confirm('Are you sure you want to remove this SOP file?') ) {
          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/delete_file_pre_post/"
            ,data: ( {file_id: file_id} )
            ,success: function(ajax_return) {
              $(this_link).parent().parent().parent().fadeOut(300);
              $(this_link).parent().parent().parent().remove();
            }
          });
        }
      });

      /*
       * Update SOP File Type IDs in the sop_files table.
       */

      $("form").on("change", ".sop_type_menu", function(event){
        var all_selected_ids = [];
        var sop_file_id = $(this).attr('data-sop-file-id');
        var sop_file_type_id = $(this).val();
        var all_selects = $('#sop_files_container .file-controls-wrapper .sop-file-type-menu .sop_types_menu_template').find('select');
        // Place all of the selected sop_file_type_ids into an array.
        if(all_selects.length > 0) {
          $.each(all_selects, function (index, single_select) {
            if($(single_select).val().length > 0) {
              all_selected_ids.push( $(single_select).val() );
            }
          });
        }
        // Place all sop_file_type_ids which have been associated to existing uploaded files into an array.
        if(existing_sop_files) {
          $.each(existing_sop_files, function (index, single_sop_file) {
            if(single_sop_file.sop_file_type_id.length) {
              all_selected_ids.push( single_sop_file.sop_file_type_id );
            }
          });
        }
        // Check the array for duplicates, and throw an alert if a duplicate sop_file_type_id is found.
        var duplicates_found = contains_duplicates( all_selected_ids );
        if( duplicates_found ) {
          $('#duplicateSopFileTypeAlert').modal();
          $(this).val('');
        } else {
          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/update_sop_file_type_id/"
            ,data: ( {sop_file_id: sop_file_id, sop_file_type_id: sop_file_type_id} )
            ,success: function(ajax_return) {
              // Do nothing.
            }
          });
        }
      });

      /*
       * Tooltips
       */

      $(".icon-question-sign").tooltip();
      $(".delete_file_pre_post_link").tooltip();

    });

    /*
     * Functions
     */
    
    // Underscore-based add Publication block function.
    function add_publication_block( publication_citation, publication_url ) {
      var publication_container_id = _.uniqueId();
      var publication_template = _.template($("#single_publication_template").html());
      var publication_markup = publication_template({
          "publication_id":publication_container_id
        , "publication_citation":publication_citation
        , "publication_url":publication_url
      });
      $("#publication_div").append(publication_markup);
    }

    // Underscore-based add SOP File Type Menu block function.
    function add_sop_file_type_menu_block( sop_file_id, sop_file_types ) {
      var sop_file_type_menu_container_id = _.uniqueId();
      var sop_file_type_menu_template = _.template($("#sop_types_menu_template").html());
      var sop_file_type_menu_markup = sop_file_type_menu_template({
          "sop_file_type_menu_id":sop_file_type_menu_container_id
        , "sop_file_id":sop_file_id
        , "sop_file_types":sop_file_types
      });
      $("#sop_file_type_menu_div_"+sop_file_id).append(sop_file_type_menu_markup);
    }

    // Check an array for duplicate values.
    function contains_duplicates( a ) {
      var i=a.length;
      var a2, o, j;
      while (i--) {
        a2 = a.slice(0,i).concat(a.slice(i+1,a.length));
        o = {};
        j = a2.length;
        while (j--) {
          o[a2[j]] = '';
        }
        if (a[i] in o) {
          return true;
        }
      }
      return false;
    };

  </script>
{% endblock %}
