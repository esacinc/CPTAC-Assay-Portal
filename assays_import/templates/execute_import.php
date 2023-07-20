{% extends layout_template_name %}
{% block styles_head %}
  {{ parent() }}
  <link href="//cdnjs.cloudflare.com/ajax/libs/sweetalert/0.3.2/sweet-alert.min.css" rel="stylesheet">
{% endblock %}
{% block content %}

{% set execute_button_class = "btn-primary" %}
{% set execute_test_button_class = "" %}
{% set disabled = '' %}
{% set disabled_text = '' %}
{% set executing = false %}

{% if data.executed_imports %}
  {% for single in data.executed_imports %}
    {% if single.number_of_records == 0 %}
      {% set execute_button_class = "btn-danger" %}
      {% set execute_test_button_class = "btn-danger" %}
      {% set disabled = ' disabled="disabled"' %}
      {% set disabled_text = ' (disabled)' %}
      {% set executing = true %}
    {% endif %}
    {% if single.import_in_progress or single.reimport_in_progress %}
      {% set execute_button_class = "btn-danger" %}
      {% set execute_test_button_class = "btn-danger" %}
      {% set disabled = ' disabled="disabled"' %}
      {% set disabled_text = ' (disabled)' %}
      {% set executing = true %}
    {% endif %}
  {% endfor %}
{% endif %}

<div class="row-fluid">
  {% if errors %}
    <div class="alert alert-block">
      <h4>Form Errors</h4>
      {% for single_error in errors %}
      <p>{{ single_error }}</p>
      {% endfor %}
    </div>
  {% endif %}
</div>

<div class="row-fluid">
  {% if flash['success'] %}
    <div class="alert alert-block alert-success">
      <button type="button" class="close" data-dismiss="alert">×</button>
      {{ flash['success'] }}
    </div>
  {% endif %}
</div>

