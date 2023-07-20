<?php

namespace assays_import\controllers;

use \PDO;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays\models\Assay;

class FixUniprotImport extends Controller {

    function fix_uniprot_import(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $xml2array = new \swpg\models\XML2Array();
        $assay = new Assay($db_resource);

        $import_log_id = $args['import_log_id'];

        $limit = false; //"LIMIT 5";
        $where = "AND chromosome_number IS NULL";

        function stringToArray($string, $delimiter = ' [') {
            $data = explode($delimiter, $string);
            $array_1 = array();
            foreach ($data as $key => $value)
                $array_1[$key] = rtrim($value, ']');
            foreach ($array_1 as $value) {
                $this_rec = explode(': ', $value);
                if (isset($this_rec[1]))
                    $output[$this_rec[0]] = $this_rec[1];
            }
            return $output;
        }


        $url = 'http://biodbnet.abcc.ncifcrf.gov/webServices/rest.php/';
        $query = 'biodbnetRestApi.json?method=db2db&input=UniProt%20Accession&inputValues=[uniprot_accession_id]&outputs=Ensembl%20Gene%20Info,geneinfo,UniProt%20Info,Chromosomal%20Location,KEGG%20Pathway%20Info&taxonId=9606';
        $url = $url . $query;


        // get all protein records
        $sql = "SELECT protein_id, uniprot_accession_id
          FROM protein 
          WHERE 1 = 1
          {$where}
          AND import_log_id = :import_log_id
          {$limit}";

        $statement = $db_resource->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $records = $statement->fetchAll(PDO::FETCH_ASSOC);

        write_log($import_log_id, "Uniprot fix - Found " . count($records) . " to update.");

        foreach ($records as $key => $value) {

            write_log($import_log_id, "Uniprot fix - Getting: " . $value['uniprot_accession_id']);

            $uniprot = $assay->get_assay_by_uniprot_api(
                $import_log_id
                , $value['uniprot_accession_id']
                , $final_global_template_vars["uniprot_protein_api_url"]
                , $xml2array
                , $final_global_template_vars["uniprot_regions_array"]
                , false
                , false
                , false
            );


            write_log($import_log_id, "Uniprot fix - Looking in BioDbNet");

            // biodbnet
            $this_url = str_replace("[uniprot_accession_id]", $value['uniprot_accession_id'], $url);
            $data = false;
            $data = @file_get_contents($this_url);
            $data = json_decode($data, true);
            $output = array();
            $update_fields = array();

            if (isset($data[0]['outputs'])) {
                $data = $data[0]['outputs'];


                if (isset($data['Gene Info'][0])) {
                    $output['gene'] = stringToArray($data['Gene Info'][0]);
                    $update_fields[] = "gene_symbol = '" . $output['gene']["Gene Symbol"] . "'";
                    $update_fields[] = "chromosome_number = '" . $output['gene']["Chromosome"] . "'";

                    write_log($import_log_id, "BioDbNet Found");

                } else {
                    write_log($import_log_id, "BioDbNet Not Found");
                    //$update_fields[] = "gene_symbol = NULL";
                    //$update_fields[] = "chromosome_number = NULL";
                }

                // $output['Ensembl'] = stringToArray($data['Ensembl Gene Info'][0]);
                // $output['Ensembl_2'] = stringToArray($data['Ensembl Gene Info'][1]);
                // $output['UniProt'] = stringToArray($data['UniProt Info'][0]);

                if (isset($data['Chromosomal Location'][0])) {
                    $output['Chromosomal'] = stringToArray($data['Chromosomal Location'][0]);
                    $update_fields[] = "chromosome_start = '" . $output['Chromosomal']["chr_start"] . "'";
                    $update_fields[] = "chromosome_stop = '" . $output['Chromosomal']["chr_end"] . "'";

                } else {
                    //$update_fields[] = "chromosome_start = NULL";
                    //$update_fields[] = "chromosome_stop = NULL";
                }
                // $output['KEGG'] = stringToArray($data['KEGG Pathway Info'][0]);
            } else {
                // $update_fields[] = "gene_symbol = NULL";
                // $update_fields[] = "chromosome_number = NULL";
                // $update_fields[] = "chromosome_start = NULL";
                // $update_fields[] = "chromosome_stop = NULL";
            }


            if ($uniprot) {

                // gene_synonym
                $uniprot['gene_synonym'] = is_array($uniprot['gene_synonym']) ? $uniprot['gene_synonym'] : array();
                $gene_synonym = array();
                foreach ($uniprot['gene_synonym'] as $key_syn => $sym_value)
                    $gene_synonym[] = $sym_value;
                $update_fields[] = "uniprot_gene_synonym = '" . implode(",", $gene_synonym) . "'";

                // uniprot_source_taxon_id
                $update_fields[] = "uniprot_source_taxon_id = '" . $uniprot['source_taxon_id'] . "'";

                // uniprot_sequence
                $update_fields[] = "uniprot_sequence = '" . $uniprot['sequence'] . "'";

                // uniprot_sequence_raw
                $update_fields[] = "uniprot_sequence_raw = '" . $uniprot['sequence_raw'] . "'";

                // uniprot_kb
                $update_fields[] = "uniprot_kb = '" . $uniprot['uniprot_kb'] . "'";

                // uniprot_hgnc_gene_id
                if (isset($uniprot['hgnc_gene_id'])) {
                    $update_fields[] = "uniprot_hgnc_gene_id = '" . $uniprot['hgnc_gene_id'] . "'";
                }

                // uniprot_protein_name
                $uniprot['protein_name'] = isset($uniprot['protein_name']) ? $uniprot['protein_name'] : NULL;
                $update_fields[] = "uniprot_protein_name = '" . $uniprot['protein_name'] . "'";

                // sequence_length
                $uniprot['sequence_length'] = isset($uniprot['sequence_length']) ? $uniprot['sequence_length'] : NULL;
                $update_fields[] = "uniprot_sequence_length = '" . $uniprot['sequence_length'] . "'";

                // protein_molecular_weight
                $uniprot['mass'] = isset($uniprot['mass']) ? $uniprot['mass'] : NULL;
                $update_fields[] = "protein_molecular_weight = '" . $uniprot['mass'] . "'";

            }

            $sql = "UPDATE protein SET " . implode(", ", $update_fields) . " WHERE protein_id = " . $value['protein_id'];
            $statement = $db_resource->prepare($sql);
            //$statement->bindValue(":protein_id", , PDO::PARAM_INT);
            $statement->execute();

            write_log($import_log_id, "Updated - Protein ID: " . $value['protein_id']);

        }


        // get all peptide records
        $sql = "SELECT analyte_peptide.analyte_peptide_id, 
                   analyte_peptide.peptide_sequence, 
                   protein.uniprot_accession_id, 
                   protein.uniprot_sequence_raw as sequence_raw,
                   analyte_peptide.site_of_modification_peptide
          FROM analyte_peptide 
          LEFT JOIN protein ON protein.protein_id = analyte_peptide.protein_id
          WHERE 1 = 1
          -- AND analyte_peptide.peptide_start = '' AND analyte_peptide.peptide_end = ''
          AND analyte_peptide.import_log_id = :import_log_id
          {$limit}";

        $statement = $db_resource->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $peptides = $statement->fetchAll(PDO::FETCH_ASSOC);


        foreach ($peptides as $key => $value) {
            $peptide_start_pos = strpos($value["sequence_raw"], $value['peptide_sequence']);
            $peptide_start = ($peptide_start_pos + 1);
            $peptide_end = $peptide_start_pos + strlen($value['peptide_sequence']);

            $modification_sites = explode(", ", $value["site_of_modification_peptide"]);
            foreach ($modification_sites as $modification_key => $modification_value) {
                $modification_sites[$modification_key] = $modification_value + $peptide_start;
            }

            $sql = "UPDATE analyte_peptide 
                SET peptide_start = :peptide_start, 
                    peptide_end = :peptide_end, 
                    analyte_peptide.site_of_modification_protein = :site_of_modification_protein  
                WHERE analyte_peptide_id = :analyte_peptide_id";

            $statement = $db_resource->prepare($sql);
            $statement->bindValue(":peptide_start", $peptide_start, PDO::PARAM_INT);
            $statement->bindValue(":peptide_end", $peptide_end, PDO::PARAM_INT);
            $statement->bindValue(":analyte_peptide_id", $value['analyte_peptide_id'], PDO::PARAM_INT);
            $statement->bindValue(":site_of_modification_protein", implode(", ", $modification_sites));
            $statement->execute();

            write_log($import_log_id, "Updated - analyte_peptide table analyte_peptide_id: " . $value['analyte_peptide_id']);

        }
    }

}