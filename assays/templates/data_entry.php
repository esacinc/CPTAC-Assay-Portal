{% extends layout_template_name %}
{% block styles_head %}
  {{ parent() }}
  <link type="text/css" rel="stylesheet" href="//cdn.jsdelivr.net/jquery.handsontable/0.8.16/jquery.handsontable.full.css" />
  <link href="/site/library/css/styles.css" rel="stylesheet" type="text/css" />
  <link href="/assays/library/css/styles.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}
{% include 'secondary_navigation.php' %}
<div id="genSlotMainNav">
  {% include 'top_navigation.php' %}
</div>
<div class="row-outer">
  <div class="page-title no-margin">
    <h3>{{ page_title }}</h3>
  </div>
  <div class="row-fluid data-entry-form" style="clear:both;">
    <!-- <form method="POST" class="form-horizontal"> -->

      <div id="proteins_container">
        <h4>Target Protein</h4>
        <p class="muted">This first example includes an instance employing a menu for Species, as well as a dedicated table. 
          Menus are good for selecting one out of a predefined list, whereas dedicated tables are suitable for 
          associating more than one.</p>
        <div id="proteins"></div>
      </div>

      <div id="species_container">
        <h4>Species</h4>
        <div id="species"></div>
      </div>

      <div id="protein_stds_container">
        <h4>Protein STDs</h4>
        <div id="protein_stds"></div>
      </div>

      <div id="peptide_container">
        <h4>Analyte Peptide</h4>
        <p class="muted">(Note: If more than one peptide is entered for a given protein, only the first enry will be recorded.)</p>
        <div id="peptide"></div>
      </div>

      <div id="laboratories_container">
        <h4>Laboratories</h4>
        <div id="laboratories"></div>
      </div>

      <div id="instruments_container">
        <h4>Instruments</h4>
        <div id="instruments"></div>>
      </div>

      <div id="peptide_stds_container">
        <h4>Peptide STDs</h4>
        <div id="peptide_stds"></div>
      </div>

      <div id="assay_parameters_container">
        <h4>Assay Parameters</h4>
        <div id="assay_parameters"></div>
      </div>

      <div id="analytical_validation_of_assay_container">
        <h4>Analytical Validation of Assay</h4>
        <div id="analytical_validation_of_assay"></div>
      </div>

      <button name="dump_all" class="btn btn-small" title="Prints all data sources to Firebug/Chrome Dev Tools">Dump all data to page</button>

      <div id="output">
        
      </div>

    <!-- </form> -->
  </div>