<div class="row-fluid">
  <div class="span12">

    {% if data.import_executed_status == false %}
      <input type="hidden" name="import_log_id" value="{{ data.laboratory_data.import_log_id|e }}">

      {% if data.executed_imports == false %}
        <div class="row-fluid">
          <div class="span6">
            <h3><i class="icon-hand-right"></i> Execute A Test Run</h3>
            <div class="well">
              <div style="min-height: 160px;">
                <p><i class="icon-info-sign"></i> <strong>Choose this option if you wish to run a test of 5 assays (highly recommended).</strong></p>
                <p>The 5 assays are automatically selected and cannot be determined by the user. After the test import has completed, you will have the opportunity to review any issues, delete the initial test import, and execute the test import again, or execute the full import. You will be notified via email when the import begins and has completed.</p>
              </div>
              <a href="javascript:void(0);" id="execute_test" class="btn {{ execute_test_button_class }}"><i class="icon-cog"></i> Execute A Test Run</a>
            </div>
          </div>
          <div class="span6">
            <h3><i class="icon-hand-right"></i> Execute A Full Import</h3>
            <div class="well">
              <div style="min-height: 160px;">
                <p><i class="icon-info-sign"></i> <strong>Choose this option to execute the full import.</strong></p>
                <p>Please note that, depending on the amount of records, the import process may take some time (hours) to complete. Please be patient. You will be notified via email when the import begins and has completed.</p>
              </div>
              <a href="javascript:void(0);" id="execute_all" class="btn {{ execute_button_class }}"><i class="icon-cog"></i> Execute Full Import</a>
            </div>
          </div>
        </div>
        <hr>
      {% endif %}

      {% if data.executed_imports %}
        {% for single in data.executed_imports %}
          {% if single.reimport_in_progress %}
            <h3><i class="icon-exclamation-sign red"></i> Currently Re-Executing the Import</h3>
            <p class="muted">Only one import can be executed at a time.</p>
          {% endif %}
        {% endfor %}
      {% endif %}
      <table class="table table-bordered table-striped table-condensed">
        <tbody>
          <tr>
            <th>Executed Date</th>
            <th>Number of Records</th>
            <th>Actions</th>
          </tr>
          {% if data.executed_imports %}
            {% for single in data.executed_imports %}
              {% if single.import_in_progress %}
                {% set number_of_records = '<span class="red"><i class="icon-exclamation-sign"></i> Currently executing import</span>' %}
              {% else %}
                {% set number_of_records = single.number_of_records %}
              {% endif %}
              <tr>
                <td>{{ single.import_executed_date }}</td>
                <td>{{ number_of_records|raw }}</td>
                <td>
                  {% if data.executed_imports %}
                    {% for single in data.executed_imports %}
                      {% if ((single.reimport_in_progress == 0) and (single.import_in_progress == 0)) %}
                      <a id="send_error_report_email" class="btn btn-small" data-import-log-id="{{ data.laboratory_data.import_log_id }}" 
                        title="Send an error report to the CPTAC Portal Administrators."><i class="icon-envelope"></i> Send Error Report</a>
                      
                      <a href="/assays_preview/" class="btn btn-small" data-import-log-id="{{ data.laboratory_data.import_log_id }}" 
                        title="Preview the imported assays on the Assay Portal"><i class="icon-eye-open"></i> Preview Assays</a>

                      {% if data.path_to_log %}
                         <a href="{{ data.path_to_log }}" target="_blank" class="btn btn-small" data-import-log-id="{{ data.laboratory_data.import_log_id }}" 
                        title="View the last import log"><i class="icon-file-text-alt"></i> View Import Log</a>
                      {% endif %}


                      <a href="javascript:void(0);" id="delete_import" class="btn btn-small btn-danger" data-import-log-id="{{ data.laboratory_data.import_log_id }}" 
                        title="If something has gone wrong, or if a test import needs to be removed, you may safely delete all data and image files associated with this import."><i class="icon-trash"></i> Delete This Import</a>
                      {% else %}
                      <!-- <a href="javascript:void(0);" id="reset_import" class="btn btn-small" data-import-log-id="{{ data.laboratory_data.import_log_id }}" 
                        title="If something has gone wrong, you can reset this import."><i class="icon-refresh"></i> Reset This Import</a> -->
                        <a href="/support/" class="btn btn-small" title="If no results have been returned by the import, please submit a support ticket."><i class="icon-medkit"></i> Submit a Support Ticket</a>
                      {% endif %}
                    {% endfor %}
                  {% endif %}
                  </td>
              </tr>
            {% endfor %}
          {% else %}
            <tr>
              <td colspan="3">No imports executed.</td>
            </tr>
          {% endif %}
        </tbody>
      </table>

      {% if executing == false %}
        {% if data.executed_imports %}
          <div class="well">
              <p><i class="icon-medkit"></i> If the Health Report returns problematic images and data, please send an 
                "Error Report" to the CPTAC Portal Administrators ({{ data.panorama_errors_email_recipients.names|join(', ') }}) 
                using the "Send Error Report" button above.</p>
                <hr>
              <p><i class="icon-warning-sign"></i> The test import must be deleted in order to run a full import. Once the test 
                import has been removed, the interface will display both options, "Execute a Test Run" and 
                "Execute a Full Import".</p>
          </div>
        {% endif %}
      {% endif %}

      <h3>Health Report</h3>

      {% if executing == false %}
        {% if data.executed_imports %}
          {% if data.missed_images.chromatograms or data.missed_images.response_curves or data.missed_images_data.lod_loq_data or data.missed_images_data.response_curves_data or data.missed_images.validation_samples or data.missed_images_data.validation_samples_data %}
            <form id="reexecute_import_form" method="POST" action="{{ path_to_this_module }}/execute" class="form-horizontal">
              <input type="hidden" name="import_log_id" value="{{ data.laboratory_data.import_log_id|e }}">
              <input type="hidden" name="imports_executed_log_id" value="{{ data.executed_imports[0].imports_executed_log_id|e }}">
              <input type="hidden" name="account_id" value="{{ data.session.account_id|e }}">
              <input type="hidden" name="run_missed_images" value="true">
              <a id="reexecute_import" class="btn {{ execute_button_class }}"{{ disabled }}>Re-execute Import Only for These Images and Data{{ disabled_text }}</a>
            </form>
            <p class="muted">If problems persist, please don't hesitate to <a href="/support/" title="Go to the Support form">contact us</a>.</p>
          {% endif %}

          <h4 class="margin-top-40">Problematic Chromatogram Images <span class="un-bold">({{ data.missed_images.chromatograms|length }})</span></h4>
          <i class="icon-eye-open"></i> <a href="javascript:void(0);" class="toggle_chromatogram_image_errors">expand all errors</a>
         
          <table class="table table-bordered table-striped table-condensed">
            <tbody>
              <tr>
                <th>CPTAC ID</th>
                <th>Peptide (Modified) Sequence</th>
                <th>Image File</th>
              </tr>
              {% if data.missed_images.chromatograms %}
                {% for single in data.missed_images.chromatograms %}
                  <tr>
                      <td><a href="/{{ single.cptac_id }}" target="_blank" title="View {{ single.cptac_id }} on the Portal"><strong>{{ single.cptac_id }}</strong></a> - [<a href="javascript:void(0);" id="{{ single.analyte_peptide_id }}_chromatogram_image" class="view-chromatogram-image-errors" title="View errors generated by Panorama">display errors</a>]</td>
                      <td>{{ single.peptide_modified_sequence }}</td>
                      <td>{% if single.file_name %}<a href="{{ panorama_images_path }}{{ single.file_name }}" target="_blank">View Image</a>{% else %}No Image{% endif %}</td>
                    </tr>
                    
                    <tr>
                      <td id="error_{{ single.analyte_peptide_id }}_chromatogram_image_td" class="chromatogram_image_errors_td" style="display: none;" colspan="3">
                        <div id="error_{{ single.analyte_peptide_id }}_chromatogram_image" class="chromatogram_image_errors" style="display: none;">
                          {% if error %}
                          {% for error in single.error %}
                          <strong>Type:</strong> {{ error.type }}<br>
                          <strong>Generated Date:</strong> {{ error.created_date }}<br>
                          <strong>Panorama Error:</strong><br><pre class="text-error">{{ error.error_response|trim }}</pre>
                          {% endfor %}
                          {% else %}
                          <span class="muted">Unknown: No error reported by server.</span>
                          {% endif %}
                        </div>
                      </td>
                    </tr>
                {% endfor %}
              {% else %}
                <tr>
                  <td class="no-results-td" colspan="3"><p>No problematic chromatogram images detected.</p></td>
                </tr>
              {% endif %}
            </tbody>
          </table>

          <h4 class="margin-top-40">Problematic Response Curve Images <span class="un-bold">({{ data.missed_images.response_curves|length }})</span></h4>
          <i class="icon-eye-open"></i> <a href="javascript:void(0);" class="toggle_response_curve_images_errors">expand all errors</a>
          <table class="table table-bordered table-striped table-condensed">
            <tbody>
              <tr>
                <th>CPTAC ID</th>
                <th>Peptide (Modified) Sequence</th>
                <th>Image File</th>
              </tr>
              {% if data.missed_images.response_curves %}
                {% for single in data.missed_images.response_curves %}
                  
                    <tr>
                      <td><a href="/CPTAC-{{ single.analyte_peptide_id }}" target="_blank" title="View {{ single.cptac_id }} on the Portal"><strong>{{ single.cptac_id }}</strong></a> - [<a href="javascript:void(0);" id="{{ single.analyte_peptide_id }}_response_curve" class="view-response-curve-errors" title="View errors generated by Panorama">display errors</a>]</td>
                      <td>{{ single.peptide_modified_sequence }}</td>
                      <td>{% if single.file_name %}<a href="{{ panorama_images_path }}{{ single.file_name }}" target="_blank">View Image</a>{% else %}No Image{% endif %}</td>
                    </tr>
                    
                    <tr>
                      <td id="error_{{ single.analyte_peptide_id }}_response_curve_td" class="response_curve_errors_td" style="display: none;" colspan="3">
                        <div id="error_{{ single.analyte_peptide_id }}_response_curve" class="response_curve_errors" style="display: none;">
                          {% if single.error_response %}
                          {% for error in single.error %}
                          <strong>Curve Type:</strong> {{ error.curve_type }}<br>
                          <strong>Generated Date:</strong> {{ error.created_date }}<br>
                          <strong>Panorama URL:</strong> <a href="{{ error.panorama_url }}" target="_blank" title="Execute API Call (authentication required)">{{ error.panorama_url }}</a><br>
                          <strong>Panorama Error:</strong><br><pre class="text-error">{{ error.error_response|trim }}</pre>
                          {% endfor %}
                          {% else %}
                          <span class="muted">No errors generated by Panorama. This issue could have been an API or server-based performance glitch.</span>
                          {% endif %}
                        </div>
                      </td>
                    </tr>
                  
                {% endfor %}
              {% else %}
                <tr>
                  <td class="no-results-td" colspan="3"><p>No problematic response curve images detected.</p></td>
                </tr>
              {% endif %}
            </tbody>
          </table>

          <h4 class="margin-top-40">Problematic LOD/LOQ Data <span class="un-bold">({{ data.missed_images_data.lod_loq_data|length }})</span></h4>
          <i class="icon-eye-open"></i> <a href="javascript:void(0);" class="toggle_lod_loq_errors">expand all errors</a>
          <table class="table table-bordered table-striped table-condensed">
            <tbody>
              <tr>
                <th>CPTAC ID</th>
                <th>Peptide (Modified) Sequence</th>
              </tr>
              {% if data.missed_images_data.lod_loq_data %}
                {% for single in data.missed_images_data.lod_loq_data %}
                  <tr>
                    <td><a href="/CPTAC-{{ single.analyte_peptide_id }}" target="_blank" title="View {{ single.cptac_id }} on the Portal"><strong>{{ single.cptac_id }}</strong></a> - [<a href="javascript:void(0);" id="{{ single.analyte_peptide_id }}_lod_loq" class="view-lod-loq-errors" title="View errors generated by Panorama">display errors</a>]</td>
                    <td>{{ single.peptide_modified_sequence }}</td>
                  </tr>
                  <tr>
                    <td id="error_{{ single.analyte_peptide_id }}_lod_loq_td" class="lod_loq_errors_td" style="display: none;" colspan="2">
                      <div id="error_{{ single.analyte_peptide_id }}_lod_loq" class="lod_loq_errors" style="display: none;">
                        {% if single.error_response %}
                        <strong>Generated Date:</strong> {{ single.created_date }}<br>
                        <strong>Panorama URL:</strong> <a href="{{ single.panorama_url }}" target="_blank" title="Execute API Call (authentication required)">{{ single.panorama_url }}</a><br>
                        <strong>Panorama Error:</strong><br><pre class="text-error">{{ single.error_response|trim }}</pre>
                        {% else %}
                        <span class="muted">No errors generated by Panorama. This issue could have been an API or server-based performance glitch.</span>
                        {% endif %}
                      </div>
                    </td>
                  </tr>
                {% endfor %}
              {% else %}
                <tr>
                  <td class="no-results-td" colspan="3"><p>No problematic LOD/LOQ data detected.</p></td>
                </tr>
              {% endif %}
            </tbody>
          </table>

          <h4 class="margin-top-40">Problematic Curve Fit Data <span class="un-bold">({{ data.missed_images_data.response_curves_data|length }})</span></h4>
          <i class="icon-eye-open"></i> <a href="javascript:void(0);" class="toggle_response_curves_data_errors">expand all errors</a>
          <table class="table table-bordered table-striped table-condensed">
            <tbody>
              <tr>
                <th>CPTAC ID</th>
                <th>Peptide (Modified) Sequence</th>
              </tr>
              {% if data.missed_images_data.response_curves_data %}
                {% for single in data.missed_images_data.response_curves_data %}
                  <tr>
                    <td><a href="/CPTAC-{{ single.analyte_peptide_id }}" target="_blank" title="View {{ single.cptac_id }} on the Portal"><strong>{{ single.cptac_id }}</strong></a> - [<a href="javascript:void(0);" id="{{ single.analyte_peptide_id }}_response_curves_data" class="view-response-curve-data-errors" title="View errors generated by Panorama">display errors</a>]</td>
                    <td>{{ single.peptide_modified_sequence }}</td>
                  </tr>
                  <tr>
                    <td id="error_{{ single.analyte_peptide_id }}_response_curves_data_td" class="response_curves_data_errors_td" style="display: none;" colspan="2">
                      <div id="error_{{ single.analyte_peptide_id }}_response_curves_data" class="response_curves_data_errors" style="display: none;">
                        {% if single.error_response %}
                        <strong>Generated Date:</strong> {{ single.created_date }}<br>
                        <strong>Panorama URL:</strong> <a href="{{ single.panorama_url }}" target="_blank" title="Execute API Call (authentication required)">{{ single.panorama_url }}</a><br>
                        <strong>Panorama Error:</strong><br><pre class="text-error">{{ single.error_response|trim }}</pre>
                        {% else %}
                        <span class="muted">No errors generated by Panorama. This issue could have been an API or server-based performance glitch.</span>
                        {% endif %}
                      </div>
                    </td>
                  </tr>
                {% endfor %}
              {% else %}
                <tr>
                  <td class="no-results-td" colspan="3"><p>No problematic curve fit data detected.</p></td>
                </tr>
              {% endif %}
            </tbody>
          </table>

          <h4 class="margin-top-40">Problematic Repeatability Images <span class="un-bold">({{ data.missed_images.validation_samples|length }})</span></h4>
          <i class="icon-eye-open"></i> <a href="javascript:void(0);" class="toggle_validation_sample_images_errors">expand all errors</a>
          <table class="table table-bordered table-striped table-condensed">
            <tbody>
              <tr>
                <th>CPTAC ID</th>
                <th>Peptide (Modified) Sequence</th>
              </tr>
              {% if data.missed_images.validation_samples %}
                {% for single in data.missed_images.validation_samples %}
                    <tr>
                      <td><a href="/CPTAC-{{ single.analyte_peptide_id }}" target="_blank" title="View {{ single.cptac_id }} on the Portal"><strong>{{ single.cptac_id }}</strong></a> - [<a href="javascript:void(0);" id="{{ single.analyte_peptide_id }}_validation_sample_images" class="view-validation-sample-images-errors" title="View errors generated by Panorama">display errors</a>]</td>
                      <td>{{ single.peptide_modified_sequence }}</td>
                    </tr>
                    <tr>
                      <td id="error_{{ single.analyte_peptide_id }}_validation_sample_images_td" class="validation_sample_images_errors_td" style="display: none;" colspan="2">
                        <div id="error_{{ single.analyte_peptide_id }}_validation_sample_images" class="validation_sample_images_errors" style="display: none;">
                          {% if single.error %}
                          <strong>Generated Date:</strong> {{ single.error.created_date }}<br>
                          <strong>Panorama URL:</strong> <a href="{{ single.panorama_url }}" target="_blank" title="Execute API Call (authentication required)">{{ single.error.panorama_url }}</a><br>
                          <strong>Panorama Error:</strong><br><pre class="text-error">{{ single.error.error_response|trim }}</pre>
                          {% else %}
                          <span class="muted">No errors generated by Panorama. This issue could have been an API or server-based performance glitch.</span>
                          {% endif %}
                        </div>
                      </td>
                    </tr>
                {% endfor %}
              {% else %}
                <tr>
                  <td class="no-results-td" colspan="3"><p>No problematic repeatability images detected.</p></td>
                </tr>
              {% endif %}
            </tbody>
          </table>

          <h4 class="margin-top-40">Problematic Repeatability Data <span class="un-bold">({{ data.missed_images_data.validation_samples_data|length }})</span></h4>
          <i class="icon-eye-open"></i> <a href="javascript:void(0);" class="toggle_validation_sample_data_errors">expand all errors</a>
          <table class="table table-bordered table-striped table-condensed">
            <tbody>
              <tr>
                <th>CPTAC ID</th>
                <th>Peptide (Modified) Sequence</th>
              </tr>
              {% if data.missed_images_data.validation_samples_data %}
                {% for single in data.missed_images_data.validation_samples_data %}
                  <tr>
                    <td><a href="/CPTAC-{{ single.analyte_peptide_id }}" target="_blank" title="View {{ single.cptac_id }} on the Portal"><strong>{{ single.cptac_id }}</strong></a> - [<a href="javascript:void(0);" id="{{ single.analyte_peptide_id }}_validation_sample_data" class="view-validation-sample-data-errors" title="View errors generated by Panorama">display errors</a>]</td>
                    <td>{{ single.peptide_modified_sequence }}</td>
                  </tr>
                  <tr>
                    <td id="error_{{ single.analyte_peptide_id }}_validation_sample_data_td" class="validation_sample_data_errors_td" style="display: none;" colspan="2">
                      <div id="error_{{ single.analyte_peptide_id }}_validation_sample_data" class="validation_sample_data_errors" style="display: none;">
                        {% if single.error_response %}
                        <strong>Generated Date:</strong> {{ single.created_date }}<br>
                        <strong>Panorama URL:</strong> <a href="{{ single.panorama_url }}" target="_blank" title="Execute API Call (authentication required)">{{ single.panorama_url }}</a><br>
                        <strong>Panorama Error:</strong><br><pre class="text-error">{{ single.error_response|trim }}</pre>
                        {% else %}
                        <span class="muted">No errors generated by Panorama. This issue could have been an API or server-based performance glitch.</span>
                        {% endif %}
                      </div>
                    </td>
                  </tr>
                {% endfor %}
              {% else %}
                <tr>
                  <td class="no-results-td" colspan="3"><p>No problematic repeatability data detected.</p></td>
                </tr>
              {% endif %}
            </tbody>
          </table>
        {% else %}
          <div class="alert alert-block" style="margin-bottom:200px;">
            Import not executed yet. Health Report unavailable.
          </div>
        {% endif %}
      {% else %}
        <div class="alert alert-block" style="margin-bottom:200px;">
          Import currently executing. Health Report unavailable.  <a href="/assays_import/execute/?import_log_id={{ data.laboratory_data.import_log_id|e }}&import_executed_status=true">View Log</a>
        </div>
      {% endif %}

    {% else %}
    
      <div class="alert alert-success">The import has been executed.</div>
      <ol>
        <li>An email confirmation of the import execution time has been sent to your inbox at <strong>{{ data.session.email }}</strong>.</li>
        <li>Another email confirmation will be sent when the import has finished.</li>
        <li>Please note that, depending on the amount of records, the import process may take some time to complete. Please be patient.</li>
        <li>If you do not receive confirmation emails from the system, please <a href="/support/">submit a support ticket</a>.</li>
      </ol>
      <p><a href="/assays_import/execute/?import_log_id={{ data.laboratory_data.import_log_id|e }}" class="btn btn-default"><i class="icon-arrow-left"></i> Back to Overview</a></p>
      <div class="import-log-container"></div>
      <div class="download-log"></div> 
     

    {% endif %}
  </div>
