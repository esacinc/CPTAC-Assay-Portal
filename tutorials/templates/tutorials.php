{% extends layout_template_name %}
{% block content %}
<div class="row-fluid">


  <div class="row-fluid">
    <div class="span12">
      <h3>Before You Begin</h3>
      <p class="well lead">Before you begin, you must have data already uploaded to <a href="https://panoramaweb.org/" target="_blank">Panorama</a>. If you haven't already done so, please review the <a href="/uploading-instructions/">Instructions for Uploading Data to the Assay Portal</a> document, and come back after data has been uploaded to Panorama.</p>

      <h3>Add Import Metadata</h3>
      <p class="well lead">Adding Import Metadata is the first step, and is required before executing any imports. The information entered in this interface can be revisisted and modified at any time.</p>
      <img src="{{ path_to_this_module }}/library/images/01_import_metadata_01.png" alt="Add Import Metadata screenshot" class="img-responsive">

      <h3>Add Import Metadata - Panorama Directory</h3>
      <p class="well lead">The value entered into the "Panorama Directory" field is the "glue" between the Portal import processes and the data out on Panorama. For this reason, it's imperative to get this field right. Once set, this field <strong>should not</strong> be modified, unless the directory has changed out on Panorama.</p>
      <img src="{{ path_to_this_module }}/library/images/01_import_metadata_02.png" alt="Add Import Metadata - Panorama Directory screenshot" class="img-responsive">

      <h3>Browse Imports</h3>
      <p class="well lead">Once the Import Metadata has been entered, the ability to execute imports will become available on the
        "Browse Imports" page. You may also manage the Import Metadata from this page at any time, before and after the import has been executed.</p>
      <img src="{{ path_to_this_module }}/library/images/02_browse_imports.png" alt="Add Import Metadata - Panorama Directory screenshot" class="img-responsive">

      <h3>Execute Import</h3>
      <p class="well lead">From here, imports can be executed. Imports can be executed only after the "Import Metadata" is entered.
        The option to <strong>"Execute a Test Run"</strong> is available, which is a limited test of 5 assays (highly recommended).
        Please note, the 5 assays are automatically selected and cannot be determined by the user. If issues are
        present after the test import has been executed, the "Health Report" section will be populated with any problematic data
        and imagery. Problematic data and imagery may occur due to (1) faulty data out on Panorama, or (2) sporatic performance issues
        with Panorama's API.</p>
      <img src="{{ path_to_this_module }}/library/images/02_execute_import.png" alt="Execute Import screenshot" class="img-responsive">

      <h3>Fixing Issues and Deleting an Import</h3>
      <p class="well lead">To fix issues, send an "Error Report" to the CPTAC Portal Administrators using the <strong>"Send Error Report"</strong>
        button. Another way to fix issues is the integrated ability to "re-execute" the import against the problematic images and data.
        In some cases, may take 2-3 runs to fix all existing issues. If issues persist,
        please feel free to <a href="https://assays.cancer.gov/support/" target="_blank">submit a support ticket</a>.
        In the scenario where all options have been exhausted, the import can be completely deleted,
        without affecting other existing data on the CPTAC Assay Portal. In other words, if data is wrong out on
        Panorama, the issues should be fixed there first. Please note, it is not possible to "edit" or "modify" any imported
        data on the Assay Portal, aside from the Metadata.</p>
      <img src="{{ path_to_this_module }}/library/images/03_delete_this_import.png" alt="Delete Import screenshot" class="img-responsive">

      <h3>Preview Assays - Landing</h3>
      <p class="well lead">After the import has been successfully executed, assays can be previewed as they would appear on the
        Portal. These assays are not public until the <strong>NCI Assay Review Team</strong> has marked them as approved.</p>
      <img src="{{ path_to_this_module }}/library/images/04_preview_assays_01.png" alt="Preview Assays - Landing screenshot" class="img-responsive">

      <h3>Preview Assays - Interface</h3>
      <p class="well lead">The status of assays are depicted by color-coded rows. A row in red means it is disapproved, while a row
        in green means it has been approved. If a row is neither red or green, it means it hasn't been reviewed
        by the <strong>NCI Assay Review Team</strong>.</p>
      <img src="{{ path_to_this_module }}/library/images/05_preview_assays_02.png" alt="Preview Assays - Interface screenshot" class="img-responsive">

      <h3>Preview Assays - Notes</h3>
      <p class="well lead">Notes can be added by both data providers and the NCI Assay Review Team. Notes provide the means to discuss individual
        assays, during the review process.</p>
      <img src="{{ path_to_this_module }}/library/images/06_preview_assays_03.png" alt="Preview Assays - Notes screenshot" class="img-responsive">

    </div>
  </div>

</div>
{% endblock %}
{% block js_bottom %}
  {{ parent() }}

  <script type="text/javascript">

    $(document).ready(function(){

      // placeholder

    });

    /*
     * Functions
     */

  </script>

{% endblock %}