</div>
{% endblock %}
{% block js_bottom %}
  {{ parent() }}
  <script type="text/javascript" src="/{{ core_type }}/javascripts/jquery-handsontable/jquery-handsontable-0.9.1/dist/jquery.handsontable.full.js"></script>
  <script type="text/javascript">
  $(document).ready(function(){

    var data_protein = [];
    var data_peptide = [];
    var data_species = [];
    var data_peptide_stds= [];
    var data_protein_stds = [];
    var data_laboratories = [];
    var data_instruments = [];
    var data_assay_parameters = [];
    var data_analytical_validation_of_assay = [];

    var button_species = '<a href="javascript:void(0);" class="species">Add Species</a>';
    var button_protein_stds = '<a href="javascript:void(0);" class="protein_stds">Add Protein STDs</a>';
    var button_peptide = '<a href="javascript:void(0);" class="peptide">Add Peptide</a>';
    var button_laboratories = '<a href="javascript:void(0);" class="laboratories">Add Laboratories</a>';
    var button_instruments = '<a href="javascript:void(0);" class="instruments">Add Instruments</a>';
    var button_peptide_stds = '<a href="javascript:void(0);" class="peptide_stds">Add Peptide STDs</a>';
    var button_assay_parameters = '<a href="javascript:void(0);" class="assay_parameters">Add Assay Parameters</a>';
    var button_analytical_validation_of_assay = '<a href="javascript:void(0);" class="analytical_validation_of_assay">Add Analytical Validation</a>';

    var button_renderer_species = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_species);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_protein_stds = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_protein_stds);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_peptide = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_peptide);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_laboratories = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_laboratories);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_instruments = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_instruments);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_peptide_stds = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_peptide_stds);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_assay_parameters = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_assay_parameters);
      $(td).empty().append(escaped);
      return td;
    };
    var button_renderer_analytical_validation_of_assay = function (instance, td, row, col, prop, value, cellProperties) {
      var escaped = Handsontable.helper.stringify(button_analytical_validation_of_assay);
      $(td).empty().append(escaped);
      return td;
    };

    $('#proteins').on('mousedown', 'a.species', function(event) {
      event.preventDefault();
      var protein_id = $(this).parent().siblings(":first").text();
      var ht = $('#species').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, protein_id);
      $('#species').attr('data-current-id', protein_id);
      $('#species_container').fadeIn();
    });
    $('#proteins').on('mousedown', 'a.protein_stds', function(event) {
      event.preventDefault();
      var protein_id = $(this).parent().siblings(":first").text();
      var ht = $('#protein_stds').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, protein_id);
      $('#protein_stds').attr('data-current-id', protein_id);
      $('#protein_stds_container').fadeIn();
    });
    $('#protein_stds').on('mousedown', 'a.peptide', function(event) {
      event.preventDefault();
      var protein_id = $(this).parent().parent().find('td:first').text();
      var ht = $('#peptide').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, protein_id);
      $('#peptide').attr('data-current-id', protein_id);
      $('#peptide_container').fadeIn();
    });
    $('#peptide').on('mousedown', 'a.laboratories', function(event) {
      event.preventDefault();
      var peptide_id = $(this).parent().parent().find('td:first').text();
      var ht = $('#laboratories').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, peptide_id);
      $('#laboratories').attr('data-current-id', peptide_id);
      $('#laboratories_container').fadeIn();
    });
    $('#peptide').on('mousedown', 'a.instruments', function(event) {
      event.preventDefault();
      var peptide_id = $(this).parent().parent().find('td:first').text();
      var ht = $('#instruments').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, peptide_id);
      $('#instruments').attr('data-current-id', peptide_id);
      $('#instruments_container').fadeIn();
    });
    $('#peptide').on('mousedown', 'a.peptide_stds', function(event) {
      event.preventDefault();
      var peptide_id = $(this).parent().parent().find('td:first').text();
      var ht = $('#peptide_stds').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, peptide_id);
      $('#peptide_stds').attr('data-current-id', peptide_id);
      $('#peptide_stds_container').fadeIn();
    });
    $('#peptide').on('mousedown', 'a.assay_parameters', function(event) {
      event.preventDefault();
      var peptide_id = $(this).parent().parent().find('td:first').text();
      var ht = $('#assay_parameters').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, peptide_id);
      $('#assay_parameters').attr('data-current-id', peptide_id);
      $('#assay_parameters_container').fadeIn();
    });
    $('#assay_parameters').on('mousedown', 'a.analytical_validation_of_assay', function(event) {
      event.preventDefault();
      var assay_id = $(this).parent().siblings(":first").text();
      var ht = $('#analytical_validation_of_assay').data('handsontable');
      var rowCount = ht.countRows();
      ht.setDataAtCell(rowCount-1, 0, assay_id);
      $('#analytical_validation_of_assay').attr('data-current-id', assay_id);
      $('#analytical_validation_of_assay_container').fadeIn();
    });

    $('#proteins').handsontable({
      data: data_protein
      ,colHeaders: ["Gene (check genenames.org)","Species (select menu)","Species (dedicated table)","Protein STDs"]
      ,colWidths: [280, 140, 160, 120]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      // ,contextMenu: true
      ,columns: [
        {}
        ,{type: 'autocomplete',
          source: ["Mouse","Rabbit","Human","Rat"],
          strict: false}
        ,{ data: button_species, type: {renderer: button_renderer_species}, readOnly: true }
        ,{ data: button_protein_stds, type: {renderer: button_renderer_protein_stds}, readOnly: true }
      ]
      // ,afterRender: function(){
      //   //console.log($('#proteins').find('table td:first'));
      //   $('#proteins').find('table').on('mousedown', 'td', function(event) {
      //     var ht = $('#proteins').data('handsontable');
      //     var rowCount = ht.countRows();
      //     // console.log(rowCount);
      //     ht.setDataAtCell(rowCount-1, 0, rowCount);
      //   });
      // }
      // ,afterCreateRow: function() {
      //   var ht = $('#proteins').data('handsontable');
      //   // ht.setDataAtCell(0, 0, 1);

      //   var rowCount = ht.countRows();
      //   // ht.setDataAtCell(1, 0, 1);
      //   // var data_at_cell = ht.getDataAtCell(rowCount, 0);
      //   console.log(rowCount);
      //   console.log(ht.getDataAtCell(rowCount-1, 0));

      //   // if(data_at_cell == null) {
      //   //   ht.setDataAtCell(rowCount, 0, rowCount);
      //   // }
      // }
    });

    $('#species').handsontable({
      data: data_species
      ,colHeaders: ["Protein ID","Species Name"]
      ,colWidths: [120, 120]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      // ,contextMenu: true
      ,columns: [
        {}
        ,{}
      ]
    });

    $('#protein_stds').handsontable({
      data: data_protein_stds
      ,colHeaders: ["Protein ID","Peptide","Label Type","Site of Label","Protein Vendor","Organism Produced In","Protein Purity","Determination of Purity (method)"]
      ,colWidths: [90, 90, 90, 90, 120, 150, 90, 200]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      // ,contextMenu: true
      ,columns: [
        {}
        ,{ data: button_peptide, type: {renderer: button_renderer_peptide}, readOnly: true }
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
      ]
    });

    $('#peptide').handsontable({
      data: data_peptide
      ,colHeaders: ["Protein ID","Laboratories","&nbsp; Instruments &nbsp;","Peptide STDs","Assay Parameters","Peptide Sequence","Peptide Start","Peptide End","Peptide Molecular Weight","Modification Type","Site of Modification - Peptide","Site of Modification - Protein","Peptide Selection Method","Hydrophobicity","Utility"]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      ,width: 1100
      // ,contextMenu: true
      ,columns: [
        {}
        ,{ data: button_laboratories, type: {renderer: button_renderer_laboratories}, readOnly: true }
        ,{ data: button_instruments, type: {renderer: button_renderer_instruments}, readOnly: true }
        ,{ data: button_peptide_stds, type: {renderer: button_renderer_peptide_stds}, readOnly: true }
        ,{ data: button_assay_parameters, type: {renderer: button_renderer_assay_parameters}, readOnly: true }
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{type: 'autocomplete',
          source: ["in silico (IS)","MS Discovery (DIS)","Literature (LIT)","Digestion of protein standards (DIG)","Public repositories (PR)"],
          strict: true}
        ,{}
        ,{}
      ]
    });

    $('#laboratories').handsontable({
      data: data_laboratories
      ,colHeaders: ["Peptide ID","Laboratory Name"]
      ,colWidths: [120, 120]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      // ,contextMenu: true
      ,columns: [
        {}
        ,{}
      ]
    });

    $('#instruments').handsontable({
      data: data_instruments
      ,colHeaders: ["Peptide ID","Instrument Name"]
      ,colWidths: [120, 120]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      // ,contextMenu: true
      ,columns: [
        {}
        ,{}
      ]
    });

    $('#peptide_stds').handsontable({
      data: data_peptide_stds
      ,colHeaders: ["Peptide ID","Identifier","Host Identifier","Label Type","Peptide Vendor","Peptide Purity","Isotopic Purity"]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      // ,contextMenu: true
      ,columns: [
        {}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
      ]
    });

    $('#assay_parameters').handsontable({
      data: data_assay_parameters
      ,colHeaders: ["Peptide ID","Analytical Validation","Assay Type","Data Type","Internal Standard","Submitting Laboratory","Primary Contact Person","Other Assays Submitted","Publications","Instruments","MS","LC","Column Dimensions","Column Packing","Gradient Length","Flow Rate","Endogenous Detected?","Detected in What Sample?","Endogenous Amount/Concentration"]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      ,width: 1100
      // ,contextMenu: true
      ,columns: [
        {}
        ,{ data: button_analytical_validation_of_assay, type: {renderer: button_renderer_analytical_validation_of_assay}, readOnly: true }
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
      ]
    });

    $('#analytical_validation_of_assay').handsontable({
      data: data_analytical_validation_of_assay
      ,colHeaders: ["Assay ID","Response Curve?","Validation Samples?","Matrix","Matrix Amount","Matrix Amount Units","Heavy Spike Level","Number Of Analytes Targeted In Assay","Curve Type","Response Curve Concentrations","Regression Type","Regression Weighting","Regression Slope","Regression Intercept","Regression Coefficient","Number Of Replicates","Description Of Replicates","Slope Std Error","Intercept Std Error","Precursor M/z","Precursor Z","Spectral Library (y/n)","Spectral Library Source","Quantifier Transition","Quantifier Transition Z","Quantifier Transition Ce","Quantifier Selection Method","Qualifier Transition 1","Qualifier Transition N","LOD/LOQ Method Type"]
      ,rowHeaders: true
      ,minSpareRows: 1
      ,manualColumnResize: true
      ,currentRowClassName: 'currentRow'
      ,currentColClassName: 'currentCol'
      ,autoWrapRow: true
      ,autoWrapCol: true
      ,columnSorting: true
      ,width: 1100
      // ,contextMenu: true
      ,columns: [
        {}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
        ,{}
      ]
    });


    $('body').on('click', 'button[name=dump]', function () {
      var dump = $(this).data('dump');
      var $container = $(dump);
      console.log('data of ' + dump, $container.handsontable('getData'));
    });

    $('body').on('click', 'button[name=dump_all]', function () {
      // console.log($('#proteins').handsontable('getData'));
      // console.log($('#species').handsontable('getData'));
      // console.log($('#protein_stds').handsontable('getData'));
      // console.log($('#peptide').handsontable('getData'));
      // console.log($('#laboratories').handsontable('getData'));
      // console.log($('#instruments').handsontable('getData'));
      // console.log($('#peptide_stds').handsontable('getData'));
      // console.log($('#assay_parameters').handsontable('getData'));
      // console.log($('#analytical_validation_of_assay').handsontable('getData'));
      $('#output').empty();
      $('#output').append( '<pre>'+JSON.stringify($('#proteins').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#species').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#protein_stds').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#peptide').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#laboratories').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#instruments').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#peptide_stds').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#assay_parameters').handsontable('getData'))+'</pre><br /><br />' );
      $('#output').append( '<pre>'+JSON.stringify($('#analytical_validation_of_assay').handsontable('getData'))+'</pre><br /><br />' );
    });

    //$('#proteins').data('handsontable').setDataAtCell(0, 0, 1);

    // $('#example1 table').addClass('table table-hover table-striped');

  });
  </script>
{% endblock %}