</div>

<!-- Notify NCI modal -->
<div id="notify_nci_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="notifyNciModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="icon-envelope"></i> Notify NCI</h3>
  </div>
  <div class="modal-body">
    <div class="control-group">
      <div id="notify_nci_container">
        <p id="notify_nci_instructions"></p>
        <div class="control-group">
          <p><em>Send an email to notify NCI that this import is ready for reveiw.</em></p>
          <label for="notify_nci_comment">Add a Message <span class="muted">(optional)</span></label>
          <textarea id="notify_nci_comment" name="notify_nci_comment"></textarea>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" id="notify_nci_submit_button" class="btn btn-small btn-primary">Send</button>
    <button id="cancel_close_button" class="btn btn-small" data-dismiss="modal" aria-hidden="true">Cancel</button>
  </div>
</div>

<!-- Executing Import modal -->
<div id="import_executing_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="preloaderModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="preloaderModalLabel">Import is Currently Executing</h3>
  </div>
  <div class="modal-body">
    <img src="/site/library/images/preloader_90x90.gif" width="90" height="90" alt="animated preloader image">
    <p>Running two imports at once is not allowed</p>
  </div>
</div>

<!-- Preloader modal -->
<div id="preloader_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="preloaderModalLabel" aria-hidden="true">
  <div class="modal-body">
    <img src="/site/library/images/preloader_90x90.gif" width="90" height="90" alt="animated preloader image">
    <h4>Initializing&hellip; please wait</h4>
  </div>
