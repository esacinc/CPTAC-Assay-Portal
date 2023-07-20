{% extends public_layout_template_name %}
{% block meta_tags %}
{{ parent() }}
<meta name="description"
      content="A community web-based repository for well-characterized quantitative targeted proteomics assays."/>
<meta name="keywords"
      content="cancer, assay, protein, gene, peptide, sequence, characterized, tumor, normal tissue, biospecimens, genomic, proteomic, data, analysis, discovery, biomarker, testing, verification, uniprot, panorama, entrez"/>
<meta name="author"
      content="ABCC's Scientific Web Programming Group, Frederick National Laboratory for Cancer Research (FNLCR)"/>
{% endblock %}
{% block styles_head %}
{{ parent() }}
<link href="/{{ core_type }}/javascripts/DataTables-SWPG/css/bootstrap_datatables.css" rel="stylesheet"
      type="text/css"/>
<link href="/{{ core_type }}/javascripts/DataTables-1.9.0/extras/ColVis/media/css/ColVis.css" rel="stylesheet"
      type="text/css"/>
<link href="//cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="/assays/library/css/public_browse.css"/>
{% endblock %}
{% block content %}

<div>
  <div class="row-fluid">
    <div class="page-title span2">
        <h1 style="font-size:30px;">Assay Portal</h1>
        <p class="genSitePrintButton" style="margin-top:20px;margin-right:25px"><i class="fa fa-print" aria-hidden="true"></i><a href="javascript:window.print()"> PRINT</a></p>
    </div>
    <div class="page-title span8" style="padding-left:35px;">
      <br>
      <div style="font-weight:bolder;">
        <div style="text-align:center;font-weight:bold;text-decoration:underline;line-height: 30px;">Please include the following statement when referencing the CPTAC Assay Portal</div>
        <div style="text-align:center;font-size:13px;line-height: 30px;">We would like to acknowledge the National Cancer Institute’s Clinical Proteomic Tumor Analysis Consortium (CPTAC) Assay Portal (assays.cancer.gov)
        for developing assays and establishing criteria for the assays described in this publication.</div>
      </div>

    </div>

    <div class="page-title span2 pull-right" style="text-align:right;">
        <img src="{{ site_logo }}"  width="101" height="101" alt="CPTAC logo">
    </div>
  </div> <!-- row_fluid_page_title -->
 {% for single_datatable in datatables %}
  <div class="row-fluid">
    <div id="sidebar_nav" class="span2">


	      <div class="searches">
            <div id="sidebar_columns"></div>
        </div>
        <hr>
        <div class="show_hide_columns">
        </div>
        <hr>
        <div id="sidebar_csv">
           <form action="/assays/export_csv" method="post" name="export">
             <input type="submit" id="table_csv" value="Download Table (CSV)" />
              <input type="hidden" name="csv_filter" id="csv_filter" value="" />
           </form>
        </div>
        <hr>

        <div class="nav_menu" ><div class="sidenav_header">Filter Assays</div><i class="fa fa-chevron-down"></i></div>
        <hr>
        <div class="sub-filter">
           <div id="kegg_filter"><div class="sidenav_header">Kegg Pathway</div><i class="fa fa-chevron-down"></i></div>
           <div id="kegg_toggle">
                <ul class="nav nav-list">

                    {# -------------------------
                    <li class="nav-header">Gene Ontology</li>
                    <li>
                        <select class="multiselect single_sidebar_filter" data-filter_handle="gene_ontology_filter"
                                multiple="multiple" id="select_gene_ontology">
                            {% for single_gene_ontology in gene_ontology %}
                            <option value="{{ single_gene_ontology.gene_ontology_id }}">{{ single_gene_ontology.name
                                }}
                            </option>
                            {% endfor %}
                        </select>
                    </li>
                    <li>
                        <hr>
                    </li>
                    ------------------------- #}


                    <li>
                        <select id="kegg_select" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;">
                            {% for single_kegg_data in kegg_data %}

                            {% set parent_class = '' %}
                            {% set disabled = '' %}
                            {% if single_kegg_data.indent == '--' %}
                            {% set parent_class = ' class=parent' %}
                            {% set disabled = ' disabled' %}
                            {% endif %}
                            {% if single_kegg_data.indent == '' %}
                            {% set parent_class = ' class=parent' %}
                            {% set disabled = ' disabled' %}
                            {% endif %}
                            {% if single_kegg_data.enabled == 0 %}
                            {% set disabled = ' disabled' %}
                            {% endif %}

                            <option value="{{ single_kegg_data.kegg_id }}" {{ parent_class }}{{ disabled }}>{{
                                single_kegg_data.indent }} {{ single_kegg_data.name }}
                            </option>
                            {% endfor %}

                        </select>
                    </li>
                    <li class="muted">Data Source: <a href="http://www.genome.jp/kegg/pathway.html"
                                                      target="_blank">KEGG</a></li>
                </ul>
           </div>
            <hr>
            <div id="wikipathway_filter"><div class="sidenav_header">Wiki Pathway</div><i class="fa fa-chevron-down"></i></div>
            <div id="wikipathway_toggle" class="filter">
                <ul class="nav nav-list">
                    <li>
                        <select id="select_wikipathway" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;"  data-filter_handle="wikipathway_filter">
                            {% for single_wikipathway in wikipathways %}

                            {% set parent_class = '' %}
                            {% set disabled = '' %}
                            {% if single_wikipathway.indent == '--' %}
                            {% set parent_class = ' class=parent' %}
                            {% set disabled = ' disabled' %}
                            {% endif %}
                            {% if single_wikipathway.indent == '' %}
                            {% set parent_class = ' class=parent' %}
                            {% set disabled = ' disabled' %}
                            {% endif %}
                            {% if single_wikipathway.enabled == 0 %}
                            {% set disabled = ' disabled' %}
                            {% endif %}

                            <option value="{{ single_wikipathway.wikipathway_id }}" {{ parent_class }}{{ disabled }}>
                                {{single_wikipathway.indent }}  {{ single_wikipathway.name }}
                            </option>
                            {% endfor %}
                        </select>
                    </li>

                    <li class="muted">Data Source: <a href="http://wikipathways.org"
                                                      target="_blank">WikiPathways</a>
                        <a href="https://www.wikipathways.org/index.php/Portal:CPTAC"
                           target="_blank">CPTAC WikiPathways</a></li></li>
                </ul>
            </div>
           <hr>
           <div class="sidebar_chromosome"><div class="sidenav_header">Chromosome Number</div><i class="fa fa-chevron-down"></i></div>
           <div id="cromosome_filter" class="filter">
               <ul class="nav nav-list">
                    <li class="nav-header">Find assays to proteins encoded in a specific chromosomal region</li>

                    <li>
                        <select id="select_cromosome" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;"  data-filter_handle="chromosome_filter">
                            <!-- <option value="0">None</option> -->
                            {% for single_chromosome_number in chromosome_numbers %}
                            <option value="{{ single_chromosome_number }}">{{ single_chromosome_number }}</option>
                            {% endfor %}
                        </select>
                        <i class="icon-question-sign" title="e.g. 1-22"></i>
                    </li>
                </ul>
            </div>
            <hr>
            <div class="sidebar_chromosome_location"><div class="sidenav_header">Chromosomal Location</div><i class="fa fa-chevron-down"></i></div>
            <div id="cromosome_location_filter" class="filter">
                <ul class="nav nav-list">
                    <li>
                        <input type="text" class="single_sidebar_filter select_cromosomal_location"
                               data-filter_handle="chromosomal_location_start_filter" placeholder="Start"
                               id="cromosomal_location_start">
                        <i class="icon-question-sign" title="e.g. 8768444" ></i>
                    </li>
                    <li>
                        <input type="text" class="single_sidebar_filter select_cromosomal_location"
                               data-filter_handle="chromosomal_location_stop_filter" placeholder="Stop"
                               id="cromosomal_location_stop">
                        <i class="icon-question-sign" title="e.g. 8878432"></i>
                    </li>
                    <li class="muted">Data Source: <a href="http://www.uniprot.org/"
                                                      target="_blank">Uniprot</a></li>
                </ul>
            </div> <!-- chromosome_location_filter -->
            <hr>
            <div class="cptac_type_filter"><div class="sidenav_header">CPTAC Prefix</div><i class="fa fa-chevron-down"></i></div>
            <div id="cptac_type_toggle" class="filter">
               <ul class="nav nav-list">
                   <li>
                       <select id="select_cptac_type" data-placeholder="Select" multiple class="chosen-select"
                              style="width:100%!important;"  data-filter_handle="cptac_type_filter">

                              <option value="CPTAC">CPTAC</option>
                              <option value="non-CPTAC">non-CPTAC</option>

                        </select>
                    </li>
               </ul>
            </div>
            <hr>
            <div class="assay_type_filter"><div class="sidenav_header">Assay Type</div><i class="fa fa-chevron-down"></i></div>
            <div id="assay_type_toggle" class="filter">
                 <ul class="nav nav-list">
                     <li>
                         <select id="select_assay_type" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;"  data-filter_handle="assay_type_filter">
                            {% for single_assay_type in assay_types %}
                                <option value="{{ single_assay_type.assay_type }}">{{ single_assay_type.assay_type }}</option>
                            {% endfor %}
                          </select>
                      </li>
                 </ul>
            </div>
            <hr>
            <div class="peptide_standard_filter"><div class="sidenav_header">Peptide Standard Purity</div><i class="fa fa-chevron-down"></i></div>

            <div id="peptide_standard_toggle" class="filter">
              <ul class="nav nav-list">
                    <li>
                        <select id="select_peptide_standard_purity" data-placeholder="Select" multiple class="chosen-select"
                                            style="width:100%!important;"  data-filter_handle="peptide_standard_purity_filter">
                            {% for single_peptide_standard_purity in peptide_standard_purity %}
                            <option value="{{ single_peptide_standard_purity.peptide_standard_purity_types_id }}">{{ single_peptide_standard_purity.type }}
                            </option>
                            {% endfor %}
                        </select>
                    </li>
                </ul>
            </div>
            <hr>
            <div id="species_filter"><div class="sidenav_header">Species</div><i class="fa fa-chevron-down"></i></div>
            <div id="species_toggle" class="filter">
                <ul class="nav nav-list">
                    <li class="nav-header">Find assays that work in a particular species</li>
                    <li>
                       <select id="select_species" data-placeholder="Select" multiple class="chosen-select"
                                    style="width:100%!important;"  data-filter_handle="species_filter">
                            {% for single_protein_species in protein_species %}
                            <option value="{{ single_protein_species.protein_species_label }}">{{
                                single_protein_species.organism_scientific }} ({{ single_protein_species.organism_common }})
                            </option>
                            {% endfor %}
                        </select>
                    </li>
                </ul>
            </div>
            <hr>
            <div class="laboratory_filter"><div class="sidenav_header">Laboratory</div><i class="fa fa-chevron-down"></i></div>
                 <div id="lab_toggle" class="filter">
                <ul class="nav nav-list">
                    <li class="nav-header">Find assays in particular lab</li>
                    <li>
                      <select id="select_lab" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;"  data-filter_handle="lab_filter">
                            {% for single_lab in labs %}
                            <option value="{{ single_lab.name }}">{{single_lab.name }}</option>
                            {% endfor %}
                        </select>
                    </li>
                </ul>
            </div>
            <hr>
            <!-- commenting out until discussing more with NCI and FHCRC
            <div class="certification_filter"><div class="sidenav_header">Environment</div><i class="fa fa-chevron-down"></i></div>
            <div id="certification_toggle" class="filter">
                 <ul class="nav nav-list">
                     <li>
                         <select id="select_certification" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;"  data-filter_handle="certification_filter">

                                <option value="CLIA">CLIA</option>


                          </select>
                      </li>
                 </ul>
            </div>
            <hr>
            -->

            <div class="antibody_filter"><div class="sidenav_header">Assays with Antibodies on CPTAC Antibody Portal (Catalog ID)</div><i class="fa fa-chevron-down"></i></div>
            <div id="antibody_toggle" class="filter">
                 <ul class="nav nav-list">
                     <li>
                         <select id="select_antibody" data-placeholder="Select" multiple class="chosen-select"
                                style="width:100%!important;"  data-filter_handle="antibody_filter">

                                {% for single_antibody in assays_with_antibodies %}
                                <option value="{{ single_antibody.cptc_catalog_id }}">{{single_antibody.cptc_catalog_id }}</option>
                                {% endfor %}


                          </select>
                      </li>
                 </ul>
            </div>



            {# -------------------------
            <div class="multiplex_filter"><div class="sidenav_header">Multiplex Panels</div><i class="fa fa-chevron-down"></i></div>
              <div id="multiplex_toggle" class="filter">
                <ul class="nav nav-list">
                    <li class="nav-header">Find assays in particular panel</li>
                    <li>
                      <select id="panel_filter" data-placeholder="Select" multiple class="chosen-select"
                               style="width:100%!important;"  data-filter_handle="panel_filter">

                            {% for single_panel in assay_panels %}
                            <option value="{{ single_panel.multiplex_panel_id }}">{{single_panel.name }}-{{single_panel.panel_description}}</option>
                            {% endfor %}
                        </select>
                    </li>
                 </ul>
            </div>
            <hr>
            ------------------------- #}
            <!--
            <hr>
            <div class="biogrid_filter"><div class="sidenav_header">BioGrid</div><i class="fa fa-chevron-down"></i></div>
            <div id="biogrid_toggle" class="filter">
             <ul class="nav nav-list">
                    <li class="nav-header">Find assays to quantify proteins that interact with the following
                        protein(s):
                    </li>
                    <li>
                        <input data-filter_handle="protein_interactions_filter" id="select_protein_interactions"
                               name="protein_interactions">
                    </li>
                    <li class="muted">Data Source: <a href="http://thebiogrid.org/" target="_blank">BioGRID</a></li>
              </ul>
            </div>
            -->
        </div>
        <div class="sub-filter-divide"><hr></div>
        <div class="nav_clear">
            <a id="clear_all"><i class="fa fa-times" aria-hidden="true"></i> Clear All Filters</a>
        </div>
        <hr>


        <div class="statistics"><div class="sidenav_header">Statistics</div></div>
        <div class="container" style="width:100%;">
            <table class="statistics-table">
               <tbody class="statistics-table-body">
                  <tr><td class="statistics-table-left">{{statistics.total_assays}}</td><td class="statistics-table-right">Assays</td></tr>
                  <tr><td class="statistics-table-left">{{statistics.unique_peptides}}</td><td class="statistics-table-right">Unique Peptides</td></tr>
                  <tr><td class="statistics-table-left">{{statistics.unique_proteins}}</td><td class="statistics-table-right">Unique Proteins</td></tr>
               </tbody>
            </table>
        </div>


    </div> <!-- sidebar_nav -->

    <div id="browse_table_wrapper" class="span10">

        <div id="kegg_wrapper" class="kegg_table">
            <div id="tabs">
                <ul>

                </ul>
            </div>

        </div>

        <table id="browse_table" cellpadding="0" cellspacing="0" border="0" class="table table-bordered" style="width:100%">
                    <thead>
                    <tr>
                        {% for key, field in single_datatable.fields %}
                        {% if field.label != 'Gene' %}
                        <th>
                            <div class="th_header_text" style="text-align:center">{{ field.label }}</div>
                        </th>
                        {% endif %}
                        {% endfor %}
                        <th>
                            <div class="th_header_text" style="text-align:center">Uniprot accession id</div>
                        </th>
                    </tr>
                    </thead>
                </table>
        </div>


    {% endfor %}

    </div>
    </div> <!-- row_fluid -->
    <div id="view_protein_modal" class="modal hide fade modal-kegg-dialog" tabindex="-1" role="dialog" aria-labelledby="viewNotesModalLabel" aria-hidden="true">
    <div class="modal-header">
    </div>
    <div class="modal-body">
        <div id="assays_container">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Portal ID</th>
                    <th>Peptide Sequence</th>
                    <th>Modification Type</th>
                    <th>Protein - Site of Modification</th>
                    <th>Assay Type</th>
                    <th>Matrix</th>
                </tr>
                </thead>
                <tbody id="modal-assays-table-body">
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-small" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<div class="modal hide fade modal-kegg" id="keggZoonInModal" style="display: none;">
    <div class="modal-header">
        <a data-dismiss="modal" class="close">×</a>
        <h3 id='dialog-heading'></h3>
    </div>
    <div class="modal-body">
        <div id="dialog-data"></div>
    </div>
    <div class="modal-footer">
        <a data-dismiss="modal" class="btn btn-small" >Close</a>
    </div>
</div>
</div> <!-- row_outer -->
{% endblock %}
{% block js_bottom %}
{{ parent() }}



<script type="text/javascript"
        src="/{{ core_type }}/slim_framework/plugins/bootstrap-multiselect/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>


<script type="text/javascript" src="{{ path_to_this_module }}/library/js/tag-it.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.jquery.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.20/b-1.6.1/b-colvis-1.6.1/datatables.min.js"></script>

<script src="https://cdn.datatables.net/rowgroup/1.1.1/js/dataTables.rowGroup.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.20/api/fnFilterClear.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $(".DataUseContent").hide();
        $(".DataUseButton").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $(".DataUseContent").toggle();
        });

        var post_data = {};
        var table = $('#browse_table').DataTable( {
              //dom: "<'top'Bflrtip>rt<'clear'>",
              dom: "<'row'B><<'span4'l><'span4'i><'span4 row_search'f>><<'span3 datatables_bulk_actions'><'span3'r><'span5 pull-right'p>>t<'row'<'span6 pull-right'p>>",

              buttons: [{
                  extend: 'colvis',
                  text: 'Show Hide/Columns',
                  columns: ':gt(0),:gt(18)'
              }],
              ajax:{
                 url: '{{ path_to_this_module }}/datatables_browse_assays',
                 type: 'POST',
                "data": function ( d ) {
                     var post_data = {};
                     var assay_type_filter = [];
                     var cptac_type_filter = [];
                     $.each($("#select_cptac_type option:selected"), function(){
                         cptac_type_filter.push($(this).val());
                    });
                    post_data['cptac_type_filter'] = cptac_type_filter;

                     $.each($("#select_assay_type option:selected"), function(){
                          assay_type_filter.push($(this).val());
                     });
                     post_data['assay_type_filter'] = assay_type_filter;

                     var certification_filter = [];
                     $.each($("#select_certification option:selected"), function(){
                          certification_filter.push($(this).val());
                     });
                     post_data['certification_filter'] = certification_filter;

                     var antibody_filter = [];
                     $.each($("#select_antibody option:selected"), function(){
                          antibody_filter.push($(this).val());
                     });
                     post_data['antibody_filter'] = antibody_filter;

                    //species filter
                    var species_filter = [];
                    $.each($("#select_species option:selected"), function(){
                         species_filter.push($(this).val());
                    });
                    post_data['species_filter'] = species_filter;

                    //peptide_standard_purity
                    var peptide_standard_purity_filter = [];
                    $.each($("#select_peptide_standard_purity option:selected"), function(){
                         peptide_standard_purity_filter.push($(this).val());
                    });
                    post_data['peptide_standard_purity_filter'] = peptide_standard_purity_filter;

                    //lab filter
                    var lab_filter = [];
                    $.each($("#select_lab option:selected"), function(){
                        lab_filter.push($(this).val());
                    });
                    post_data['lab_filter'] = lab_filter;

                    //chromosome_filter
                    var chromosome_filter = [];
                    $.each($("#select_cromosome option:selected"), function(){
                        chromosome_filter.push($(this).val());
                    });
                    post_data['chromosome_filter'] = chromosome_filter;

                    post_data['chromosomal_location_start_filter'] = $("#cromosomal_location_start").val();
                    post_data['chromosomal_location_stop_filter'] = $("#cromosomal_location_stop").val();


                    //kegg_filter
                    var kegg_pathways_filter = [];
                    $.each($("#kegg_select option:selected"), function(){
                        kegg_pathways_filter.push($(this).val());
                    });
                    post_data['kegg_pathways_filter'] = kegg_pathways_filter;

                    //kegg_filter
                    var wiki_pathways_filter = [];
                    $.each($("#select_wikipathway option:selected"), function(){
                        wiki_pathways_filter.push($(this).val());
                    });
                    post_data['wiki_pathways_filter'] = wiki_pathways_filter;
                    console.log(post_data);
                    return $.extend( {}, d, post_data);
              },
         },
              "columns": [
                  { "data": "gene_symbol",
                    "visible": false},
                  { "data": "peptide_sequence",
                    "visible": true},
                  { "data": "laboratory_name",
                    "visible": true},
                  { "data": "peptide_start",
                    "visible": false},
                  { "data": "peptide_end",
                    "visible": false },
                  { "data": "modification",
                    "visible": true},
                  { "data": "assay_type",
                    "visible": true},
                  { "data": "matrix",
                    "visible": true},
                  { "data": "hydrophobicity",
                    "visible": false},
                  { "data": "site_of_modification_protein",
                    "visible": false},
                  { "data": "protein_species_label",
                    "visible": false},
                  { "data": "peptide_standard_purity",
                    "visible": false},
                  { "data": "instrument",
                    "visible": false},
                  { "data": "endogenous_detected",
                    "visible": false},
                  { "data": "med_total_CV",
                    "visible": false},
                  { "data": "cptac_id",
                    "visible": true},
                  { "data": "uniprot_protein_name",
                    "visible": true},
                  { "data": "uniprot_gene_synonym",
                    "visible": false},
                  { "data": "uniprot",
                    "visible": false}
                ],
              "columnDefs": [
                 { "visible": false, "targets": 0, "orderData": [0] },
                 { "visible": false, "targets": 18 }
              ],

              "displayLength": 25,
              "drawCallback": function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    console.log(rows);
                    var last=null;
                    api.column(0, {page:'current'} ).data().each( function ( group, i ) {
                    console.log(table.columns(':visible').count());
                    if ( last !== group ) {
                    $(rows).eq( i ).before(
                    '<tr class="group" style="background-color:grey"><td colspan="'+table.columns().count()+'">'+group+' - Uniprot Accession ID - '+api.column(18, {page:'current'} ).data()[i]+'</td></tr>'
                    );
                    last = group;
                    } else {
                      $(rows).eq( i ).before(
                      '<tr class="group" style="background-color:grey"><td colspan="'+table.columns().count()+'"> - Uniprot Accession ID - '+api.column(18, {page:'current'} ).data()[i]+'</td></tr>'
                      );
                    }
              } );
              },
              "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                  var table_datas = $(nRow).find('td');


                  // Set the last sequence
                  lastSequence = aData.peptide_sequence;
                  var this_cptac_id = aData.cptac_id;
                  $(table_datas).each(function(index, single) {
                      $(this).on('click', function (event) {
                          if (!$(this).hasClass("no_row_click") && !$(event.target).is("a") && !$(this).hasClass("dataTables_empty")) {
                              window.location.href = this_cptac_id;
                          }
                      });
                  });
              }
    });

   // Order by the grouping
   $('#browse_table tbody').on( 'click', 'tr.group', function () {
       var currentOrder = table.order()[0];
       if ( currentOrder[0] === 2 && currentOrder[1] === 'asc' ) {
           table.order( [ 0, 'desc' ] ).draw();
       } else {
           table.order( [ 0, 'asc' ] ).draw();
       }
   });




 $('.dt-button').detach().appendTo('.show_hide_columns');

 $('.dt-button.buttons-columnVisibility.active span').on( 'click', function () {
    console.log("clicked");
  });


 $('.dataTables_filter').detach().appendTo('.searches');
        $(".csv_menu").hide();
        $(".sub-filter").hide();
        $(".sub-filter .fa").hide();
        $(".sub-filter-divide").hide();
        $("#cromosome_location_filter").hide();
        $("#cromosome_filter").hide();
        $("#cptac_type_toggle").hide();
        $("#certification_toggle").hide();
        $("#antibody_toggle").hide();
        $("#peptide_standard_toggle").hide();
        $("#assay_type_toggle").hide();
        $("#species_toggle").hide();
        $("#kegg_toggle").hide();
        $("#wikipathway_toggle").hide();
        $("#biogrid_toggle").hide();
        $("#lab_toggle").hide();
        $("#multiplex_toggle").hide();
        $( "div.nav_menu i#filter_arrow" ).toggleClass('fa fa-chevron-down fa fa-chevron-up' );
        $(".nav_menu").click(function(){
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $(".sub-filter .fa").show();
            $(".sub-filter").toggle();
            $(".sub-filter-divide").toggle();
        });
        $(".nav_search").click(function(){
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $(".searches").toggle();
        });
        $(".download_csv").click(function(){
            $(".csv_menu").toggle();
        });
        $(".sidebar_chromosome").click(function(){
            $("#cromosome_filter").toggle();
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
        });
        $(".sidebar_chromosome_location").click(function(){
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#cromosome_location_filter").toggle();
        });
        $(".cptac_type_filter").click(function(){
           $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
          $("#cptac_type_toggle").toggle();
        });
        $(".assay_type_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#assay_type_toggle").toggle();
        });
        $(".certification_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#certification_toggle").toggle();
        });

        $(".antibody_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#antibody_toggle").toggle();
        });
        $("#species_filter").click(function () {
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#species_toggle").toggle();
        });
        $("#kegg_filter").click(function () {
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#kegg_toggle").toggle();
        });
        $("#wikipathway_filter").click(function () {
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#wikipathway_toggle").toggle();
        });
         $(".peptide_standard_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#peptide_standard_toggle").toggle();
         });
         $(".biogrid_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#biogrid_toggle").toggle();
         });
         $(".laboratory_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#lab_toggle").toggle();
         });
         $(".multiplex_filter").click(function(){
             $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            $("#multiplex_toggle").toggle();
         });
        $('#show_side_nav').remove();

        var tabCounter = 0;

        var datatables_data = JSON.parse('{{ datatables|json_encode|raw }}');

        var wikipathway_id = '{{ wikipathway_id }}';


        $("#table_csv").click(function(){
               var searchstring = $('#sidebar_search_box').val();
               post_data['search_string'] = searchstring;
               //console.log(post_data);

               $("#csv_filter").val(JSON.stringify(post_data));
        });

        // Clear all filter selections
        $('#clear_all').on('click', function() {
            clearAll();
        });


        // Multiselects
        $('.multiselect').multiselect({
            buttonClass: 'btn',
            buttonWidth: '100%',
            buttonContainer: '<div class="btn-group" style="white-space:normal;"/>',
            maxHeight: false,
            buttonText: function (options) {
                if (options.length == 0) {
                    return 'Include All <b class="caret"></b>';
                }
                else if (options.length > 3) {
                    return options.length + ' selected  <b class="caret"></b>';
                }
                else {
                    var selected = '';
                    options.each(function () {
                        selected += $(this).text() + ', ';
                    });
                    return '<span class="filter-button-text">' + selected.substr(0, selected.length - 2) + '</span> <b class="caret"></b>';
                }
            }
            , onChange: function (element, checked) {
                $('#browse_table').dataTable().fnDraw();
            }
        });

        $('.dropdown-multiselect').multiselect({
            buttonWidth: '100%',
            buttonContainer: '<div class="btn-group" />',
            maxHeight: false,
            buttonText: function (options) {
                if (options.length == 0) {
                    return '<b class="caret"></b>';
                }
                else if (options.length > 3) {
                    return options.length + ' selected  <b class="caret"></b>';
                }
                else {
                    var selected = '';
                    options.each(function () {
                        selected += $(this).text() + ', ';
                    });
                    return '<span class="filter-button-text">' + selected.substr(0, selected.length - 2) + '</span> <b class="caret"></b>';
                }
            }
            , onChange: function (element, checked) {
                $('#browse_table').dataTable().fnDraw();
            }
        });

        // Protein interactions
        $("#select_protein_interactions").tagit({
            removeConfirmation: true,
            maxLength: 6,
            afterTagAdded: function (event, ui) {
                $('#browse_table').dataTable().fnDraw();
            },
            afterTagRemoved: function (event, ui) {
                $('#browse_table').dataTable().fnDraw();
            }
        });



        $("#cromosomal_location_start, #cromosomal_location_stop").on('keyup', function () {
            $('#browse_table').DataTable().ajax.reload();
        });

        $('.ui-autocomplete-input').after(' <i class="icon-question-sign" title="Hit the return/enter key to enter each protein"></i>');

        //multiplex_panel_filter
        $("#panel_filter").chosen();
        $('#panel_filter').on('change', function (evt, params) {
            $(".search-choice span").each(function () {
              $('#browse_table').dataTable().fnDraw();
            });
        });

        //lab_chosen_filter
        $("#select_lab").chosen();
        $('#select_lab').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
              $('#browse_table').DataTable().ajax.reload();
          });
        });


        //species chosen_filter
        $("#select_species").chosen();
        $('#select_species').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
              $('#browse_table').DataTable().ajax.reload();
          });
        });

        //peptide standard purity_filter
        $("#select_peptide_standard_purity").chosen();
        $('#select_peptide_standard_purity').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
            $('#browse_table').DataTable().ajax.reload();
          });
        });

        $("#select_cptac_type").chosen();
        $('#select_cptac_type').on('change', function (evt, params) {
        $(".search-choice span").each(function () {
            $('#browse_table').DataTable().ajax.reload();
          });
        });

        //certification_filter
        $("#select_certification").chosen();
        $('#select_certification').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
              $('#browse_table').DataTable().ajax.reload();
            });
        });

        //certification_filter
        $("#select_antibody").chosen();
        $('#select_antibody').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
              $('#browse_table').DataTable().ajax.reload();
            });
        });

        //assay_type_filter
        $("#select_assay_type").chosen();
        $('#select_assay_type').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
              $('#browse_table').DataTable().ajax.reload();
            });
        });

        //chromosome_filter
        $("#select_cromosome").chosen();
        $('#select_cromosome').on('change', function (evt, params) {
          $(".search-choice span").each(function () {
              $('#browse_table').DataTable().ajax.reload();
          });
        });


        // KEGG
        $("#kegg_select").chosen();
        // Strip the leading indent dashed before displaying in the DOM
        $('#kegg_select').on('change', function (evt, params) {
            var target = $(evt.target),
                currentDataSet = target.val();
            var kegg_strings = [];
            for (index = 0; index < currentDataSet.length; ++index) {
                var kegg_string = $("#kegg_select option[value='" + currentDataSet[index] + "']").text().replace(/-+/, '').replace(/[^a-zA-Z0-9\s]+/g, '');
                kegg_strings.push("tabs-" + kegg_string.replace(/\s/g,''));
                if(params.selected) {
                    addTab(currentDataSet[index], kegg_string, kegg_string);
                }

            }

            if (params.deselected) {
                var tab_lis = tabs.find("li");
                var i = tab_lis.length;
                while(i--) {
                    var li = tab_lis[i];
                    if(!$(li).hasClass('wikipathway')) {
                        if ($.inArray($(li).attr("aria-controls"), kegg_strings) < 0) {
                            removeTab($(li).attr("aria-controls"));
                        }
                    }
                }
            }

            $(".search-choice span").each(function () {
                var kegg_string = $(this).text();
                var kegg_string_stripped = kegg_string.replace(/-+/, '');

                $(this).text(kegg_string_stripped);
                $('#browse_table').DataTable().ajax.reload();

            });
        });

        //wikipathway chosen_filter
        $("#select_wikipathway").chosen();
        // Strip the leading indent dashed before displaying in the DOM
        $('#select_wikipathway').on('change', function (evt, params) {
            var target = $(evt.target),
                currentDataSet = target.val();

            var pathway_strings = [];


            for (index = 0; index < currentDataSet.length; ++index) {
                var pathway_string = $("#select_wikipathway option[value='" + currentDataSet[index] + "']").text().replace(/-+/, '').replace(/[^a-zA-Z0-9\s]+/g, '');
                pathway_strings.push("tabs-wikipathway-" + pathway_string.replace(/\s/g,''));

                if(params.selected) {
                    addTab(currentDataSet[index], pathway_string, pathway_string, true);
                }

            }

            if (params.deselected) {
                var tab_lis = tabs.find("li");
                var i = tab_lis.length;
                while(i--) {
                    var li = tab_lis[i];
                    if($(li).hasClass('wikipathway')) {
                        if ($.inArray($(li).attr("aria-controls"), pathway_strings) < 0) {
                            removeTab($(li).attr("aria-controls"));
                        }
                    }
                }
            }

            $(".search-choice span").each(function () {
                var pathway_string = $(this).text();
                var pathway_string_stripped = pathway_string.replace(/-+/, '');

                $(this).text(pathway_string_stripped);

                //$('#browse_table').dataTable().fnDraw();
                $('#browse_table').DataTable().ajax.reload();

            });
        });



        var oTable = $('#browse_table').dataTable();
        $('#sidebar_search_box').keypress(function(){
                 oTable.fnFilter( $(this).val() );
        });




        $('.colvis').detach().appendTo('.show_hide_columns');


        // Modify the width of the KEGG input
        $('#kegg_select_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#kegg_select_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#kegg_select_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        // Modify the width of the wikipathway input
        $('#select_wikipathway_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_wikipathway_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#select_wikipathway_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        // Modify the width of the Panela input
        $('#panel_filter_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#panel_filter_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#panel_filter_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        // Modify the width of the lab input
        $('#select_lab_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_lab_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#select_lab_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        // Modify the width of the species input
        $('#select_species_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_species_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#select_species_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        //Modify peptide standard purity input
        $('#select_peptide_standard_purity_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_peptide_standard_purity_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#select_peptide_standard_purity_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        $('#select_cptac_type_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_cptac_type_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');
        $('#select_cptac_type_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

        $('#select_certification_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_certification_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');
        $('#select_certification_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});


        $('#select_antibody_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_antibody_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');
        $('#select_antibody_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});



        //Modify assay type input
        $('#select_assay_type_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_assay_type_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#select_assay_type_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});

         //Modify cromosome input
        $('#select_cromosome_chosen').removeAttr('style').attr('style','width:100%!important;margin-top:10px;margin-bottom:10px;');
        $('#select_cromosome_chosen ul li input').removeAttr('style').attr('style', 'width:100%!important;height:25px;');

        $('#select_cromosome_chosen.chosen-container.chosen-container-multi').css({"width":"100%"});


        /* TOUR STUFF */
        $('#tour_button_container').on('click', 'button', function () {
            // Instance the tour
            var tour = new Tour({
                storage: false
            });

            // Add steps
            tour.addSteps([
                {
                    element: "#kegg_filter",
                    title: "KEGG Pathways",
                    content: "Select one or more KEGG pathways from the menu. Each selected pathway is added above the menu as a removable item. To remove a selected pathway, click the 'x' in the upper right corner."
                },
                {
                    element: "#cromosome_filter",
                    title: "Chromosomal Regions",
                    content: "Filter by chromosome numbers and specific chromosomal locations. Select and deselect cromosome numbers (1-22) from the menu. Enter chromosomal location values in the start and stop input fields."
                },
                {
                    element: "#protein_interaction_filter",
                    title: "Protein Interactions",
                    content: "Enter a protein name and press enter. Multiple protein names are allowed. Each entered protein name is added above the menu as a removable item. To remove an entered protein name, click the 'x'."
                },
                {
                    element: ".ColVis",
                    title: "Show / hide columns",
                    content: "Toggle the visibility of table columns.",
                    placement: "left"
                },
                {
                    element: "#browse_table_length label select",
                    title: "Change Amount Viewable",
                    content: "Change the number of viewable records per page.",
                    placement: "left"
                },
                {
                    element: ".dataTables_paginate",
                    title: "Navigate Pages",
                    content: "Navigate the paginated results.",
                    placement: "bottom"
                },
                {
                    element: "#browse_table_filter label input",
                    title: "Free Text Search",
                    content: "Search across all fields in the table, including hidden fields. Searchable fields include: Gene Symbol, Peptide Sequence, UniProt Accession ID, CPTAC ID, Modification Type, and Laboratory Name.",
                    placement: "top"
                },
                {
                    element: "#clear_all",
                    title: "Clear All Filters",
                    content: "Reset all filter settings, including the search field, and return the table view to the default state."
                },
                {
                    element: "#browse_table tbody",
                    title: "View Records",
                    content: "Click on a row to navigate to the details page.",
                    placement: "top"
                }
            ]);
            // Initialize the tour
            tour.init();
            // Start the tour
            tour.start();
        });

        // Tooltips
        $(".icon-question-sign").tooltip();
        $("#browse_table_wrapper .DTTT_container a").attr('title', "Download a CSV of all records displayed on this page. If all records are desired, select 'All' from the 'Show' dropdown menu below.");
        $("#browse_table_wrapper .DTTT_container a").tooltip();

        //KEGG Patway Tabs
        tabTemplate = '<li><a href="#{href}">#{label}</a></li>';

        var tabs = $( "#tabs" ).tabs({
            activate: function( event, ui ) {
                var id = ui.newPanel.attr('id');
                tabs.find(tabs.find( 'ul' )).after(tabs.find( '#' + id ));
            }
        });

        var kegg_wrapper = $("#kegg_wrapper");

        $('#kegg_select_chosen  .chosen-choices .search-choice span').each(function () {
            var kegg_string = $(this).text();
            var kegg_id = $('#kegg_select option').filter(function () { return $(this).html() == kegg_string; }).val();
            var kegg_string_stripped = kegg_string.replace(/-+/, '');


            addTab(kegg_id, kegg_string_stripped, kegg_string_stripped);

            $(this).text(kegg_string_stripped);
            tabCounter++;
        });

        if (tabCounter == 0) {
            tabs.hide();
            kegg_wrapper.hide();
        } else {
            tabs.show();
            kegg_wrapper.show();
        }

        // Actual addTab function: adds new tab using the input from the form above
        function addTab(pathwayId, tabTitle, tabContent, wikipathway) {
            var label = tabTitle || "Tab " + tabCounter;
            var tab_id = tabTitle.replace(/\s/g,'');

            if(wikipathway) {
                tab_id = "wikipathway-" + tab_id;
            }

            var    id = "tabs-" + tab_id || "tabs-" + tabCounter,
                li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) ),
                tabContentHtml = tabContent || "Tab " + tabCounter + " content.";

            if(wikipathway) {
                li.addClass('wikipathway');
            }
            if ($("#" + id ).length != 0) {
                return;
            } else {
                tabCounter++;
            }

            tabs.find( ".ui-tabs-nav" ).append( li );

            tabs.append("<div id='" + id + "'></div>" );

            //d3.select("#" + id).html(getSvg());
            if(wikipathway) {
                jQuery.ajax({
                    type: "GET"
                    , dataType: "json"
                    , url: "{{ path_to_this_module }}/get_wiki_svg"
                    , data: ({wikipathway_id: pathwayId})
                    , success: function (svg_return) {

                        var svg = "<div id='kegg-legend'><p><span style='background:#00FF00; display: block;'></span>Green Boxes have targeted assays<p></div>" + svg_return.svg;
                        svg = svg + "<div id='wikipathway-source' class='muted'>" +
                                        "<p>Source: <i class='icon-external-link'></i> " +
                                            "<a href='https://www.wikipathways.org/index.php/Pathway:" + svg_return.wp_id + "' target='_blank'>Wikipathways.org</a>" +
                                        "</p>" +
                                    "</div>"


                        d3.select("#" + id).html(svg);
                    }
                });
            } else {
                jQuery.ajax({
                    type: "GET"
                    , dataType: "html"
                    , url: "{{ path_to_this_module }}/get_kegg_svg"
                    , data: ({kegg_id: pathwayId})
                    , success: function (svg_return) {

                        var svg = "<div id='kegg-legend'><p><span style='background:#00FF00; display: block;'></span>Green Boxes have targeted assays<p></div>" + svg_return;

                        d3.select("#" + id).html(svg);
                    }
                });
            }

            if (tabCounter > 0) {
                tabs.show();
                kegg_wrapper.show();
            }

            tabs.tabs( "refresh" );

            var index = $('#tabs a[href="#' + id + '"]').parent().index();

            $("#tabs").tabs("option", "active", index);
        }

        // Actual removeTab function: adds new tab using the input from the form above
        function removeTab(tabId) {
            var panelId = tabs.find( "a[href='#" + tabId + "']" ).closest("li").remove().attr( "aria-controls" );
            //var panelId = tabs.find("li").has( "a[href='#" + id + "']" ).remove().attr( "aria-controls" );
            $( "#" + panelId ).remove();
            tabCounter--;

            tabs.tabs( "refresh" );
            if(tabCounter == 0) {
                tabs.hide();
                kegg_wrapper.hide();
            }
        }

        // Close icon: removing the tab on click
        tabs.on( "click", "span.ui-icon-close", function() {
            var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
            $( "#" + panelId ).remove();
            //$("#kegg_select option[value='" + panelId + "']").remove();
            //$("#kegg_select option[value='"+ panelId + "']").attr('selected', false);

            //$("#kegg_select").trigger("chosen:updated");
            tabCounter--;
            tabs.tabs( "refresh" );
            if(tabCounter == 0) {
                tabs.hide();
                kegg_wrapper.hide();
            }
        });

        tabs.on( "keyup", function( event ) {
            if ( event.altKey && event.keyCode === $.ui.keyCode.BACKSPACE ) {
                var panelId = tabs.find( ".ui-tabs-active" ).remove().attr( "aria-controls" );
                $( "#" + panelId ).remove();
                tabs.tabs( "refresh" );
            }
        });

        //clearAll();

        function clearAll() {
            // KEGG pathways
            $("#kegg_select").val('').trigger("chosen:updated");
            $("#panel_filter").val('').trigger("chosen:updated");
            $("#select_cromosome").val('').trigger("chosen:updated");
            $("#select_assay_type").val('').trigger("chosen:updated");
            $("#select_cptac_type").val('').trigger("chosen:updated");
            $("#select_certification").val('').trigger("chosen:updated");
            $("#select_antibody").val('').trigger("chosen:updated");
            $("#select_peptide_standard_purity").val('').trigger("chosen:updated");
            $("#select_lab").val('').trigger("chosen:updated");
            $("#select_species").val('').trigger("chosen:updated");
            var tab_lis = tabs.find("li");
            var i = tab_lis.length;
            while(i--) {
                var li = tab_lis[i];
                removeTab($(li).attr("aria-controls"));
            }

            // All multiselects
            // Protein interactions
            $("#select_protein_interactions").tagit("removeAll");
            // Chromosomal location
            $('#cromosomal_location_start, #cromosomal_location_stop').val('');
            // Search box
            $("#sidebar_search_box").val('');
            // Clear the main search
            //$('#browse_table').fnFilterClear();
            $('#browse_table').DataTable().ajax.reload();
        }

        if(wikipathway_id) {

            var pathway_string_stripped = $("#select_wikipathway option[value='" + wikipathway_id + "']").text().replace(/-+/, '').replace(/[^a-zA-Z0-9\s]+/g, '');

            $("#select_wikipathway option[value='" + wikipathway_id + "']").text(pathway_string_stripped);

            $("#select_wikipathway option[value='" + wikipathway_id + "']").prop("selected", true);
            $("#select_wikipathway option[value='" + wikipathway_id + "']").trigger("chosen:updated");

            addTab(wikipathway_id, pathway_string_stripped, pathway_string_stripped, true);

            //$("#select_wikipathway").val([wikipathway_id]).trigger("chosen:updated");

            $(".nav_menu").click();
            $("#wikipathway_filter").click();

            $('#browse_table').dataTable().fnDraw();
        }

    });

    function multiselect_deselectAll($el) {
        $('option', $el).each(function (element) {
            $el.multiselect('deselect', $(this).val());
        });
    }

    function kegg_pathway_assays (e, gene_symbol) {

        $('#view_protein_modal').modal('show');

        $.ajax({
            type:"POST"
            , dataType:"json"
            , url: "{{ path_to_this_module }}/get_assays_by_gene_symbol"
            , data: { gene_symbol: gene_symbol }
            , success: function(ajax_return){
                // Clear any data from previous population
                $('.modal-header').html("");
                $('#modal-assays-table-body').html("");

                if( ajax_return.length > 0 ) {
                    var gene_div_ul = $('<ul />').attr('id', gene_symbol);
                    var gene_div_li = $('<li> <strong>' + gene_symbol + ' - ' + ajax_return[0].uniprot_protein_name + '</strong> </li>')
                    gene_div_ul.append(gene_div_li);
                    $('.modal-header').append(gene_div_ul);

                    // Loop through each result and create an unordered list
                    $( ajax_return ).each(function(index, single) {
                        var view_detail = $('<tr><td class="cptac-id">' + single.cptac_id + '</td>' +
                            '<td>' + single.peptide_modified_sequence + '</td>' +
                            '<td>' + single.modification + '</td>' +
                            '<td>' + single.site_of_modification_protein + '</td>' +
                            '<td>' + single.assay_type + ' ' + single.data_type + '</td>' +
                            '<td>' + single.matrix + '</td></tr>');
                        $('#modal-assays-table-body').append(view_detail);

                    });

                    $('#modal-assays-table-body tr').click(function () {
                        window.open($(this).children('td.cptac-id').text());
                    });

                }
            }
        });
    }
     /*
    table.on( 'draw', function () {
    console.log( 'Redraw occurred at: '+new Date().getTime() );
    } );
    */


</script>





{% endblock %}
