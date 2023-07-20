<?php
// Frontend-related
$container['DatatablesBrowseAssays'] = function ($container) {
    return new assays\controllers\DatatablesBrowseAssays($container);
};

$app->post("/datatables_browse_assays", DatatablesBrowseAssays::class . ":datatables_browse_assays");

$container['DownloadFile'] = function ($container) {
    return new assays\controllers\DownloadFile($container);
};
$app->get("/download_file[/{sop_files_id}]", DownloadFile::class . ":download_file");

$container['GetKeggSvg'] = function ($container) {
    return new assays\controllers\GetKeggSvg($container);
};
$app->get("/get_kegg_svg", GetKeggSvg::class . ":get_kegg_svg");

$container['GetWikiSvg'] = function ($container) {
    return new assays\controllers\GetWikiSvg($container);
};
$app->get("/get_wiki_svg", GetWikiSvg::class . ":get_wiki_svg");

$container['GetProteinMapSvg'] = function ($container) {
    return new assays\controllers\GetProteinMapSvg($container);
};
$app->get("/get_protein_map_svg", GetProteinMapSvg::class . ":get_protein_map_svg");

$container['GetAssaysByGeneSymbol'] = function ($container) {
    return new assays\controllers\GetAssaysByGeneSymbol($container);
};
$app->post("/get_assays_by_gene_symbol", GetAssaysByGeneSymbol::class . ":get_assays_by_gene_symbol");

// Import-related
//$app->get("/import_entrez_genomic_context(/)", "import_entrez_genomic_context");
//$app->get("/import_kegg(/)", "import_kegg_data");
//$app->get("/delete_interlab_data(/)", "delete_interlab_data");

$container['GetPlotsTableData'] = function ($container) {
    return new assays\controllers\GetPlotsTableData($container);
};
$app->post("/get_plots_table_data", GetPlotsTableData::class . ":get_plots_table_data");

// LinkOut scripts, run by cron script. (http://www.ncbi.nlm.nih.gov/projects/linkout/)
// Frequency: once a month.
//$app->get("/linkout_assays(/)", "linkout_assays");

$container['Statistics'] = function ($container) {
    return new assays\controllers\Statistics($container);
};
$app->get("/statistics", Statistics::class . ":statistics");

$container['ExportCsv'] = function ($container) {
    return new assays\controllers\ExportCsv($container);
};
$app->post("/export_csv", ExportCsv::class . ":export_csv");

$container['ExportMultiplex'] = function ($container) {
    return new assays\controllers\ExportMultiplex($container);
};
$app->post("/export_multiplex", ExportMultiplex::class . ":export_multiplex");

$container['ExportUniProtCsv'] = function ($container) {
    return new assays\controllers\ExportUniProtCsv($container);
};
$app->get("/export_uniprot", ExportUniProtCsv::class . ":export_uniprot");

$container['Home'] = function($container) {
    return new assays\controllers\Home($container);
};
$app->get('/home', Home::class . ':home');

$container['WikiPathway'] = function($container) {
    return new assays\controllers\WikiPathway($container);
};
$app->get('/wikipathway', WikiPathway::class . ':home');