</div>




{% endblock %}
{% block js_bottom %}
  {{ parent() }}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/0.3.2/sweet-alert.min.js"></script>
  <script type="text/javascript">

   var show_log = "{{ show_log }}";

    $(document).ready(function(){

      var account_id = "{{ data.session.account_id }}";
      var import_log_id = "{{ data.laboratory_data.import_log_id|e }}";
      var laboratory_id = "{{ data.laboratory_data.laboratory_id|e }}";
      var panorama_errors_email_recipients_names = "{{ data.panorama_errors_email_recipients.names|join(', ') }}";

      $(".import-log-container").hide();

      /*
       * Execute a test import.
       */

      $("#execute_test").on("click", function(event){

        swal({
          title: "Confirm",
          text: "Are you sure you want to execute a test import?",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, execute a test import.",
          closeOnConfirm: true,
        },
        function(){
          $( "#preloader_modal" ).modal('show');

          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/execute"
            ,data: ( {import_log_id: import_log_id, account_id: account_id, test_import: true} )
            ,success: function(ajax_return) {
              // Nothing is returned.
            }
          });

         return window.location = "{{ path_to_this_module }}/execute/?import_log_id="+import_log_id+"&import_executed_status=true";
        });

      });

      /*
       * Execute the full import.
       */

      $("#execute_all").on("click", function(event){

        swal({
          title: "Confirm",
          text: "Are you sure you want to execute the full import?",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, execute the full import.",
          closeOnConfirm: true,
        },
        function(){
          $( "#preloader_modal" ).modal('show');

          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/execute"
            ,data: ( {import_log_id: import_log_id, account_id: account_id} )
            ,success: function(ajax_return) {
              // Nothing is returned.
            }
          });

          return window.location = "{{ path_to_this_module }}/execute/?import_log_id="+import_log_id+"&import_executed_status=true";
        });

      });

      /*
       * Re-execute the import against problematic images and data.
       */

      $("#reexecute_import").on("click", function(event){
        swal({
          title: "Confirm",
          text: "Are you sure you want to re-execute the import?",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, re-execute the import.",
          closeOnConfirm: true,
        },
        function(){
          $( "#preloader_modal" ).modal('show');
          $( "#reexecute_import_form" ).submit();
        });
      });

      /*
       * Notify the NCI Assay Review Team that the assays are ready for review.
       */

      $("#notify_nci_submit_button").on("click", function(event){

        var message = $("#notify_nci_comment").val();
        // import_log_id: import_log_id, account_id: account_id,
        
        $.ajax({
          type: "POST"
          ,dataType: "json"
          ,url: "{{ path_to_this_module }}/email_nci"
          ,data: ( {laboratory_id: laboratory_id, message: message} )
          ,success: function(ajax_return) {
            if(ajax_return == 'sent') {
            $('#email_sent_message').remove();
            $('#notify_nci_container').hide();
            $('#notify_nci_submit_button').hide();
            $('#cancel_close_button').text('Close');
            $('#notify_nci_container').parent().before('<div id="email_sent_message" class="alert alert-success">Email sent successfully.</div>');
          } else {
            $('#notify_nci_container').parent().before('<div id="email_sent_message" class="alert alert-error">Emails not sent.</div>');
          }
          }
        });
      });

      /*
       * Delete the import.
       */

      $("#delete_import").on("click", function(event){

        var import_log_id = $(this).attr('data-import-log-id');

        swal({
          title: "Confirm",
          text: "Are you sure you want to delete this import? This is a destructive operation and there is no way to rebuild the data once executed.",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, delete this import.",
          closeOnConfirm: true,
        },
        function(){

          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/delete_import"
            ,data: ( {import_log_id: import_log_id} )
            ,success: function(ajax_return) {
              if(ajax_return == 'import_deleted') {
                window.location.href = "{{ path_to_this_module }}/execute/?import_log_id={{ data.laboratory_data.import_log_id|e }}";
              } else {
                alert("[ERROR]: Import data not deleted. Please contact the administrator if this persists.");
              }
            }
          });

        });

      });

      /*
       * Send the Health Check error report.
       */

      $("#send_error_report_email").on("click", function(event){

        var import_log_id = $(this).attr('data-import-log-id');

        swal({
          title: "Confirm",
          text: 'Are you sure you wish to send an error report? The following CPTAC Assay Portal Administrators will receive the email: '+panorama_errors_email_recipients_names+'. You will also receive a copy of the email.',
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, send an error report to the CPTAC Assay Portal Administrators.",
          closeOnConfirm: true,
        },
        function(){

          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/send_error_report_email"
            ,data: ( {import_log_id: import_log_id, laboratory_id: laboratory_id} )
            ,success: function(ajax_return) {
              if(ajax_return == 'email_sent') {
                window.location.href = "{{ path_to_this_module }}/execute/?import_log_id={{ data.laboratory_data.import_log_id|e }}";
              } else {
                alert("[ERROR]: The email was not sent. Please contact the administrator if this persists.");
              }
            }
          });

        });

      });

      /*
       * Reset the import.
       */

      $("#reset_import").on("click", function(event){

        var import_log_id = $(this).attr('data-import-log-id');

        swal({
          title: "Confirm",
          text: "Are you sure you want to reset this import? This will bring back the ability to re-execute the import, and is useful for when the process has halted for a while, due to errors.",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, reset this import.",
          closeOnConfirm: true,
        },
        function(){
          
          $.ajax({
            type: "POST"
            ,dataType: "json"
            ,url: "{{ path_to_this_module }}/reset_import"
            ,data: ( {import_log_id: import_log_id} )
            ,success: function(ajax_return) {
              if(ajax_return == 'import_reset') {
                window.location.href = "{{ path_to_this_module }}/execute/?import_log_id={{ data.laboratory_data.import_log_id|e }}&reset=true";
              } else {
                alert("[ERROR]: Import has not been reset. Please contact the administrator if this persists.");
              }
            }
          });

        });

      });

      /*
       * Show/Hide Errors: Chromatogram images.
       */

      $(".view-chromatogram-image-errors").on("click", function(event){
        $("#error_"+this.id+"_td").toggle();
        $("#error_"+this.id).slideToggle();
      });

      $(".toggle_chromatogram_image_errors").on("click", function(event){
        $(".chromatogram_image_errors").toggle();
        if($(".toggle_chromatogram_image_errors").text() == "expand all errors") {
          $(".toggle_chromatogram_image_errors").text("collapse all errors");
        } else {
          $(".toggle_chromatogram_image_errors").text("expand all errors");
        }
        $(".chromatogram_image_errors_td").toggle();
      });

      /*
       * Show/Hide Errors: Response curve images.
       */

      $(".view-response-curve-errors").on("click", function(event){
        $("#error_"+this.id+"_td").toggle();
        $("#error_"+this.id).slideToggle();
      });

      $(".toggle_response_curve_images_errors").on("click", function(event){
        $(".response_curve_errors").toggle();
        if($(".toggle_response_curve_images_errors").text() == "expand all errors") {
          $(".toggle_response_curve_images_errors").text("collapse all errors");
        } else {
          $(".toggle_response_curve_images_errors").text("expand all errors");
        }
        $(".response_curve_errors_td").toggle();
      });

      /*
       * Show/Hide Errors: LOD LOQ data.
       */

      $(".view-lod-loq-errors").on("click", function(event){
        $("#error_"+this.id+"_td").toggle();
        $("#error_"+this.id).slideToggle();
      });

      $(".toggle_lod_loq_errors").on("click", function(event){
        $(".lod_loq_errors").toggle();
        if($(".toggle_lod_loq_errors").text() == "expand all errors") {
          $(".toggle_lod_loq_errors").text("collapse all errors");
        } else {
          $(".toggle_lod_loq_errors").text("expand all errors");
        }
        $(".lod_loq_errors_td").toggle();
      });

      /*
       * Show/Hide Errors: Response curve data (curve fit).
       */

      $(".view-response-curve-data-errors").on("click", function(event){
        $("#error_"+this.id+"_td").toggle();
        $("#error_"+this.id).slideToggle();
      });

      $(".toggle_response_curves_data_errors").on("click", function(event){
        $(".response_curves_data_errors").toggle();
        if($(".toggle_response_curves_data_errors").text() == "expand all errors") {
          $(".toggle_response_curves_data_errors").text("collapse all errors");
        } else {
          $(".toggle_response_curves_data_errors").text("expand all errors");
        }
        $(".response_curves_data_errors_td").toggle();
      });

      /*
       * Show/Hide Errors: Validation sample images.
       */

      $(".view-validation-sample-images-errors").on("click", function(event){
        $("#error_"+this.id+"_td").toggle();
        $("#error_"+this.id).slideToggle();
      });

      $(".toggle_validation_sample_images_errors").on("click", function(event){
        $(".validation_sample_images_errors").toggle();
        if($(".toggle_validation_sample_images_errors").text() == "expand all errors") {
          $(".toggle_validation_sample_images_errors").text("collapse all errors");
        } else {
          $(".toggle_validation_sample_images_errors").text("expand all errors");
        }
        $(".validation_sample_images_errors_td").toggle();
      });

      /*
       * Show/Hide Errors: Validation sample data.
       */

      $(".view-validation-sample-data-errors").on("click", function(event){
        $("#error_"+this.id+"_td").toggle();
        $("#error_"+this.id).slideToggle();
      });

      $(".toggle_validation_sample_data_errors").on("click", function(event){
        $(".validation_sample_data_errors").toggle();
        if($(".toggle_validation_sample_data_errors").text() == "expand all errors") {
          $(".toggle_validation_sample_data_errors").text("collapse all errors");
        } else {
          $(".toggle_validation_sample_data_errors").text("expand all errors");
        }
        $(".validation_sample_data_errors_td").toggle();
      });

      /*
       * Tooltips.
       */

      $('a').tooltip();
    });



  function nl2br (str, is_xhtml) {
      var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
      return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
  }

  function update_import_log()
  {
    // show the import log
      var log_file = "{{ path_to_this_module }}/library/import_logs/{{ "now"|date("Y-m-d") }}/{{ data.laboratory_data.import_log_id|e }}.txt";
      $(".import-log-container").show();
      
      $.ajax({
        type: "GET"
        //,dataType: "json"
        ,url: log_file
        ,data: {
          "cache_id": "{{ log_cache_id }}"
          }
        ,success: function(data){
          $(".import-log-container").html(nl2br(data)).text();              
          $(".download-log").html('<a href="'+"{{ path_to_this_module }}/library/import_logs/{{ "now"|date("Y-m-d") }}/{{ data.laboratory_data.import_log_id|e }}.txt"+'" target="_blank">Download Log</a>');
        }
      });
 
  }

  $(".import-log-container").on("blur",function(){
      setTimeout(function(){
      // scroll to bottom
       $(".import-log-container").animate({ scrollTop: $(".import-log-container")[0].scrollHeight }, 1000);
      }, 1000);
  });


  function run_log()
  {
     
    setTimeout(function(){
      update_import_log();
      run_log();
      }, 1000);

     setTimeout(function(){
      // scroll to bottom
       $(".import-log-container").animate({ scrollTop: $(".import-log-container")[0].scrollHeight }, 1000);
      }, 5000); 


  }

  if(show_log)
  {
   run_log();
  }

  </script>
{% endblock %}