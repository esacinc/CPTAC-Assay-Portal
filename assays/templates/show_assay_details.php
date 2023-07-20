{% extends public_layout_template_name %}
{% import "convert_filesize.twig" as convert %}
{% block meta_tags %}
{{ parent() }}
<meta name="description" content="Assay details for {{ gene }}, CPTAC-{{ manage }}">
<meta name="keywords"
      content="{{ gene }}, CPTAC-{{ manage }}, cancer, assay, protein, gene, peptide, sequence, characterize, tumor, normal tissue, biospecimens, genomic, proteomic, data, analysis, discovery, biomarker, testing, verification, uniprot, panorama, entrez">
<meta name="author"
      content="ABCC's Scientific Web Programming Group, Frederick National Laboratory for Cancer Research (FNLCR)">
{% endblock %}
{% block styles_head %}
{{ parent() }}
<link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-lightbox/0.5/bootstrap-lightbox.css" rel="stylesheet">
<link href="/assays/library/css/public_details.css" rel="stylesheet" type="text/css"/>
{% endblock %}
{% block content %}
<a name="top"></a>

<div id="outer-wrapper">

  <div class="row-fluid" style="margin-top:0px">
      <div class="span1"></div>
      <div class="page-title span10">
          <div style="font-weight:bolder;">
          <div style="text-align:center;font-weight:bold;text-decoration:underline;line-height: 30px;">Please include the following statement when referencing the CPTAC Assay Portal</div>
          <div style="text-align:center;font-size:13px;line-height:30px;">We would like to acknowledge the National Cancer Institute’s Clinical Proteomic Tumor Analysis Consortium (CPTAC) Assay Portal (assays.cancer.gov)
           for developing assays and establishing criteria for the assays described in this publication.</div>
         </div>
      </div>
     <div class="span1"></div>
 </div>


    {% if preview_header %}
    <div class="alert alert-error alert-preview">
        <i class="icon-exclamation-sign"></i> You are viewing this record in preview mode. Record Status: {{
        status_display }}
    </div>
    {% endif %}

    <div class="row-fluid">
        <div class="detail-page-header"><h3><i class="local-icon-helix"></i> {{ gene }}</h3></div>
        <div class="detail-page-header assay-nav">

            {% set previous = false %}
            {% set previousLink = '#' %}
            {% set previousDisabled = 'disabled' %}
            {% if prevNext.previous != '' %}
            {% set previous = prevNext.previous %}
            {% set previousTooltip = 'Go to Assay '~previous %}
            {% set previousDisabled = '' %}
            {% set previousLink = '/'~previous %}
            {% endif %}

            {% set next = false %}
            {% set nextLink = '#' %}
            {% set nextDisabled = 'disabled' %}
            {% if prevNext.next != '' %}
            {% set next = prevNext.next %}
            {% set nextTooltip = 'Go to Assay '~next %}
            {% set nextDisabled = '' %}
            {% set nextLink = '/'~next %}
            {% endif %}
            <div class="details_pagination">
                <div class="pull-left">
                    <a href="{{ previousLink }}" data-original-title="{{ previousTooltip }}"
                       class="{{ previousDisabled }}"><i class="icon-arrow-left"></i> Previous Assay</a>
                    <a href="{{ nextLink }}" class="{{ nextDisabled }}" data-original-title="{{ nextTooltip }}">Next
                        Assay <i class="icon-arrow-right"></i></a>
                </div>

            </div>
            <div class="available_assays pull-right">
                <a href="/available_assays">Available Assays</a>
            </div>


        </div>
    </div>

    <div class="row-fluid">
        <div class="span6 overview-details">
            <h4>Overview <span class="graph-instructions muted">Data source: UniProt</span></h4>
            <table class="table table-condensed borderless n-bordered">
                <tr>
                    <th>Official Gene Symbol</th>
                    <th>Other Aliases</th>
                </tr>
                <tr>
                    <td>{{ gene }}</td>
                    <!-- <td>{% if entrez_api.gene_synonyms %}{{ entrez_api.gene_synonyms }}{% else %}N/A{% endif %}</td> -->
                    <td>{% if uniprot_gene_synonym %}{{ uniprot_gene_synonym }}{% else %}N/A{% endif %}</td>

                </tr>
            </table>
            <table class="table table-condensed borderless n-bordered">
                <tr>
                    <th>Sequence Length (AA)</th>
                    <th>Molecular Weight (Da)</th>
                </tr>
                <tr>
                    <td>{{ uniprot_api.sequence_length }}</td>
                    <td>{{ protein_molecular_weight }}</td>
                </tr>
            </table>
            <table class="table table-condensed borderless n-bordered">
                <tr>
                    <th>Protein Name</th>
                </tr>
                <tr>
                    <td>{{ uniprot_protein_name }}</td>
                </tr>
            </table>
            <table class="table table-condensed borderless n-bordered">
                <tr>
                    <th colspan="2">Sources</th>
                </tr>
                <tr>
                    <td>
                        <i class="icon-external-link"></i> <a href="{{ uniprot_link }}" target="_blank"
                                                              title="External link to Uniprot Database">UniProt</a><br/>
                        <i class="icon-external-link"></i> <a
                                href="http://www.phosphosite.org/uniprotAccAction.do?id={{ uniprot_api.uniprot_ac }}"
                                target="_blank" title="PhosphoSite">PhosphoSitePlus &reg;</a><br/>
                        <i class="icon-external-link"></i> <a
                                href="http://www.genecards.org/cgi-bin/carddisp.pl?gene={{ gene }}" target="_blank"
                                title="GeneCards">GeneCards</a>
                    </td>
                    <td>
                        {% if entrez_api.entrez_gene_id %}
                        <i class="icon-external-link"></i> <a
                                href="http://www.ncbi.nlm.nih.gov/gene/{{ entrez_api.entrez_gene_id }}" target="_blank"
                                title="Entrez Gene">Entrez Gene</a><br/>
                        {% endif %}
                        <i class="icon-external-link"></i> <a href="http://www.proteinatlas.org/search/{{ gene }}"
                                                              target="_blank" title="Human Protein Atlas">Human Protein
                            Atlas</a>
                    </td>
                </tr>
            </table>
        </div>


        <div class="span6 sequence">
            <h4>Protein Sequence <span class="graph-instructions"><i class="icon-info-sign"></i> hover to view complete sequence</span>
            </h4>

            <div class="protein-sequence">
                {{ uniprot_api.formatted_sequence|raw }}
            </div>
            <p class="muted">Data source: UniProt</p>
        </div>
    </div>

    {#
    <div class="row-fluid">
        <div class="span6 sequence">
            <h4>Protein Sequence <span class="graph-instructions"><i class="icon-info-sign"></i> hover to view complete sequence</span>
            </h4>
            <div class="protein-sequence">
                {{ uniprot_api.formatted_sequence|raw }}
            </div>
        </div>

        <div class="span6 sequence">
            <h4>Splice Junctions <span class="graph-instructions"><i class="icon-info-sign"></i> hover to view complete sequence</span>
            </h4>
            {% if ensembl_sequence %}
            <div class="splice-junction-sequence">
                <div class="splice-junctions-legend">
                    <div class="splice-junction-black">Alternating Exons</div>
                    <div class="splice-junction-blue">Alternating Exons</div>
                    <div class="splice-junction-red">Residue Overlap Splice Site</div>
                </div>
                <pre>{{ ensembl_sequence|raw }}</pre>
            </div>
            {% else %}
            <p>Splice Junctions Unavailable</p>
            {% endif %}
        </div>
    </div>
    #}

    <hr class="black">

    <div class="row-fluid header-split">
        <div class="span12">
            <h3><i class="icon-picture"></i> Protein Map <span class="collapse-button"><a href="javascript:void(0);"><i
                                class="icon-collapse-alt"></i> <span class="toggle-text">Collapse protein map</span></a></span>
            </h3>
        </div>
    </div>

    <div id="graphs" class="row-fluid">
        <div class="span12 details">

            <h3>Position of Targeted Peptide Analytes Relative to SNPs, Isoforms, and PTMs</h3>
            <p>
                <i class="icon-external-link"></i> <a href="{{ uniprot_link }}" target="_blank"
                                                      title="External link to the Uniprot database entry">Uniprot
                    Database Entry</a>
                <i class="icon-external-link horizontal-list"></i> <a
                        href="http://www.phosphosite.org/uniprotAccAction.do?id={{ uniprot_api.uniprot_ac }}"
                        target="_blank" title="External link to the PhosphoSitePlus database entry">PhosphoSitePlus
                    &reg;</a>
            </p>

            <div class="muted mrm-points">
                Click a point on a node
                <div class="mrm-block-symbol"></div>
                to view detailed assay information below <i class="icon-circle-arrow-down"></i>
            </div>
            <div class="muted all-other-points">
                All other points link out to <i class="icon-external-link"></i> <a href="http://www.uniprot.org/"
                                                                                   target="_blank">UniProt</a>
            </div>

            <div id="all_in_one_graph"></div>

            <hr>
            <br>
            <br>

            <div class="phosphosite_plus_container">
                <div style="padding-left: 5px;padding-bottom: 5px; float:left; width:1000px" class="bold01">

                </div>
                <div id="proteincartoon" style="width:800px;height:400px;float:left; cursor: pointer; margin-left: 150px">

                </div>

                <div id="controlpanel" style="width:200px;height:400px;float:left;">
                    <div id="legend" width="200" heigt="100">
                        <svg width="150" height="200">
                            <g>
                                <circle r="5" cx="7" cy="10"
                                        style="fill: #4393c3; stroke: #666666; stroke-width: 1px;"></circle>
                                <text x="20" y="15" text-anchor="start" style="font-size: 12px; font-family: Verdana;">
                                    Phosphorylation
                                </text>
                                <circle r="5" cx="7" cy="30"
                                        style="fill: #5aae61; stroke: #666666; stroke-width: 1px;"></circle>
                                <text x="20" y="35" text-anchor="start" style="font-size: 12px; font-family: Verdana;">
                                    Acetylation
                                </text>
                                <circle r="5" cx="7" cy="50"
                                        style="fill: #bf812d; stroke: #666666; stroke-width: 1px;"></circle>
                                <text x="20" y="55" text-anchor="start" style="font-size: 12px; font-family: Verdana;">
                                    Ubiquitylation
                                </text>
                                <circle r="5" cx="7" cy="70"
                                        style="fill: #666666; stroke: #666666; stroke-width: 1px;"></circle>
                                <text x="20" y="75" text-anchor="start" style="font-size: 12px; font-family: Verdana;">Other
                                </text>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a name="assay_details_anchor"></a>

    <div id="preloader_loading_assay_details">
        <p id="preloader_text">loading</p>
        <img src="/site/library/images/indicator-big.gif" id="preloader_image" alt="Loader">
    </div>

    {% for key, field in genes %}
    <div class="{{ field.peptide_sequence }}-{{ field.manage }} {{ field.manage }} assay-details-wrapper"> {# _{{
        loop.index0 }} #}
        <hr class="black">
        <h3><i class="icon-bar-chart"></i> Assay Details for {{ field.cptac_id }} <span
                    id="collapse-button-assay-details"><a href="javascript:void(0);"><i
                            class="icon-collapse-alt"></i> <span
                            class="toggle-text">Collapse assay details</span></a></span></h3>
        <p class="muted">Data source: Panorama</p>

        <div id="assay_details" class="row-fluid">
            <div class="span12">
                <div class="span6">
                    <dl class="dl-horizontal-assay">
                        <dt>Official Gene Symbol</dt>
                        <dd>{{ field.gene }}</dd>
                        <dt>Peptide{{ (field.peptide_modified_sequence != field.peptide_sequence)?' Modified':'' }}
                            Sequence
                        </dt>
                        <dd>{{ field.peptide_modified_sequence }}</dd>
                        <dt>Modification Type</dt>
                        <dd>{{ field.modification }}</dd>
                        <dt>Protein - Site of Modification</dt>
                        <dd>{% if field.site_of_modification_protein %}{{ field.site_of_modification_protein }}{% else
                            %}N/A{% endif %}
                        </dd>
                        <dt>Peptide - Site of Modification</dt>
                        <dd>{% if field.site_of_modification_peptide %}{{ field.site_of_modification_peptide }}{% else
                            %}N/A{% endif %}
                        </dd>
                        <dt>Peptide Start</dt>
                        <dd>{{ field.peptide_start }}</dd>
                        <dt>Peptide End</dt>
                        <dd>{{ field.peptide_end }}</dd>
                    </dl>
                </div>
                <div class="span6">
                    <dl class="dl-horizontal-assay">
                        <dt>CPTAC ID</dt>
                        <dd>{{ field.cptac_id }}</dd>
                        <dt>Peptide Molecular Mass</dt>
                        <dd>{{ field.peptide_molecular_weight|number_format(4) }}</dd>
                        <dt>Species</dt>
                        <dd>
                            {% set varStack = field.species|split(' ') %}
                            {% for value in varStack %}
                            {% if '(' and ')' in value %} <!-- matches () in species -->
                            {{value}}
                            {% else %}
                            <i>{{value}}</i>
                            {% endif %}
                            {% endfor %}
                        </dd>
                        {% set display_assay_type = false %}
                        {% if field.assay_type %}
                        {% set display_assay_type = field.assay_type|capitalize %}
                        {% endif %}


                        <dt>Assay Type</dt>
                        <dd>{% if display_assay_type %}{{ display_assay_type }}{% else %}Direct{% endif %} {{ data_type
                            }}
                        </dd>

                        {% if field.assay_type == 'enrichment' %}
                        <dt>Enrichment Method</dt>
                        <dd>{{ enrichment_method?:'N/A' }}</dd>
                        {% endif %}

                        {% if field.assay_type == 'fractionation' %}
                        <dt>Fractionation Approach</dt>
                        <dd>{{ fractionation_approach?:'N/A' }}</dd>
                        {% endif %}

                        <dt>Matrix</dt>
                        <dd>{{ field.matrix }}</dd>
                        <dt>Submitting Laboratory</dt>
                        <dd>{{ field.laboratory_name }}</dd>
                        <dt>Submitting Lab PI</dt>
                        <dd>{{ field.primary_contact_name }}</dd>
                    </dl>
                </div>
                {% if field.publication %}
                <hr class="clear_bottom_margin">
                <h4>Publication</h4>
                {% for publication_key, publication_field in field.publication %}
                <p><i class="icon-external-link"></i> <a href="{{ publication_field.publication_url }}" target="_blank"
                                                         title="External link to publication details">View Details</a>
                    <span class="muted">(opens in a new window)</span></p>
                <p>{{ publication_field.publication_citation | raw }}</p>
                {% endfor %}
                {% endif %}
                {% if field.disclaimer %}
                <hr class="clear_bottom_margin">
                <p><em>{{ field.disclaimer }}</em></p>
                {% endif %}

                {% if field.assay_with_publication %}
                <hr class="clear_bottom_margin">
                <h4>Publication</h4>
                {% for publication_key, publication_field in field.assay_with_publication %}
                <p><i class="icon-external-link"></i> <a href="{{ publication_field.publication_url }}" target="_blank"
                                                         title="External link to publication details">View Details</a>
                    <span class="muted">(opens in a new window)</span></p>
                <p>{{ publication_field.publication_citation | raw }}</p>
                {% endfor %}
                {% endif %}
                {% if field.disclaimer %}
                <hr class="clear_bottom_margin">
                <p><em>{{ field.disclaimer }}</em></p>
                {% endif %}




            </div>
        </div>
        <hr class="black">
        <h3><i class="icon-bar-chart"></i> Assay Parameters <span id="collapse-button-assay-parameters"><a
                        href="javascript:void(0);"><i class="icon-collapse-alt"></i> <span class="toggle-text">Collapse assay parameters</span></a></span>
        </h3>
        <p class="muted">Data source: Panorama</p>

        <div id="assay_parameters" class="row-fluid">
            <div class="span6">
                <dl>
                    <dt>Instrument</dt>
                    <dd>{{ field.instrument }}</dd>
                    <dt>Internal Standard</dt>
                    <dd>{{ field.internal_standard }}</dd>
                    <dt>Peptide Standard Purity</dt>
                    <dd>{{ field.peptide_standard_purity }}</dd>
                    <dt>Peptide Standard Label Type</dt>
                    <dd>{{ field.peptide_standard_label_type }}</dd>
                </dl>
            </div>
            <div class="span6">
                <dl>
                    <dt>LC</dt>
                    <dd>{{ field.lc }}</dd>
                    <dt>Column Packing</dt>
                    <dd>{{ field.column_packing }}</dd>
                    <dt>Column Dimensions</dt>
                    <dd>{{ field.column_dimensions }}</dd>
                    <dt>Flow Rate</dt>
                    <dd>{{ field.flow_rate }}</dd>
                </dl>
            </div>
        </div>

        {% if multiplex %}
        <hr class="black">

        <h3><i class="icon-list-ul"></i> Assay Multiplexing <i id="multiplex_info_icon" class="icon-info-sign"
                                                               title="More information" data-placement="right"></i>
            <span id="collapse-button-assay-multiplex"><a href="javascript:void(0);"><i
                            class="icon-collapse-alt"></i> <span
                            class="toggle-text">Expand assay panel</span></a></span></h3>
        <p class="muted" class="multiplex_panel_name">
            {{multiplex[0].name}}-{{multiplex[0].panel_description}}
        </p>

        <div id="assay_multiplex" class="assay_multiplex">

            <div class="span12">
                <form action="/assays/export_multiplex" method="post" name="export" class="export_multiplex"><input
                            type="submit" id="table_csv" value="Download Panel (CSV)"/>
                    <input type="hidden" name="multiplex_csv_name_filter" id="lab_name" value="{{multiplex[0].name}}"/>
                    <input type="hidden" name="multiplex_csv_description_filter" id="panel_name"
                           value="{{multiplex[0].panel_description}}"/>
                </form>
                <table id="multiplex" width="100%" class="multiplex_table table noborder">
                    {% for k,v in multiplex %}
                    {% if k % 3 == 0 %}
                    <tr>
                        <td>
                            <a href="/{{v.cptac_id }}">{{v.cptac_id}}:
                                <div class="multiplex_gene">{{v.gene_symbol}}.</div>
                                {{v.peptide_modified_sequence}}</a>
                        </td>
                        {% else %}
                        <td>
                            <a href="/{{v.cptac_id }}">{{v.cptac_id}}:
                                <div class="multiplex_gene">{{v.gene_symbol}}.</div>
                                {{v.peptide_modified_sequence}}</a>
                        </td>
                        {% endif %}
                        {% endfor %}
                    </tr>
                </table>
            </div>
        </div>

        {% endif %}

        <hr class="black">
        <h3>Chromatograms</h3> {# _{{ loop.index }} #}
        <p class="muted">Data source: Panorama</p>

        <div class="row-fluid plasma-charts">

            <div class="chromatogram_image_inline_1 span4 image">
                {% if field.chromatogram_images[0] %}
                <a data-toggle="lightbox" href=".chromatogram_image_{{ field.manage }}_1"><img
                            src="{{ field.chromatogram_images[0] }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".chromatogram_image_{{ field.manage }}_1">view larger
                        image</a>
                </div>
                {% else %}
                <img src="{{path_to_this_module}}/library/images/600x600_clear.png" width="600" height="600"
                     alt="Chromatogram Image Unavailable"/>
                <div class="empty-spacer">&nbsp;</div>
                {% endif %}
            </div>
            <div class="chromatogram_image_{{ field.manage }}_1 lightbox hide fade" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class='lightbox-header'>
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                </div>
                <div class='lightbox-content'>
                    {% if field.chromatogram_images[0] %}
                    <img src="{{ field.chromatogram_images[0] }}" width="600" height="600" alt="chromatogram image 1">
                    {% endif %}
                </div>
            </div>

            <div class="chromatogram_image_inline_2 span4 image">
                {% if field.chromatogram_images[1] %}
                <a data-toggle="lightbox" href=".chromatogram_image_{{ field.manage }}_2"><img
                            src="{{ field.chromatogram_images[1] }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".chromatogram_image_{{ field.manage }}_2">view larger
                        image</a>
                </div>
                {% else %}
                <img src="{{path_to_this_module}}/library/images/600x600_clear.png" width="600" height="600"
                     alt="Chromatogram Image Unavailable"/>
                <div class="empty-spacer">&nbsp;</div>
                {% endif %}
            </div>
            <div class="chromatogram_image_{{ field.manage }}_2 lightbox hide fade" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class='lightbox-header'>
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                </div>
                <div class='lightbox-content'>
                    {% if field.chromatogram_images[1] %}
                    <img src="{{ field.chromatogram_images[1] }}" width="600" height="600" alt="chromatogram image 2">
                    {% endif %}
                </div>
            </div>

            <div class="chromatogram_image_inline_3 span4 image">
                {% if field.chromatogram_images[2] %}
                <a data-toggle="lightbox" href=".chromatogram_image_{{ field.manage }}_3"><img
                            src="{{ field.chromatogram_images[2] }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".chromatogram_image_{{ field.manage }}_3">view larger
                        image</a>
                </div>
                {% else %}
                <img src="{{path_to_this_module}}/library/images/600x600_clear.png" width="600" height="600"
                     alt="Chromatogram Image Unavailable"/>
                <div class="empty-spacer">&nbsp;</div>
                {% endif %}
            </div>
            <div class="chromatogram_image_{{ field.manage }}_3 lightbox hide fade" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class='lightbox-header'>
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                </div>
                <div class='lightbox-content'>
                    {% if field.chromatogram_images[2] %}
                    <img src="{{ field.chromatogram_images[2] }}" width="600" height="600" alt="chromatogram image 3">
                    {% endif %}
                </div>
            </div>

        </div>


        {% set crude = false %}
        {% if field.peptide_standard_purity_types_id == 3 %}
        {% set crude = false %}
        {% endif %}
        <hr class="black">

        <h3 id="response_curves_block_title">
            Response Curves
           <i id="response_curves_info_icon" class="icon-info-sign"
              title="Multipoint serial dilution of analyte in a sample matrix.
              Curve parameters are presented for each transition/fragment measured and the sum of all fragments.
              For more details, please see the Assay Characterization Guidance Document and the assay SOPs."
                                                      data-placement="right" data-toggle="tooltip"></i>


            </h3>
        <p class="muted">Data source: Panorama</p>

        <div class="row-fluid plasma-charts">
            <div class="response_curve_image_linear_inline span4 image">
                {% if field.response_curve_images[0] %}
                <a data-toggle="lightbox" href=".response_curve_image_linear_{{ field.manage }}"><img
                            src="{{ field.response_curve_images[0] }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".response_curve_image_linear_{{ field.manage }}">view larger
                        image</a>
                </div>
                {% else %}
                <p>Response Curve Image Unavailable</p>
                {% endif %}
            </div>
            <div class="response_curve_image_linear_{{ field.manage }} lightbox hide fade" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class='lightbox-header'>
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                </div>
                <div class='lightbox-content'>
                    {% if field.response_curve_images[0] %}
                    <img src="{{ field.response_curve_images[0] }}" width="800" height="600"
                         alt="response curve image - linear">
                    {% endif %}
                </div>
            </div>

            <div class="response_curve_image_log_inline span4 image">
                {% if field.response_curve_images[1] %}
                <a data-toggle="lightbox" href=".response_curve_image_log_{{ field.manage }}"><img
                            src="{{ field.response_curve_images[1] }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".response_curve_image_log_{{ field.manage }}">view larger
                        image</a>
                </div>
                {% else %}
                <p>Response Curve Image Unavailable</p>
                {% endif %}
            </div>
            <div class="response_curve_image_log_{{ field.manage }} lightbox hide fade" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class='lightbox-header'>
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                </div>
                <div class='lightbox-content'>
                    {% if field.response_curve_images[1] %}
                    <img src="{{ field.response_curve_images[1] }}" width="800" height="600"
                         alt="response curve image - log">
                    {% endif %}
                </div>
            </div>

            <div class="response_curve_image_residual_inline span4 image">
                {% if field.response_curve_images[2] %}
                <a data-toggle="lightbox" href=".response_curve_image_residual_{{ field.manage }}"><img
                            src="{{ field.response_curve_images[2] }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".response_curve_image_residual_{{ field.manage }}">view
                        larger image</a>
                </div>
                {% else %}
                <p>Response Curve Image Unavailable</p>
                {% endif %}
            </div>
            <div class="response_curve_image_residual_{{ field.manage }} lightbox hide fade" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class='lightbox-header'>
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                </div>
                <div class='lightbox-content'>
                    {% if field.response_curve_images[2] %}
                    <img src="{{ field.response_curve_images[2] }}" width="800" height="600"
                         alt="response curve image - residual">
                    {% endif %}
                </div>
            </div>

        </div>

        <div class="row-fluid">

            <div class="span12 {{ field.manage }}_{{ field.laboratory_abbreviation }}_{{ field.manage }} centered repeatability-data-table">
                <div class="loc_lloq_preloader_loading">
                    <p>Retrieving Data</p>
                    <img src="/site/library/images/indicator-big.gif" alt="Loader">
                </div>
            </div>
        </div>

        <hr class="black">

        <h3>Repeatability

              <i id="repeatability_info_icon" class="icon-info-sign"
              title="To approximate variability, samples are prepared at three known concentrations in sample matrix and run over five days, CV is calculated for intra-assay and inter-assay performance.
              The total CV is the square root of the sum of squares of intra- and inter-assay CVs (sqrt[intraCV2+interCV2]).
              CVs are presented for each transition/fragment measured and the sum of all fragments.
              The CVs larger than 20% are highlighted in red."
                                                                  data-placement="right" data-toggle="tooltip"></i>

        </h3>
        <p class="muted">Data source: Panorama</p>

        <div class="row-fluid plasma-charts">
            <div class="span12 image">
                {% if field.validation_sample_image %}
                <a data-toggle="lightbox" href=".validation_sample_image_{{ field.manage }}"><img
                            src="{{ field.validation_sample_image }}"></a>
                <div class="caption-link">
                    <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                     href=".validation_sample_image_{{ field.manage }}">view larger
                        image</a>
                </div>
                {% else %}
                <p>Repeatability Image Unavailable</p>
                {% endif %}
            </div>
            <div class="validation_sample_image_{{ field.manage }} modal fade scroll-touch exampleModalLong" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">

                  <div class="lightbox-header">
                    <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
                  </div>
                    <div class="modal-body scroll-touch" style="text-align:left;">
                    {% if field.validation_sample_image %}
                    <img src="{{ field.validation_sample_image }}" width="1400" height="600"
                         alt="validation_sample_image">
                    {% endif %}


                  </div>

                </div>
              </div>
            </div>
        </div>


        {% if field.validation_sample_images_data[field.manage] %}
        <div class="row-fluid">
            <div class="span12">
                <table class="table table-bordered table-striped table-condensed text-centered repeatability-table"
                       id="{{ field.manage }}_validation_sample_data_{{ field.manage }}">
                    <tbody>
                    <tr>
                        <th>&nbsp;</th>
                        <th colspan="3">Average intra-assay CV<br><span class="un-bold">(within day CV)</span></th>
                        <th colspan="3">Average inter-assay CV<br><span class="un-bold">(between day CV)</span></th>
                        <th colspan="3">Total CV<br><img src="{{ path_to_this_module }}/library/images/equation.png"
                                                         width="118" height="13" alt="equation"></th>
                        <th colspan="3">n=</th>
                    </tr>
                    <tr>
                        <td><strong>Fragment ion / Transition</strong></td>
                        <td><strong>Low</strong></td>
                        <td><strong>Med</strong></td>
                        <td><strong>High</strong></td>
                        <td><strong>Low</strong></td>
                        <td><strong>Med</strong></td>
                        <td><strong>High</strong></td>
                        <td><strong>Low</strong></td>
                        <td><strong>Med</strong></td>
                        <td><strong>High</strong></td>
                        <td><strong>Low</strong></td>
                        <td><strong>Med</strong></td>
                        <td><strong>High</strong></td>
                    </tr>
                    {% for key, val in field.validation_sample_images_data[field.manage] %}
                    <tr>
                        <td>{{ val.fragment_ion | raw }}</td>
                        <td>{{ val.low_intra_CV | raw }}</td>
                        <td>{{ val.med_intra_CV | raw }}</td>
                        <td>{{ val.high_intra_CV | raw }}</td>
                        <td>{{ val.low_inter_CV | raw }}</td>
                        <td>{{ val.med_inter_CV | raw }}</td>
                        <td>{{ val.high_inter_CV | raw }}</td>
                        <td>{{ val.low_total_CV | raw }}</td>
                        <td>{{ val.med_total_CV | raw }}</td>
                        <td>{{ val.high_total_CV | raw}}</td>
                        <td>{{ val.low_count | raw }}</td>
                        <td>{{ val.med_count | raw }}</td>
                        <td>{{ val.high_count | raw }}</td>
                    </tr>
                    {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
        {% endif %}

      <hr class="black"> <!--new experiments here -->


      {% if field.selectivity_image %}
      <h3 id="selectivity_block_title">
          Selectivity

              <i id="repeatability_info_icon" class="icon-info-sign"
              title="To examine the response of a peptide at no spike,
               1/2 the medium and medium concentrations defined in Experiment 2 from six different biological replicates of the matrix,
              the slopes of the curve are presented for each transition/fragment measured and the sum of all fragments."
                                                                  data-placement="right" data-toggle="tooltip"></i>

      </h3>
      <p class="muted">Data source: Panorama</p>
      <div class="row-fluid plasma-charts">
          <div class="span12 image">
              {% if field.selectivity_image %}
              <a data-toggle="lightbox" href=".selectivity_image_{{ field.manage }}"><img
                          src="{{ field.selectivity_image }}"></a>
              <div class="caption-link">
                  <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                   href=".selectivity_image_{{ field.manage }}">view larger
                      image</a>
              </div>
              {% else %}
              <p>Selectivity Image Unavailable</p>
              {% endif %}
          </div>

        <div class="selectivity_image_{{ field.manage }} modal fade scroll-touch exampleModalLong" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">

              <div class="lightbox-header">
                <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
              </div>
                <div class="modal-body scroll-touch" style="text-align:left;">
                {% if field.selectivity_image %}
                <img src="{{ field.selectivity_image }}" width="1400" height="800"
                     alt="selectivity image" >
                {% endif %}


              </div>

            </div>
          </div>
        </div>
      </div>

      {% if field.selectivity_summary_data %}
      <div class="row-fluid">
          <div class="span12">
              <table class="table text-centered">
                  <tr>
                      {% for key,value in field.selectivity_summary_data %}
                      {% if key == 0 %}
                      {% set colspan_length = 0 %}
                      {% for k,v in value %}
                      {% set colspan_length = k %}
                      {% endfor %}
                      <th></th>
                      <th colspan={{colspan_length}}>
                          Slope of Curve Fit for cell line
                      </th>
                      {% endif %}
                      {% endfor %}
                  </tr>


                  {% for key,value in field.selectivity_summary_data %}
                  <tr>
                      {% for k,v in value %}
                      <td>{{v}}</td>
                      {% endfor %}
                  </tr>
                  {% endfor %}
              </table>
          </div>
      </div>
      {% endif %}

      {% endif %} <!--end selectivity -->

    {% if field.stability_image or field.stability_data %}
      <hr class="black">
      <h3 id="response_curves_block_title">
          Stability

              <i id="repeatability_info_icon" class="icon-info-sign"
              title="To assess the variation of a peptide spiked into a background matrix, samples are treated at different sample storage
              conditions over time and the matrix for this experiment was spiked with the medium concentration as defined in Experiment 2.
              CV is calculated for intra-assay and inter-assay performance.
              The total CV is the square root of the sum of squares of intra- and inter-assay CVs (sqrt[intraCV2+interCV2]).
              CVs are presented for each transition/fragment measured and the sum of all fragments.
              The CVs larger than the total CV from the medium concentration in Repeatability table are highlighted in red."
                                                                  data-placement="right" data-toggle="tooltip"></i>

      </h3>
      <p class="muted">Data source: Panorama</p>
      <div class="row-fluid plasma-charts">
        <div class="span12 image">
                 {% if field.stability_image %}
                 <a data-toggle="lightbox" href=".stability_image_{{ field.manage }}"><img
                             src="{{ field.stability_image }}"></a>
                 <div class="caption-link">
                     <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                      href=".stability_image_{{ field.manage }}">view larger
                         image</a>
                 </div>
                 {% else %}
                 <p>Stability Image Unavailable</p>
                 {% endif %}
             </div>

<div class="stability_image_{{ field.manage }} modal fade exampleModalLong"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
 <div class="modal-dialog" role="document">
   <div class="modal-content">

       <div class='lightbox-header'>
       <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
       </div>
       <div class="modal-body" style="text-align:left;">
       {% if field.stability_image %}
       <img src="{{ field.stability_image }}" width="1400" height="600"
            alt="stability image">
       {% endif %}


     </div>

   </div>
 </div>
</div>

      </div>
      <div class="row-fluid">
        <div class="span12">
        <table class="table table-bordered table-striped table-condensed text-centered"
         id="stability_data">
           <tbody>
            <tr class="stability_header">
              <th id="fragment_ion"><strong>Fragment ion / Transition</strong></th>
              <th id="control_intra_CV"><strong>control_intra_CV</strong></th>
              <th id="actual_temp_intra_CV"><strong>actual_temp_intra_CV</strong></th>
              <th id="frozen_intra_CV" ><strong>frozen_intra_CV</strong></th>
              <th id="FTx1_intra_CV" ><strong>FTx1_intra_CV</strong></th>
              <th id="FTx2_intra_CV" ><strong>FTx2_intra_CV</strong></th>
            </tr>
            {% for key, val in field.stability_data %}
            <tr>
              <td id="fragment_ion_value" headers="fragment_ion">{{ val.fragment_ion | raw }}</td>
              <td id="control_intra_CV_value" headers="control_intra_CV">{{ val.control_intra_CV | raw }}</td>
              <td id="actual_temp_intra_value" headers="actual_temp_intra_CV">{{ val.actual_temp_intra_CV | raw }}</td>
              <td id="frozen_intra_value" headers="frozen_intra_CV">{{ val.frozen_intra_CV | raw }}</td>
              <td id="FTx1_intra_CV_value" headers="FTx1_intra_CV">{{ val.FTx1_intra_CV | raw }}</td>
              <td id="FTx2_intra_CV_value" headers="FTx2_intra_CV">{{ val.FTx2_intra_CV | raw }}</td>
            </tr>
            {% endfor %}
           </tbody>
       </table>
       <table class="table table-bordered table-striped table-condensed text-centered repeatability-table"
         id="stability_data_2">
          <tbody>
            <tr class="stability_header">
              <th id="fragment_ion"><strong>Fragment ion / Transition</strong></th>
              <th id="all_intra_CV"><strong>all_intra_CV</strong></th>
              <th id="all_inter_CV"><strong>all_inter_CV</strong></th>

            </tr>
            {% for key, val in field.stability_data %}
            <tr>
             <td id="fragment_ion_value" headers="fragment_ion">{{ val.fragment_ion | raw }}</td>
             <td id="all_intra_CV_value" headers="all_intra_CV">{{ val.all_intra_CV | raw }}</td>
             <td id="all_inter_CV_value" headers="all_inter_CV">{{ val.all_inter_CV | raw }}</td>

            </tr>
           {% endfor %}
        </tbody>
       </table>

        </div>
      </div>
      {% endif %} <!-- end stability -->



      {% if field.endogenous_image or field.endogenous_data%}

        <h3 id="endogenous_block_title">
            Endogenous

                <i id="endogenous_info_icon" class="icon-info-sign"
                title="To assess the ability to detect endogenous analytes from representative samples digested five times on each of five days, CV is calculated for intra-assay and inter-assay performance.
                The total CV is the square root of the sum of squares of intra- and inter-assay CVs (sqrt[intraCV2+interCV2]).
                CVs are presented for each transition/fragment measured and the sum of all fragments. The CVs larger than 20% are highlighted in red."
                                                                    data-placement="right" data-toggle="tooltip"></i>

        </h3>

        <p class="muted">Data source: Panorama</p>
        <div class="row-fluid plasma-charts">
           <div class="span12 image">

             {% if field.endogenous_image %}

                           <a data-toggle="lightbox" href=".endogenous_image_{{ field.manage }}"><img
                                       src="{{ field.endogenous_image }}"></a>
                           <div class="caption-link">
                               <i class="icon-eye-open"></i> <a data-toggle="lightbox"
                                                                href=".endogenous_image_{{ field.manage }}">view larger
                                   image</a>
                           </div>
                           {% else %}
                           <p>Endogenous Image Unavailable</p>
                           {% endif %}
           </div>

         <div class="endogenous_image_{{ field.manage }} modal fade exampleModalLong" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
           <div class="modal-dialog" role="document">
             <div class="modal-content">

                  <div class='lightbox-header'>
                 <button type="button" class="btn-js-close" data-dismiss="lightbox" aria-hidden="true">&times;</button>
               </div>
                 <div class="modal-body" style="text-align:left;">
                 {% if field.endogenous_image  %}
                 <img src="{{ field.endogenous_image }}" width="1400" height="600"
                      alt="endogenous image">
                 {% endif %}


               </div>

             </div>
           </div>
         </div>
       </div>

        <div class="row-fluid">
              <div class="span12">
                <table class="table table-bordered table-striped table-condensed text-centered repeatability-table"
                                  id="endogenous_data">
                               <tbody>
                               <tr class="endogenous_header">
                                   <th id="fragment_ion"><strong>Fragment ion / Transition</strong></th>
                                   <th id="intra_CV"><strong>intra_CV</strong></th>
                                   <th id="inter_CV"><strong>inter_CV</strong></th>
                                   <th id="total_CV"><strong>total_CV</strong></th>
                               </tr>
                               {% for key, val in field.endogenous_data %}
                               <tr>
                                   <td id="fragment_ion_value" headers="fragment_ion">{{ val.fragment_ion | raw }}</td>
                                   <td id="intra_CV_value" headers="intra_CV">{{ val.intra_CV | raw }}</td>
                                   <td id="inter_CV_value" headers="inter_CV">{{ val.inter_CV | raw }}</td>
                                   <td id="total_CV_value" headers="total_CV">{{ val.total_CV | raw }}</td>
                               </tr>
                               {% endfor %}
                               </tbody>
                  </table>
              </div>
        </div>

        {% endif %}


        <hr class="black">
        <h3 class="resources-and-comments">Additional Resources and Comments</h3>

        <div class="row-fluid">
            <div class="span4 links-container">
                <dt><i class="icon-info-sign"></i> Resources</dt>
                <dl><a href="#sops_{{ field.manage }}" role="button" class="btn btn-small" data-toggle="modal"
                       title="Download SOPs"><i class="icon-download-alt icon-white"></i> Download SOPs</a></dl>
                {% if field.panorama_peptide_url %}
                <dl><a href="https://panoramaweb.org{{ field.panorama_peptide_url }}" class="btn btn-small"
                       target="_blank" title="View in Panorama"><i class="icon-eye-open icon-white"></i> View Peptide in
                        Panorama</a></dl>
                {% endif %}
                {% if field.panorama_protein_url %}
                <dl><a href="https://panoramaweb.org{{ field.panorama_protein_url }}" class="btn btn-small"
                       target="_blank" title="View in Panorama"><i class="icon-eye-open icon-white"></i> View Protein in
                        Panorama</a></dl>
                {% endif %}

                {% if field.cptc_catalog_id %}
                <dl><a href="http://antibodies.cancer.gov/detail/{{ field.cptc_catalog_id }}"
                       class="btn btn-small" target="_blank"
                       title="View {{ field.cptc_catalog_id }} in CPTC Antibody Portal"><i
                                class="icon-eye-open icon-white"></i> View in CPTC Antibody Portal</a></dl>
                {% endif %}

                {% if field.assay_identifier %}
                <dl>
                    <a href="http://antibodies.cancer.gov/apps/site/detail/{{ field.assay_identifier }}#{{ field.assay_identifier }}"
                       class="btn btn-small" target="_blank" title="View Antibody"><i
                                class="icon-eye-open icon-white"></i> View Antibody</a></dl>
                {% endif %}
                {% if field.peptide_order %}
                <dl><a href="javascript:void(0);" class="btn btn-small" title="Generate Peptide Order"><i
                                class="icon-cog icon-white"></i> Generate Peptide Order</a></dl>
                {% endif %}
            </div>
            <div class="span8 comments-container">
                <h5 id="comments_container_{{ field.manage }}"><i class="icon-star"></i> Comments</h5>
                <noscript>
                    Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by
                        Disqus.</a>
                </noscript>
            </div>

        </div>

    </div>

    <!-- SOPs -->
    <div id="sops_{{ field.manage }}" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="SOPDownloads"
         aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="SOPDownloads"><i class="icon-download-alt"></i> Download SOPs</h4>
        </div>
        <div class="modal-body sop-downloads">
            <ul>
                {% for sop_key, sop_field in field.sop_files %}
                <li><i class="icon-download-alt"></i> <a
                            href="{{ path_to_this_module }}/download_file?sop_files_id={{ sop_field.sop_files_id }}">{{
                        sop_field.file_name }}</a></li>
                {% endfor %}
            </ul>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>


    {% endfor %}

    <hr class="black">

</div>

<!-- IE Browser Disclaimer -->
<div id="ie_disclaimer" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ieModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="ieModalLabel"><i class="icon-exclamation-sign"></i> Internet Explorer Disclaimer</h3>
    </div>
    <div class="modal-body">
        <div class="control-group">
            <p>We've detected you are running an older version of Microsoft's Internet Explorer. While the majority of
                this website's content renders well, there may several items which may not.</p>
            <p>If you want the full experience of this site, we recommend using the latest version of any modern
                browser. You can find a complete listing at <a
                        title="Browse Happy: Online. Worry-free. Upgrade your browser today!"
                        href="http://www.browsehappy.com/" target="_blank">BrowseHappy.com</a>.</p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<!-- Multiplex Panel Information -->
<div id="multiplex_information" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="rcModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="ieModalLabel">Multiplex Panel Information</h3>
    </div>
    <div class="modal-body">
        <div class="control-group">
            <p>Multiplex Panel Information</p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>


<!-- Response Curves Information -->
<div id="response_curves_information" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="rcModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="ieModalLabel">Response Curves Information</h3>
    </div>
    <div class="modal-body">
        <div class="control-group">
            <p>Multipoint serial dilution of analyte in a sample matrix. Curve parameters are presented for each
                transition/fragment measured and the sum of all fragments. For more details, please see the Assay
                Characterization Guidance Document and the assay SOPs.</p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<!-- Repeatability Information -->
<div id="repeatability_information" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="rpModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="ieModalLabel">Repeatability Information</h3>
    </div>
    <div class="modal-body">
        <div class="control-group">
            <p>To approximate variability, samples are prepared at three known concentrations in sample matrix and run
                over five days. CV is calculated for intra-assay and inter-assay performance. The total CV is the square
                root of the sum of squares of intra- and inter-assay CVs (sqrt[intraCV2+interCV2]). CVs are presented
                for each transition/fragment measured and the sum of all fragments.</p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>


{% endblock %}
{% block js_head %}
{{ parent() }}
<!--[if lt IE 9]>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/aight/1.1.1/aight.min.js"></script>
<script type="text/javascript" src="{{ path_to_this_module }}/library/flashcanvas/bin/flashcanvas.js"></script>
<![endif]-->
{% endblock %}
{% block js_bottom %}
{{ parent() }}
<script src="//cdnjs.cloudflare.com/ajax/libs/flot/0.8.1/jquery.flot.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-lightbox/0.5/bootstrap-lightbox.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/flot/0.8.1/jquery.flot.resize.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/flot/0.8.1/jquery.flot.navigate.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/flot/0.8.1/jquery.flot.symbol.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/history.js/1.8/bundled/html4+html5/jquery.history.min.js"></script>

<script type="text/javascript">


    // Script from:
    // http://mystrd.at/articles/multiple-disqus-threads-on-one-page/

    var disqus_shortname = '{{ disqus_shortname }}';
    //var disqus_identifier;
    var disqus_url = 'http://{{ server_name }}{{ request_uri }}';
    var disqus_identifier = disqus_url;
    var disqus_developer = '1';
    var disqus_title;

    var url_array = disqus_url.split("/");
    cptac_id = url_array[3];
    cptac_int_array = cptac_id.split("-");

    cptac_int = parseInt(cptac_int_array[1]);

    function loadDisqus(source, identifier, url) {


        if (window.disqus) {
            if (cptac_id != "CPTAC-720") {
                identifier = identifier + cptac_id;
            }

            jQuery('#disqus_thread').insertAfter(source); // Append the HTML after the target 'source'

            console.log(identifier);

            // Append the HTML after the target 'source'
            jQuery('<div id="disqus_thread"></div>').insertAfter(source);
            //disqus_identifier = identifier+cptac_id; //set the identifier argument
            //disqus_url = url; //set the url argument
            //disqus_title = 'Gene: {{ gene }}, Peptide Sequence: '+identifier+"_"+cptac_id+'- {{ site_name }}';

            var disqus_config = function () {
                this.page.url = url;
                this.page.identifier = identifier;
                this.page.title = 'Gene: {{ gene }}, Peptide Sequence: ' + identifier + '- {{ site_name }}';
            };


            // Append the Disqus embed script to HTML
            var dsq = document.createElement('script');
            dsq.type = 'text/javascript';
            dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            // jQuery('head').append(dsq);

            // If Disqus exists, call it's reset method with new parameters
            //DISQUS.reset({
            //  reload: true,
            //  config: function () {
            //    this.page.identifier = identifier+cptac_id;
            //    this.page.url = url;
            //    this.page.title = 'Gene: {{ gene }}, Peptide Sequence: '+identifier+"_"+cptac_id+'- {{ site_name }}';
            //  }
            //});

        } else {
            // Append the HTML after the target 'source'
            jQuery('<div id="disqus_thread"></div>').insertAfter(source);
            var comment_array = ["CPTAC-720", "CPTAC-1031", "CPTAC-968", "CPTAC-935", "CPTAC-687", "CPTAC-550", "CPTAC-978", "CPTAC-1724", "CPTAC-1725", "CPTAC-333", "CPTAC-241", "CPTAC-968", "CPTAC-540", "CPTAC-606"];
            console.log($.inArray(cptac_id, comment_array));
            if ($.inArray(cptac_id, comment_array) != -1) {
                disqus_identifier = identifier;
            } else {
                disqus_identifier = identifier + cptac_id; //set the identifier argument
            }
            disqus_url = url; //set the url argument
            disqus_title = 'Gene: {{ gene }}, Peptide Sequence: ' + identifier + "_" + cptac_id + '- {{ site_name }}';

            // Append the Disqus embed script to HTML
            var dsq = document.createElement('script');
            dsq.type = 'text/javascript';
            dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            // jQuery('head').append(dsq);

        }
    };
</script>

<script type="text/javascript">
  $(document).ready(function () {
      //tooltip formatting
      $('[data-toggle="tooltip"]').tooltip({
          placement : 'right',
          width: '500'
      });
      //@@@CAP-99 - fix broken menu links
      $('.exampleModalLong').hide();


      // Stability header message for high values
       var high_stability_values = $('#stability_data tbody tr td').find('.red');
       var high_stability_values_2 = $('#stability_data_2 tbody tr td').find('.red');

       for(i=0; i<high_stability_values.length; i++){
          var id = high_stability_values[i].parentElement.getAttribute("id");
          var x = document.getElementById(id).headers;

          // try to prevent duplicates
        //  $('#stability_data .stability_header .icon-exclamation-sign').remove();
          $('#'+x+' i.icon-exclamation-sign' ).remove();
          $('.stability_header #'+x).append(' <i class="icon-exclamation-sign red" title="Stability shows greater than med total CV for fragment ion in Repeatability" data-toggle="tooltip"></i>');
          $('.icon-exclamation-sign').tooltip();
       }

       for(i=0; i<high_stability_values_2.length; i++){
          var id = high_stability_values_2[i].parentElement.getAttribute("id");
          var x = document.getElementById(id).headers;
          // try to prevent duplicates
          $('#'+x+' i.icon-exclamation-sign' ).remove();
          $('.stability_header #'+x).append(' <i class="icon-exclamation-sign red" title="Stability shows greater than med total CV for fragment ion in Repeatability" data-toggle="tooltip"></i>');
          $('.icon-exclamation-sign').tooltip();

       }


      //Endogenous header message for high values
       var high_endogenous_values = $('#endogenous_data tbody tr td').find('.red');

       for(i=0; i<high_endogenous_values.length; i++){
          var id = high_endogenous_values[i].parentElement.getAttribute("id");
          var x = document.getElementById(id).headers;
          // try to prevent duplicates
          $('.endogenous_header #'+x+' .icon-exclamation-sign').remove();
          $('.endogenous_header #'+x).append(' <i class="icon-exclamation-sign red" title="Endogenous shows greater than 20% CV" data-toggle="tooltip"></i>');
          $('.icon-exclamation-sign').tooltip();

       }

        // [BEGIN] Miscellaneous ///////////////
        $('.skip-link').hide();

        // IE Disclaimer
        var show_disclaimer = '{{ show_disclaimer }}';
        if (show_disclaimer) {
            $('#ie_disclaimer').modal();
        }
        // Remove the side nav link
        $('#show_side_nav').remove();
        // Tooltips
        $('.assay-nav-btn').tooltip();


        $('.icon-question-sign').tooltip();
        // Prevent page from scrolling in the background
        $("body").on("click", ".view_protein_sequence, .submit_comment", function (event) {
            $('body').attr('style', 'overflow: hidden');
        });
        // Hide/show the Graphs and Visualizations block
        $(".collapse-button a").on("click", function () {
            var icon_class = ($("#graphs").is(":visible")) ? "icon-expand-alt" : "icon-collapse-alt";
            var icon_text = ($("#graphs").is(":visible")) ? "Expand protein map" : "Collapse protein map";
            $('#graphs').slideToggle();
            $('.row-fluid .span12 .collapse-button a i').attr('class', icon_class);
            $('.row-fluid .span12 .collapse-button a span.toggle-text').html(icon_text);
        });
        // Hide/show the Assay Details
        $("#collapse-button-assay-details a").on("click", function () {
            var icon_class = ($(".assay-details-wrapper #assay_details").is(":visible")) ? "icon-expand-alt" : "icon-collapse-alt";
            var icon_text = ($(".assay-details-wrapper #assay_details").is(":visible")) ? "Expand assay details" : "Collapse assay details";
            $('.assay-details-wrapper #assay_details').slideToggle();
            $('#collapse-button-assay-details a i').attr('class', icon_class);
            $('#collapse-button-assay-details span.toggle-text').html(icon_text);
        });
        // Hide/show the Assay Parameters
        $("#collapse-button-assay-parameters a").on("click", function () {
            var icon_class = ($(".assay-details-wrapper #assay_parameters").is(":visible")) ? "icon-expand-alt" : "icon-collapse-alt";
            var icon_text = ($(".assay-details-wrapper #assay_parameters").is(":visible")) ? "Expand assay parameters" : "Collapse assay parameters";
            $('.assay-details-wrapper #assay_parameters').slideToggle();
            $('#collapse-button-assay-parameters a i').attr('class', icon_class);
            $('#collapse-button-assay-parameters span.toggle-text').html(icon_text);

        });

        // Hide/show the Assay Multiplex
        $(".assay_multiplex").hide();
        $("#collapse-button-assay-multiplex a").on("click", function () {
            var icon_class = ($(".assay_multiplex").is(":visible")) ? "icon-expand-alt" : "icon-collapse-alt";
            var icon_text = ($(".assay_multiplex").is(":visible")) ? " Expand assay panel" : " Collapse assay panel";
            $('.assay_multiplex').slideToggle();
            $('#collapse-button-assay-multiplex a i').attr('class', icon_class);
            $('#collapse-button-assay-multiplex span.toggle-text').html(icon_text);
        });


        // Multiplex Panel Info
        $('#multiplex_info_icon').tooltip().on('click', function () {
            $('#multiplex_information').modal('show');
        });


        // Response Curves Info Modal
        $("#span-button-response-curves i").tooltip().on("click", function () {
            $('#response_curves_information').modal('show');
        });

        // Repeatability Info Modal
        $('#span-button-repeatability_info i').tooltip().on('click', function () {
            $('#repeatability_information').modal('show');
        });

        // [END] Miscellaneous ///////////////

        var all_in_one_data = [];
        var last_isoforms_x_plot = 4;
        var full_sequence = '{{ uniprot_api.sequence_raw }}';
        var sequence_max = '{{ uniprot_api.sequence_length }}';
        var uniprot_id = '{{ uniprot_api.uniprot_ac }}';


        // Get peptide sequences ///////////////

        var genes_data = JSON.parse('{{ gene_peptide_sequence|json_encode|raw }}');
        // Get plots table data ///////////////

        $.each(genes_data, function (index, single_gene_data) {
            var peptide_sequence_array = [];
            peptide_sequence_array.push({
                peptide_sequence: single_gene_data.peptide_sequence
                , peptide_modified_sequence: single_gene_data.peptide_modified_sequence
                , laboratory_abbreviation: single_gene_data.laboratory_abbreviation
                , celllysate_path: single_gene_data.celllysate_path
                , laboratory_id: single_gene_data.laboratories_id
                , import_log_id: single_gene_data.import_log_id
                , peptide_standard_purity_types_id: single_gene_data.peptide_standard_purity_types_id
                , library: 'ResponseCurve'
                , manage_id: single_gene_data.manage_id
            });

            get_plots_table_data(peptide_sequence_array);
        });


        // [BEGIN] SNPs Graph ///////////////
        var snps_data = JSON.parse('{{ uniprot_api.snps|json_encode|raw }}');
        var snps_data_length = snps_data ? snps_data.length : 0;

        if (snps_data) {
            var points = {show: true, radius: 3};
            var lines = {show: true, lineWidth: 4};
            var color = '#EC0000';

            $.each(snps_data, function (index, snp_data) {

                var snp_position = parseInt(snp_data.position);
                var snp_note = snp_data.original + ' &rarr; ' + snp_data.variation;

                // For all-in-one chart build
                all_in_one_data.push({
                    points: points
                    , lines: lines
                    , shadowSize: 0
                    , color: color
                    , data: [[snp_position, 5, snp_position + ': ' + snp_note, 'snp'],
                        [snp_position, 5, snp_position + ': ' + snp_note, 'snp']]
                });

            });

        }
        // [END] SNPs Graph ///////////////


        // [BEGIN] Isoforms Graph ///////////////
        var isoforms_data = JSON.parse('{{ uniprot_api.isoforms|json_encode|raw }}');
        var isoforms_data_length = isoforms_data ? isoforms_data.length : 0;
        if (isoforms_data_length > 0) {
            var isoform_array = new Array(); //create a new array to hold the values of the unique isoform_ids

            for (var key in isoforms_data) {
                var check_for_index = isoform_array.indexOf(isoforms_data[key].id);
                if (check_for_index >= 0) {

                } else {
                    isoform_array.push(JSON.stringify(isoforms_data[key].id)); //insert new key values
                }
            }
            var obj = new Object(); //create a json obj to hold the key and data values
            for (var key in isoforms_data) {
                var string_id = JSON.stringify(isoforms_data[key].id);
                obj[isoforms_data[key].id] = new Object();
                obj[isoforms_data[key].id]['data'] = new Object();
                obj[isoforms_data[key].id]['data']['regular_data'] = new Array();
                obj[isoforms_data[key].id]['data']['deletion_data'] = new Array();
                obj[isoforms_data[key].id]['data']['insertion_data'] = new Array();
            }
            for (var key in isoforms_data) { //loop through isoform_data to push data values for graphing
                for (var i = 0; i <= isoform_array.length; i++) {
                    if (isoform_array[i] == string_id) {
                        var this_id = isoforms_data[key].id;
                        var data_sequence = new Object();
                        var data_start_array = new Array();
                        var data_max_array = new Array();
                        var data_deletion_array = new Array();
                        var data_insertion_array = new Array();
                        var data_sequence_start = isoforms_data[key].sequence_start;
                        var data_sequence_max = isoforms_data[key].sequence_end;
                        var deletion = isoforms_data[key].deletion;
                        var insertion = isoforms_data[key].insertion_start;

                        data_sequence['data'] = new Array();

                        data_start_array.push(data_sequence_start, 7, data_sequence_start, isoforms_data[key].id, 'isoform');
                        data_max_array.push(data_sequence_max, 7, data_sequence_max, isoforms_data[key].id, 'isoform');

                        obj[isoforms_data[key].id]['data']['regular_data'].push(data_start_array, data_max_array, "null");
                        data_deletion_array.push(deletion, 7, deletion + "-deletion", isoforms_data[key].id, 'isoform');
                        obj[isoforms_data[key].id]['data']['deletion_data'].push(data_deletion_array);

                        data_insertion_array.push(insertion, 7, insertion + "-insertion", isoforms_data[key].id, 'isoform');
                        obj[isoforms_data[key].id]['data']['insertion_data'].push(data_insertion_array, "null");

                    }
                } //end for isoform_array
            } //end for isoforms_data
            var index2 = 0;

            for (var key in obj) {
                for (var val in obj[key]['data']['regular_data']) {
                    if (obj[key]['data']['regular_data'] && obj[key]['data']['regular_data'][val] !== 'null') {
                        obj[key]['data']['regular_data'][val][1] = (index2 + 7);
                    }
                }
                for (var val in obj[key]['data']['deletion_data']) {
                    if (obj[key]['data']['deletion_data']) {
                        obj[key]['data']['deletion_data'][val][1] = (index2 + 7);
                    }
                }
                for (var val in obj[key]['data']['insertion_data']) {
                    if (obj[key]['data']['insertion_data']) {
                        obj[key]['data']['insertion_data'][val][1] = (index2 + 7);
                    }
                }

                all_in_one_data.push({
                    points: {show: true, radius: 3}
                    , lines: {show: true, lineWidth: 2}
                    , shadowSize: 0
                    , color: '#EDC240'
                    , data: obj[key]['data']['regular_data']
                }); //end all_in_one_data
                all_in_one_data.push({
                    points: {show: true, radius: 3, fillColor: 'purple'}
                    , lines: {show: true, lineWidth: 2}
                    , shadowSize: 0
                    , color: 'purple'
                    , data: obj[key]['data']['deletion_data']
                }); //end all_in_one_data
                all_in_one_data.push({
                    points: {show: true, radius: 3, fillColor: 'grey'}
                    , lines: {show: true, lineWidth: 2}
                    , shadowSize: 0
                    , color: 'grey'
                    , data: obj[key]['data']['insertion_data']
                }); //end all_in_one_data
                index2 += 1;
            }

        }
        var x_position = index2;
        last_isoforms_x_plot = (x_position + 7);

        // [END] Isoforms Graph ///////////////

        // [BEGIN] Splice Junctions Graph ///////////////
        var splice_junctions_graph = $("#splice_junctions_graph");
        var splice_junctions_data = JSON.parse('{{ uniprot_api.splice_junctions|json_encode|raw }}');
        var splice_junctions_data_length = splice_junctions_data ? splice_junctions_data.length : 0;
        var last_x_plot = 0;

        if (splice_junctions_data) {

            var all_splice_junction_data = [];
            var points = {show: true, radius: 2};
            var lines = {show: true, lineWidth: 2};
            var color = 'green';

            $.each(splice_junctions_data, function (index, splice_junction_data) {

                last_x_plot = (last_isoforms_x_plot + index + 1);

                // sequence_max is the length of the sequence
                var data_sequence_max = sequence_max;
                var splice_junction_note = (splice_junction_data.type !== undefined) ? ' - ' + splice_junction_data.description + ' ' + splice_junction_data.type : '';

                // For splice junctions chart build
                all_in_one_data.push({
                    points: points
                    ,
                    lines: lines
                    ,
                    shadowSize: 0
                    ,
                    color: color
                    ,
                    data: [[splice_junction_data.start, ((last_isoforms_x_plot + index) + 1), splice_junction_data.start + splice_junction_note, 'splice_junction'], [splice_junction_data.stop, ((last_isoforms_x_plot + index) + 1), splice_junction_data.stop + splice_junction_note, 'splice_junction']]
                });
            });
        }

        // [END] Splice Junctions Graph ///////////////

        // [BEGIN] MRM Assays Graph ///////////////
        // Set up the peptide data for the data points
        var points = {show: false};
        var lines = {show: true, lineWidth: 3};
        var color = '#0066cc';
        var mrms_data = JSON.parse('{{ sequence_labs_array|json_encode|raw }}');
        var mrms_data_length = 0;
        var count = 0;

        var sequence_groups = mrms_data.total_sequences;
        var new_index = 1;

        $.each(mrms_data, function (index, mrm_data) {
            mrms_data_length = count++;
            if (!$.isArray(mrm_data)) {
                return;
            }
            $.each(mrm_data, function (single_index, single_mrm_data) {
                // For all-in-one chart build
                // new_index = (new_index > 3) ? 1.5 : new_index;

                if (sequence_groups > 1) {
                    if (new_index >= (sequence_groups + 1)) {
                        new_index = 1;
                        mrms_data_length = 0;
                    } else {
                        new_index = new_index + .5;
                    }
                } else {
                    new_index = single_index;
                }

                all_in_one_data.push({
                    points: points
                    , lines: lines
                    , color: color
                    , shadowSize: 0
                    , data: [
                        [single_mrm_data.peptide_start, (new_index), single_mrm_data.peptide_sequence + ' (' + single_mrm_data.submitting_laboratory + ')', single_mrm_data.peptide_sequence, 'mrm_assay', single_mrm_data.protein_id]
                        , [single_mrm_data.peptide_end, (new_index), single_mrm_data.peptide_sequence + ' (' + single_mrm_data.submitting_laboratory + ')', single_mrm_data.peptide_sequence, 'mrm_assay', single_mrm_data.protein_id]
                    ]
                });
            });
        });

        // [END] MRM Assays Graph ///////////////


        var all_in_one_graph = $("#all_in_one_graph");
        var all_in_one_graph_max = ((mrms_data_length * 2) + ((isoforms_data_length / 10) + splice_junctions_data_length) + 6);

        // Plot all data
        var all_in_one_plot = $.plot(all_in_one_graph, all_in_one_data, {
            yaxis: {show: false, min: 0, max: all_in_one_graph_max, panRange: [0, all_in_one_graph_max]}, //
            xaxis: {show: true, min: 0, max: sequence_max, tickSize: 100, panRange: [0, sequence_max]}, //
            grid: {
                show: true,
                hoverable: true,
                clickable: true,
                borderWidth: {top: 0, right: 0, bottom: 1, left: 0},
                color: '#999'
            },
            zoom: {interactive: true},
            pan: {interactive: true}
        });

        // Chart Legend
        all_in_one_graph.append('<div class="chart-legend sequence-domains-chart-legend" title="Click to go to data source (Uniprot)"></div>');
        all_in_one_graph.append('<div class="chart-legend-text sequence-domains-chart-legend-text" title="Click to go to data source (Uniprot)">Sequence Domains</div>');
        all_in_one_graph.append('<div class="chart-legend isoforms-chart-legend" title="Click to go to data source (Uniprot)" ></div>');
        all_in_one_graph.append('<div class="chart-legend-text isoforms-chart-legend-text" title="Click to go to data source (Uniprot)">Isoforms</div>');
        all_in_one_graph.append('<div class="chart-legend snps-chart-legend" title="Click to go to data source (Uniprot)"></div>');
        all_in_one_graph.append('<div class="chart-legend-text snps-chart-legend-text" title="Click to go to data source (Uniprot)">SNPs</div>');
        all_in_one_graph.append('<div class="chart-legend mrm-assays-chart-legend"></div>');
        all_in_one_graph.append('<div class="chart-legend-text mrm-assays-chart-legend-text">Targeted MS Assays</div>');

        // Chart legend links
        $('.sequence-domains-chart-legend, .sequence-domains-chart-legend-text').on('click', function (event) {
            var url_sequence_domains = 'http://www.uniprot.org/uniprot/' + uniprot_id + '#section_features';
            window.open(url_sequence_domains, '_blank');
        });
        $('.isoforms-chart-legend, .isoforms-chart-legend-text').on('click', function (event) {
            var url_isoforms = 'http://www.uniprot.org/uniprot/' + uniprot_id + '#' + uniprot_id + '-1';
            window.open(url_isoforms, '_blank');
        });
        $('.snps-chart-legend, .snps-chart-legend-text').on('click', function (event) {
            var url_snps = 'http://www.uniprot.org/uniprot/' + uniprot_id + '';
            window.open(url_isoforms, '_blank');
        });

        ///////////////////////////////////////////////////////////

        // Set the height of the chart
        var graph_height = ((last_x_plot * 30) != 0) ? (last_x_plot * 30) : 300;
        $(all_in_one_graph).css('height', graph_height); // (all_in_one_data.length * 11)

        // Add the zoom in button
        $("<div class='button' style='right:90px;top:0px;border:none;margin-right:10px;'>zoom in</div>")
            .appendTo(all_in_one_graph)
            .click(function (event) {
                event.preventDefault();
                all_in_one_plot.zoom();
            });

        // Add the zoom out button
        $("<div class='button' style='right:20px;top:0px;border:none;margin-left:5px;'>zoom out</div>")
            .appendTo(all_in_one_graph)
            .click(function (event) {
                event.preventDefault();
                all_in_one_plot.zoomOut();
            });

        // Helper for taking the repetitive work out of placing panning arrows
        function addArrow(dir, right, top, offset) {
            $("<img class='button' src='{{ path_to_this_module }}/library/images/arrow-" + dir + ".gif' style='right:" + right + "px;top:" + top + "px; background-color:transparent;border:none;'>")
                .appendTo(all_in_one_graph)
                .click(function (e) {
                    e.preventDefault();
                    all_in_one_plot.pan(offset);
                });
        }

        addArrow("left", 55, 40, {left: -100});
        addArrow("right", 25, 40, {left: 100});
        addArrow("up", 40, 25, {top: -100});
        addArrow("down", 40, 55, {top: 100});

        // Disable the mouse wheel functionality for panning and zooming
        $('#all_in_one_graph canvas.flot-overlay').unmousewheel();

        $(all_in_one_graph).bind("plotclick", function (event, pos, item) {
            // Splice Junctions
            // http://www.uniprot.org/uniprot/P04626#section_features
            if ((item != null) && (item.series.data[0][3] != null) && (item.series.data[0][3] == 'splice_junction')) {
                var url = 'http://www.uniprot.org/uniprot/' + uniprot_id + '#section_features';
                window.open(url, '_blank');
            }
            // Isoforms
            if ((item != null) && (item.series.data[0][4] != null) && (item.series.data[0][4] == 'isoform')) {
                var url = 'http://www.uniprot.org/uniprot/' + item.series.data[item.dataIndex][3];
                window.open(url, '_blank');
            }
            // SNPs
            if ((item != null) && (item.series.data[0][3] != null) && (item.series.data[0][3] == 'snp')) {
                var url = 'http://www.uniprot.org/blast/?about=' + uniprot_id + '[' + item.datapoint[0] + ']';
                window.open(url, '_blank');
            }
            // MRM Assays
            if ((item != null) && (item.series.data[0][4] != null) && (item.series.data[0][4] == 'mrm_assay')) {

                var this_peptide_sequence = item.series.data[item.dataIndex][3];
                var this_protein_id = item.series.data[item.dataIndex][5];
                var div_id = '.' + this_protein_id;
                // Change our States (URL, title in title bar)
                History.replaceState({state: 1}, "{{ gene }}, CPTAC-" + this_protein_id + " - CPTAC Assay Portal", "CPTAC-" + this_protein_id);
                // Scroll to assays area
                scrollToAnchor('assay_details_anchor');
                $('.assay-details-wrapper').hide();
                $('#preloader_loading_assay_details').fadeIn(500);
                $('#preloader_loading_assay_details').hide();
                $(div_id).fadeIn(300);
                // Load Disqus commenting
                loadDisqus($('#comments_container_' + this_protein_id), this_peptide_sequence, 'http://{{ server_name }}{{ request_uri }}/#!' + this_peptide_sequence);
            }

        });

        ///////////////////////////////////////////////////////////

        // [BEGIN] Highlighted Peptide Sequence Links ///////////////
        // Use sequence values from the 'genes_distinct' array
        var mouseY;
        var mouseX;
        $(document).mousemove(function (e) {
            mouseX = e.pageX;
            mouseY = e.pageY;
        });

        var starts = JSON.parse('{{ distinct_gene_peptide_sequence|json_encode|raw }}');
        var last_clicked = false;
        $.each(starts, function (index, starts_data) {
            // On click event for peptide sequence links in the gene sequence
            var this_peptide_sequence = starts_data.peptide_sequence;
            var this_protein_id = starts_data.manage;
            var div_id = '.' + this_protein_id;

            var clickable_class = 'seq-' + this_peptide_sequence;

            $('.' + clickable_class).on('click', function () {

                $("rect, line, .close").tooltip();

                if (last_clicked != this_peptide_sequence) {
                    $('.detail-modal').attr('style', 'display: none');
                    last_clicked = false;
                }

                var div_height = $('.all-details-' + this_peptide_sequence).height();
                var offset = (div_height > 25) ? 450 : 390;
                $('.all-details-' + this_peptide_sequence).css({'top': mouseY - offset}).fadeIn('fast');

                $('.detail-modal .close').on('click', function () {
                    $('.detail-modal').fadeOut('fast');
                });
            });

            var detail_link_class = this_peptide_sequence + '-' + this_protein_id;
            $('.protein-sequence').on('click', '.' + detail_link_class, function () {

                // close the pop up
                $('.detail-modal').fadeOut('fast');

                // Change our States (URL, title in title bar)
                History.replaceState({state: 1}, "{{ gene }}, CPTAC-" + this_protein_id + " - CPTAC Assay Portal", "CPTAC-" + this_protein_id);
                // Scroll to assays area
                scrollToAnchor('assay_details_anchor');
                $('.assay-details-wrapper').hide();
                $('#preloader_loading_assay_details').fadeIn(500);
                $('#preloader_loading_assay_details').hide();
                $(div_id).fadeIn(300);

                // Load Disqus commenting
                loadDisqus($('#comments_container_' + this_protein_id), this_peptide_sequence, 'http://{{ server_name }}{{ request_uri }}/#!' + this_peptide_sequence);
            });
        });


        // $("rect, line, .peptide_highlight").tooltip({
        //   'container': 'body',
        //   'placement': 'top'
        // });


        // [END] Highlighted Peptide Sequence Links ///////////////


        // Tooltips on hover of data points
        var previousPoint = null;
        $(all_in_one_graph).bind("plothover", function (event, pos, item) {
            if (item) {
                document.body.style.cursor = 'pointer';
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $("#tooltip").remove();
                    showTooltip(item.pageX, item.pageY, item.series.data[item.dataIndex][2]);
                }
            } else {
                document.body.style.cursor = 'default';
                $("#tooltip").remove();
                previousPoint = null;
            }
        });

        // Pop-out entire sequence
        $('div.sequence').on('mouseenter', '.protein-sequence', function (event) {
            $(this).attr('style', 'overflow: visible;');
            $('table.sequence').addClass('sequence_table_shadow');
        });
        $('div.sequence').on('mouseleave', '.protein-sequence', function (event) {
            $(this).attr('style', 'overflow: hidden;');
            $('table.sequence').removeClass('sequence_table_shadow');
            $('.detail-modal').attr('style', 'display: none');
        });

        // Tooltips
        $('.span4 img, .chart-legend, .chart-legend-text').tooltip();


        // Show the Assay details for the page id we're on
        var details_div_id = $('#outer-wrapper .{{ manage }}');
        $(details_div_id).show();
        loadDisqus($('#comments_container_{{ manage }}'), '{{ peptide_sequence }}', 'http://{{ server_name }}{{ request_uri }}/#!{{ peptide_sequence }}');

        // History.js
        (function (window, undefined) {
            // Bind to StateChange Event
            History.Adapter.bind(window, 'statechange', function () { // Note: We are using statechange instead of popstate
                var State = History.getState(); // Note: We are using History.getState() instead of event.state
            });
        })(window);

        $.ajax({
            type: "GET"
            , dataType: "html"
            , url: "{{ path_to_this_module }}/get_protein_map_svg"
            , data: ({uniprot_ac_id: uniprot_id})
            , success: function (svg_return) {
                d3.select("#proteincartoon").html(svg_return);
            }
        });

        $('#proteincartoon').click(function () {
            var win = window.open('http://www.phosphosite.org/uniprotAccAction.do?id=' + uniprot_id, '_blank');
            win.focus();
        });
    });

    // FUNCTIONS ///////////////

    function scrollToAnchor(aid) {
        var aTag = $("a[name='" + aid + "']");
        $('html,body').animate({scrollTop: aTag.offset().top}, 'slow');
    }

    function showTooltip(x, y, contents) {
        $("<div id='tooltip'>" + contents + "</div>").css({
            position: "absolute",
            display: "none",
            top: y - 28,
            left: x + 8,
            border: "1px solid #000",
            padding: "4px",
            "background-color": "#000",
            opacity: 0.80,
            color: "#fff"
        }).appendTo("body").fadeIn(200);
    }


    function get_plots_table_data(genes) {

        // If we're using an element repededly, let's create variables
        var response_curve_message = '<p>Table data unavailable</p>';
        // Display the preloaders
        $.each(genes, function (index, gene) {
            $('div.span12.' + gene.peptide_sequence + '_' + gene.laboratory_abbreviation + ' .loc_lloq_preloader_loading').show();
        });

        // Send the request via AJAX
        $.ajax({
            url: "{{ path_to_this_module }}/get_plots_table_data"
            , dataType: "json"
            , type: "post"
            , data: {genes: JSON.stringify(genes)}
            , success: function (data) {
                if (data) {
                    $.each(data, function (index, single_gene_data) {

                        /*
                         * Build out the LOC/LOQ data table
                         */

                        if (single_gene_data.lod_loq_comparison_data[0].length) {

                            var crude = (single_gene_data.peptide_standard_purity_types_id == 3) ? true : false;
                            var peptide_sequence = single_gene_data.lod_loq_comparison_data[0][0].peptide;
                            var lod_loq_units = single_gene_data.lod_loq_comparison_data[0][0].lod_loq_units;
                            var crude_style = (crude) ? 'class="crude-header"' : '';
                            var lod_loq_units_note = (crude) ? '<br /><small><span ' + crude_style + '>Estimated based on crude peptide concentration</span></small>' : '';

                            // Strip brackets from the modified peptide sequence.
                            var peptide_sequence_stripped = peptide_sequence.replace(/[\[\+\d+\]]/g, '');
                            var table = $('<table/>').addClass('table table-bordered table-striped table-condensed').attr('id', single_gene_data.manage_id + '_loc_loq');

                            var table_header_row = $('<tr />');
                            var table_headers = $('<th colspan="2">&nbsp;</th><th colspan="3">LOD (' + lod_loq_units + ')' + lod_loq_units_note + '</th><th class="lloq_header" colspan="3">LLOQ (' + lod_loq_units + ')' + lod_loq_units_note + '</th>');

                            table_header_row.append(table_headers);
                            table.append(table_header_row);

                            var table_top_row = $("<tr/>");
                            var labels = [
                                "Peptide"
                                , "Transition"
                                , "blank only"
                                , "blank+low-conc"
                                , "rsd limit"
                                , "blank only"
                                , "blank+low-conc"
                                , "rsd limit"
                            ];
                            // Stitch together the table headers row
                            $.each(labels, function (index, label) {
                                table_top_row.append('<td><strong>' + label + '</strong></td>');
                            });

                            table.append(table_top_row);

                            $.each(single_gene_data.lod_loq_comparison_data, function (index, single_data) {

                                var lod_loq_rowspan = single_data.length;

                                $.each(single_data, function (idx, single) {

                                    var transition_id = (single.transition_id == '.Sum.tr.') ? 'Sum' : single.transition_id;
                                    var table_row = $("<tr/>");
                                    table.append(table_row);

                                    var a = (single.blank_only_LOD != 'NA')
                                        ? Number(single.blank_only_LOD).toExponential(1)
                                        : single.blank_only_LOD;
                                    var b = (single.blank_low_conc_LOD != 'NA')
                                        ? Number(single.blank_low_conc_LOD).toExponential(1)
                                        : single.blank_low_conc_LOD;
                                    var c = (single.rsd_limit_LOD != 'NA')
                                        ? Number(single.rsd_limit_LOD).toExponential(1)
                                        : single.rsd_limit_LOD;
                                    var d = (single.blank_only_LOQ != 'NA')
                                        ? Number(single.blank_only_LOQ).toExponential(1)
                                        : single.blank_only_LOQ;
                                    var e = (single.blank_low_conc_LOQ != 'NA')
                                        ? Number(single.blank_low_conc_LOQ).toExponential(1)
                                        : single.blank_low_conc_LOQ;
                                    var f = (single.rsd_limit_LOQ != 'NA')
                                        ? Number(single.rsd_limit_LOQ).toExponential(1)
                                        : single.rsd_limit_LOQ;


                                    var peptide_sequence_label = (index == 0) ? peptide_sequence : '';

                                    if (idx == 0) {
                                        table_row.append('<td rowspan="' + lod_loq_rowspan + '">' + peptide_sequence_label + '</td>');
                                    }
                                    table_row.append('<td>' + transition_id + '</td>');
                                    table_row.append('<td>' + a + '</td>');
                                    table_row.append('<td>' + b + '</td>');
                                    table_row.append('<td>' + c + '</td>');
                                    table_row.append('<td>' + d + '</td>');
                                    table_row.append('<td>' + e + '</td>');
                                    table_row.append('<td>' + f + '</td>');


                                });
                            });

                            // Remove preloader
                            $('div.span12.' + single_gene_data.manage_id + '_' + single_gene_data.laboratory_abbreviation + '_' + single_gene_data.manage_id + ' .loc_lloq_preloader_loading').remove();
                            // Load the table
                            $('div.span12.' + single_gene_data.manage_id + '_' + single_gene_data.laboratory_abbreviation + '_' + single_gene_data.manage_id).append(table);

                        }

                        if (single_gene_data.response_curves_data[0].length) {

                            /*
                             * Build out the Curve Fit table
                             */

                            var peptide_sequence = single_gene_data.response_curves_data[0][0].peptide;
                            // Strip brackets from the modified peptide sequence.
                            var peptide_sequence_stripped = peptide_sequence.replace(/[\[\+\d+\]]/g, '');
                            var table = $('<table/>').addClass('table table-bordered table-striped table-condensed')
                                .attr('id', peptide_sequence_stripped + '_response_curves')
                                .attr('style', 'margin-top:40px;');

                            var table_header_row = $("<tr/>");
                            var table_headers = $('<th colspan="2">&nbsp;</th><th colspan="3">Curve Fit</th>');
                            table_header_row.append(table_headers);
                            table.append(table_header_row);

                            // Response curves data table
                            var table_top_row = $("<tr/>");
                            var labels = [
                                "Peptide"
                                , "Transition"
                                , "slope"
                                , "intercept"
                                , "r squared"
                            ];
                            // Stitch together the table headers row
                            $.each(labels, function (index, label) {
                                table_top_row.append('<td><strong>' + label + '</strong></td>');
                            });

                            table.append(table_top_row);

                            $.each(single_gene_data.response_curves_data, function (index, single_data) {

                                var curve_fit_rowspan = single_data.length;

                                $.each(single_data, function (idx, single) {

                                    var transition_id = (single.transition_id == '.Sum.tr.') ? 'Sum' : single.transition_id;
                                    var table_row = $("<tr/>");
                                    table.append(table_row);

                                    if (idx == 0) {
                                        table_row.append('<td rowspan="' + curve_fit_rowspan + '">' + single.peptide + '</td>');
                                    }
                                    table_row.append('<td>' + transition_id + '</td>');
                                    table_row.append('<td>' + single.Slope + '</td>');
                                    table_row.append('<td>' + single.Intercept + '</td>');
                                    table_row.append('<td>' + single.RSquare + '</td>');
                                });
                            });

                            // Remove preloader
                            $('div.span12.' + single_gene_data.manage_id + '_' + single_gene_data.laboratory_abbreviation + '_' + single_gene_data.manage_id + ' .loc_lloq_preloader_loading').remove();
                            // Load the table
                            $('div.span12.' + single_gene_data.manage_id + '_' + single_gene_data.laboratory_abbreviation + '_' + single_gene_data.manage_id).append(table);

                            // Response Curves LLOQ header notice
                            var high_values = $('#' + single_gene_data.manage_id + '_validation_sample_data_' + single_gene_data.manage_id + ' tbody tr td').find('.red');

                            if (high_values.length) {

                                // try to prevent duplicates
                                $('#outer-wrapper #' + single_gene_data.manage_id + '_loc_loq .lloq_header .icon-exclamation-sign').remove();

                                $('#outer-wrapper #' + single_gene_data.manage_id + '_loc_loq .lloq_header').append(' <i class="icon-exclamation-sign red" title="Repeatability shows greater than 20% CV"></i>');
                                $('.icon-exclamation-sign').tooltip();
                            }
                        }

                        if ((single_gene_data.lod_loq_comparison_data[0] == 0) && (single_gene_data.response_curves_data[0].length == 0)) {
                            $.each(genes, function (index, gene) {
                                var div_wrapper = $('div.span12.' + single_gene_data.peptide_sequence + '_' + single_gene_data.laboratory_abbreviation);
                                // Remove preloader
                                $('.loc_lloq_preloader_loading').remove();
                                $(div_wrapper).append('<p style="text-align:center; margin-top:200px;">Response Curves Data Unavailable</p>');
                            });
                        }

                    });

                }

            }

        });
    }
</script>
{% endblock %}
