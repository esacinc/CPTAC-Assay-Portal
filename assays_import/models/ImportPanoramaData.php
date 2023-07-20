<?php
/**
 * @desc Import data from Panorama into CPTAC's Assay Portal database
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 2.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

class ImportPanoramaData {

    public $db;

    public function __construct($db_connection = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
    }


    public function db_error($handle) {
        die("DB ERROR");
    }


    public function truncate_all() {
        $statement = $this->db->prepare("SET foreign_key_checks = 0");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE protein");
        $statement->execute();
        $statement = $this->db->prepare("SET foreign_key_checks = 1");
        $statement->execute();
        $statement = $this->db->prepare("SET foreign_key_checks = 0");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE analyte_peptide");
        $statement->execute();
        $statement = $this->db->prepare("SET foreign_key_checks = 1");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE uniprot_isoforms");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE uniprot_snps");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE uniprot_splice_junctions");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE lod_loq_comparison");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE response_curves_data");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE uniprot_splice_junctions");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE panorama_chromatogram_images");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE panorama_response_curve_images");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE panorama_validation_sample_data");
        $statement->execute();
        $statement = $this->db->prepare("TRUNCATE TABLE panorama_validation_sample_images");
        $statement->execute();
    }


    public function do_initial_inserts($data = false) {

        $statement = $this->db->prepare("
          INSERT INTO protein
          ( gene_symbol, import_log_id, uniprot_accession_id, guidance_document_version, created_date )
          VALUES ( :gene_symbol, :import_log_id, :uniprot_accession_id, :guidance_document_version, NOW() )");


        $data["gene_symbol"] = !empty($data["gene_symbol"]) ? $data["gene_symbol"] : false;

        $statement->bindValue(":gene_symbol", $data["gene_symbol"], PDO::PARAM_STR);
        $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
        $statement->bindValue(":uniprot_accession_id", $data["uniprot_lookup_id"], PDO::PARAM_STR);
        $statement->bindValue(":guidance_document_version", "1.0", PDO::PARAM_STR);
        $statement->execute();

        $last_inserted_protein_id = $this->db->lastInsertId();

        // Update, adding the cptac_id, using the last inserted id as the numeric base.
        $statement = $this->db->prepare("
          UPDATE protein
          SET cptac_id = :cptac_id
          WHERE protein_id = :protein_id");
        $statement->bindValue(":cptac_id", "CPTAC-" . $last_inserted_protein_id, PDO::PARAM_STR);
        $statement->bindValue(":protein_id", $last_inserted_protein_id, PDO::PARAM_INT);
        $statement->execute();


        $analyte_peptide_db_array = array(
            'protein_id'
        , 'import_log_id'
        , 'peptide_molecular_weight'
        , 'peptide_start'
        , 'peptide_end'
        , 'modification_type'
        , 'site_of_modification_peptide'
        , 'peptide_sequence'
        , 'peptide_modified_sequence'
        , 'hydrophobicity'
        , 'panorama_peptide_url'
        , 'panorama_protein_url'
        , 'panorama_created_date'
        );

        $analyte_peptide_columns = implode(", ", $analyte_peptide_db_array);
        $analyte_peptide_placeholders = ":" . implode(", :", $analyte_peptide_db_array);
        foreach ($analyte_peptide_db_array as $single_column) {
            $analyte_peptide_update[] = $single_column . " = :" . $single_column;
        }


        // Insert into the analyte_peptide table.
        $statement = $this->db->prepare("
          INSERT INTO analyte_peptide
          (" . $analyte_peptide_columns . ")
          VALUES (" . $analyte_peptide_placeholders . ")
        ");


        $data["peptide_molecular_weight"] = isset($data["peptide_molecular_weight"]) ? $data["peptide_molecular_weight"] : false;
        $data["peptide_start"] = isset($data["peptide_start"]) ? $data["peptide_start"] : false;
        $data["peptide_end"] = isset($data["peptide_end"]) ? $data["peptide_end"] : false;
        $data["modification_type"] = isset($data["modification_type"]) ? $data["modification_type"] : false;
        $data["site_of_modification_peptide"] = isset($data["site_of_modification_peptide"]) ? $data["site_of_modification_peptide"] : false;
        $data["peptide_sequence"] = isset($data["peptide_sequence"]) ? $data["peptide_sequence"] : false;
        $data["peptide_modified_sequence"] = isset($data["peptide_modified_sequence"]) ? $data["peptide_modified_sequence"] : false;
        $data["hydrophobicity"] = isset($data["hydrophobicity"]) ? $data["hydrophobicity"] : false;
        $data["panorama_peptide_url"] = isset($data["panorama_peptide_url"]) ? $data["panorama_peptide_url"] : false;
        $data["panorama_created_date"] = isset($data["panorama_created_date"]) ? $data["panorama_created_date"] : false;

        $statement->bindValue(":protein_id", $last_inserted_protein_id, PDO::PARAM_INT);
        $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
        $statement->bindValue(":peptide_molecular_weight", $data["peptide_molecular_weight"], PDO::PARAM_STR);
        $statement->bindValue(":peptide_start", $data["peptide_start"], PDO::PARAM_INT);
        $statement->bindValue(":peptide_end", $data["peptide_end"], PDO::PARAM_INT);
        $statement->bindValue(":modification_type", $data["modification_type"], PDO::PARAM_STR);
        $statement->bindValue(":site_of_modification_peptide", $data["site_of_modification_peptide"], PDO::PARAM_STR);
        $statement->bindValue(":peptide_sequence", $data["peptide_sequence"], PDO::PARAM_STR);
        $statement->bindValue(":peptide_modified_sequence", $data["peptide_modified_sequence"], PDO::PARAM_STR);
        $statement->bindValue(":hydrophobicity", $data["hydrophobicity"], PDO::PARAM_STR);
        $statement->bindValue(":panorama_peptide_url", $data["panorama_peptide_url"], PDO::PARAM_STR);
        $statement->bindValue(":panorama_protein_url", $data["panorama_protein_url"], PDO::PARAM_STR);
        $statement->bindValue(":panorama_created_date", $data["panorama_created_date"], PDO::PARAM_STR);
        $statement->execute();


    }


    public function create_initial_records($data = false) {

        $analyte_peptide_db_array = array(
            'protein_id'
        , 'import_log_id'
        , 'peptide_molecular_weight'
        , 'peptide_start'
        , 'peptide_end'
        , 'modification_type'
        , 'site_of_modification_peptide'
        , 'peptide_sequence'
        , 'peptide_modified_sequence'
        , 'hydrophobicity'
        , 'panorama_peptide_url'
        , 'panorama_protein_url'
        , 'panorama_created_date'
        );

        $analyte_peptide_columns = implode(", ", $analyte_peptide_db_array);
        $analyte_peptide_placeholders = ":" . implode(", :", $analyte_peptide_db_array);
        foreach ($analyte_peptide_db_array as $single_column) {
            $analyte_peptide_update[] = $single_column . " = :" . $single_column;
        }

        // Remove the protein_id and import_log_id for updates.
        unset($analyte_peptide_update[0], $analyte_peptide_update[1]);

        if ($data) {

            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $statement = $this->db->prepare("
        SELECT protein.protein_id, protein.gene_symbol, analyte_peptide.peptide_sequence
        FROM protein
        LEFT JOIN analyte_peptide ON analyte_peptide.protein_id = protein.protein_id
        WHERE protein.uniprot_accession_id = :uniprot_accession_id
        AND analyte_peptide.peptide_sequence = :peptide_sequence
        AND protein.import_log_id = :import_log_id
        ");
            $statement->bindValue(":uniprot_accession_id", $data["uniprot_lookup_id"], PDO::PARAM_STR);
            $statement->bindValue(":peptide_sequence", $data["peptide_sequence"], PDO::PARAM_STR);
            $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
            $statement->execute();

            $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

            // If no record is found, insert a new record.
            // Otherwise, update the existing record.
            if (!$existing_data) {

                // Insert into the protein table.
                $statement = $this->db->prepare("
          INSERT INTO protein
          ( gene_symbol, import_log_id, uniprot_accession_id, guidance_document_version, created_date )
          VALUES ( :gene_symbol, :import_log_id, :uniprot_accession_id, :guidance_document_version, NOW() )");
                $statement->bindValue(":gene_symbol", $data["gene_symbol"], PDO::PARAM_STR);
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":uniprot_accession_id", $data["uniprot_lookup_id"], PDO::PARAM_STR);
                $statement->bindValue(":guidance_document_version", "1.0", PDO::PARAM_STR);
                $statement->execute();

                $last_inserted_protein_id = $this->db->lastInsertId();

                // Update, adding the cptac_id, using the last inserted id as the numeric base.
                $statement = $this->db->prepare("
          UPDATE protein
          SET cptac_id = :cptac_id
          WHERE protein_id = :protein_id");
                $statement->bindValue(":cptac_id", "CPTAC-" . $last_inserted_protein_id, PDO::PARAM_STR);
                $statement->bindValue(":protein_id", $last_inserted_protein_id, PDO::PARAM_INT);
                $statement->execute();

                // Insert into the analyte_peptide table.
                $statement = $this->db->prepare("
          INSERT INTO analyte_peptide
          (" . $analyte_peptide_columns . ")
          VALUES (" . $analyte_peptide_placeholders . ")
        ");

                $statement->bindValue(":protein_id", $last_inserted_protein_id, PDO::PARAM_INT);
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":peptide_molecular_weight", $data["peptide_molecular_weight"], PDO::PARAM_STR);
                $statement->bindValue(":peptide_start", $data["peptide_start"], PDO::PARAM_INT);
                $statement->bindValue(":peptide_end", $data["peptide_end"], PDO::PARAM_INT);
                $statement->bindValue(":modification_type", $data["modification_type"], PDO::PARAM_STR);
                $statement->bindValue(":site_of_modification_peptide", $data["site_of_modification_peptide"], PDO::PARAM_STR);
                $statement->bindValue(":peptide_sequence", $data["peptide_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":peptide_modified_sequence", $data["peptide_modified_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":hydrophobicity", $data["hydrophobicity"], PDO::PARAM_STR);
                $statement->bindValue(":panorama_peptide_url", $data["panorama_peptide_url"], PDO::PARAM_STR);
                $statement->bindValue(":panorama_protein_url", $data["panorama_protein_url"], PDO::PARAM_STR);
                $statement->bindValue(":panorama_created_date", $data["panorama_created_date"], PDO::PARAM_STR);
                $statement->execute();

            } else {
                // Update the protein table.
                $statement = $this->db->prepare("
          UPDATE protein
          SET uniprot_accession_id = :uniprot_accession_id
          WHERE protein.protein_id = " . $existing_data["protein_id"] . "
        ");
                $statement->bindValue(":uniprot_accession_id", $data["uniprot_lookup_id"], PDO::PARAM_STR);
                $statement->execute();

                $statement = $this->db->prepare("
          UPDATE analyte_peptide
          SET " . implode(", ", $analyte_peptide_update) . "
          WHERE analyte_peptide.protein_id = " . $existing_data["protein_id"] . "
        ");


                $data["modification_type"] = isset($data["modification_type"]) ? $data["modification_type"] : false;


                $statement->bindValue(":peptide_molecular_weight", $data["peptide_molecular_weight"], PDO::PARAM_STR);
                $statement->bindValue(":peptide_start", $data["peptide_start"], PDO::PARAM_INT);
                $statement->bindValue(":peptide_end", $data["peptide_end"], PDO::PARAM_INT);
                $statement->bindValue(":modification_type", $data["modification_type"], PDO::PARAM_STR);
                $statement->bindValue(":site_of_modification_peptide", $data["site_of_modification_peptide"], PDO::PARAM_STR);
                $statement->bindValue(":peptide_sequence", $data["peptide_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":peptide_modified_sequence", $data["peptide_modified_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":hydrophobicity", $data["hydrophobicity"], PDO::PARAM_STR);
                $statement->bindValue(":panorama_peptide_url", $data["panorama_peptide_url"], PDO::PARAM_STR);
                $statement->bindValue(":panorama_protein_url", $data["panorama_protein_url"], PDO::PARAM_STR);
                $statement->bindValue(":panorama_created_date", $data["panorama_created_date"], PDO::PARAM_STR);
                $statement->execute();


            }
        }

    }

    public function import_chromatogram_images($data = false) {

        if ($data["file_name"] && $data["sequence"]) {

            // Hack. Uniform naming, fail.
            $data["peptide_sequence"] = $data["sequence"];

            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $existing_data = $this->get_single_image_data(
                'panorama_chromatogram_images'
                , $data
            );

            if (!$existing_data) {
                // Insert into the database
                $statement = $this->db->prepare("
          INSERT INTO panorama_chromatogram_images
          ( import_log_id, analyte_peptide_id, laboratory_id, sequence, file_name, created_date )
          VALUES ( :import_log_id, :analyte_peptide_id, :laboratory_id, :sequence, :file_name, NOW() )
        ");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
                $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
                $statement->bindValue(":sequence", $data["sequence"], PDO::PARAM_STR);
                $statement->bindValue(":file_name", $data["file_name"], PDO::PARAM_STR);
                $statement->execute();
            } else {
                // Update the table.
                $statement = $this->db->prepare("
          UPDATE panorama_chromatogram_images
          SET file_name = :file_name
          WHERE panorama_chromatogram_images_id = :panorama_chromatogram_images_id
        ");
                $statement->bindValue(":panorama_chromatogram_images_id", $existing_data["panorama_chromatogram_images_id"], PDO::PARAM_STR);
                $statement->bindValue(":file_name", $data["file_name"], PDO::PARAM_STR);
                $statement->execute();
            }

        }

    }

    public function import_response_curve_images($data = false) {

        if ($data["response_curve_image_linear"]) {
            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $existing_data = $this->get_single_image_data(
                'panorama_response_curve_images'
                , $data
            );

            if (!$existing_data) {
                // Insert linear plot into the database
                $statement = $this->db->prepare("
            INSERT INTO panorama_response_curve_images
            ( import_log_id, analyte_peptide_id, laboratory_id, sequence, file_name, created_date )
            VALUES ( :import_log_id, :analyte_peptide_id, :laboratory_id, :sequence, :file_name, NOW() )
          ");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
                $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
                $statement->bindValue(":sequence", $data["peptide_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":file_name", $data["response_curve_image_linear"], PDO::PARAM_STR);
                $statement->execute();
            } else {
                // Update the table.
                $statement = $this->db->prepare("
            UPDATE panorama_response_curve_images
            SET file_name = :file_name
            WHERE response_curve_images_id = :response_curve_images_id
          ");
                $statement->bindValue(":file_name", $data["response_curve_image_linear"], PDO::PARAM_STR);
                $statement->bindValue(":response_curve_images_id", $existing_data["response_curve_images_id"], PDO::PARAM_STR);
                $statement->execute();
            }
        }

        if ($data["response_curve_image_log"]) {
            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $existing_data = $this->get_single_image_data(
                'panorama_response_curve_images'
                , $data
            );

            if (!$existing_data) {
                // Insert log plot into the database
                $statement = $this->db->prepare("
            INSERT INTO panorama_response_curve_images
            ( import_log_id, analyte_peptide_id, laboratory_id, sequence, file_name, created_date )
            VALUES ( :import_log_id, :analyte_peptide_id, :laboratory_id, :sequence, :file_name, NOW() )
          ");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
                $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
                $statement->bindValue(":sequence", $data["peptide_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":file_name", $data["response_curve_image_log"], PDO::PARAM_STR);
                $statement->execute();
            } else {
                // Update the table.
                $statement = $this->db->prepare("
            UPDATE panorama_response_curve_images
            SET file_name = :file_name
            WHERE response_curve_images_id = :response_curve_images_id
          ");
                $statement->bindValue(":file_name", $data["response_curve_image_log"], PDO::PARAM_STR);
                $statement->bindValue(":response_curve_images_id", $existing_data["response_curve_images_id"], PDO::PARAM_STR);
                $statement->execute();
            }
        }

        if ($data["response_curve_image_residual"]) {
            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $existing_data = $this->get_single_image_data(
                'panorama_response_curve_images'
                , $data
            );

            if (!$existing_data) {
                // Insert residual plot into the database
                $statement = $this->db->prepare("
            INSERT INTO panorama_response_curve_images
            ( import_log_id, analyte_peptide_id, laboratory_id, sequence, file_name, created_date )
            VALUES ( :import_log_id, :analyte_peptide_id, :laboratory_id, :sequence, :file_name, NOW() )");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
                $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
                $statement->bindValue(":sequence", $data["peptide_sequence"], PDO::PARAM_STR);
                $statement->bindValue(":file_name", $data["response_curve_image_residual"], PDO::PARAM_STR);
                $statement->execute();
            } else {
                // Update the table.
                $statement = $this->db->prepare("
            UPDATE panorama_response_curve_images
            SET file_name = :file_name
            WHERE response_curve_images_id = :response_curve_images_id
          ");
                $statement->bindValue(":file_name", $data["response_curve_image_residual"], PDO::PARAM_STR);
                $statement->bindValue(":response_curve_images_id", $existing_data["response_curve_images_id"], PDO::PARAM_STR);
                $statement->execute();
            }
        }

    }

    public function import_validation_sample_images($data = false) {

        if ($data["analyte_peptide_id"] && $data["laboratory_id"] && $data["file_name"] && $data["sequence"]) {

            // Hack. Uniform naming, fail.
            $data["peptide_sequence"] = $data["sequence"];

            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $existing_data = $this->get_single_image_data(
                'panorama_validation_sample_images'
                , $data
            );

            if (!$existing_data) {
                // Insert validation sample image data into the database
                $statement = $this->db->prepare("
          INSERT INTO panorama_validation_sample_images
          ( import_log_id, analyte_peptide_id, laboratory_id, sequence, file_name, created_date )
          VALUES ( :import_log_id, :analyte_peptide_id, :laboratory_id, :sequence, :file_name, NOW() )");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
                $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
                $statement->bindValue(":sequence", $data["sequence"], PDO::PARAM_STR);
                $statement->bindValue(":file_name", $data["file_name"], PDO::PARAM_STR);
                $statement->execute();
            } else {
                // Update the table.
                $statement = $this->db->prepare("
          UPDATE panorama_validation_sample_images
          SET file_name = :file_name
          WHERE validation_sample_images_id = :validation_sample_images_id
        ");
                $statement->bindValue(":file_name", $data["file_name"], PDO::PARAM_STR);
                $statement->bindValue(":validation_sample_images_id", $existing_data["validation_sample_images_id"], PDO::PARAM_STR);
                $statement->execute();
            }
        }
    }

    public function slice_image($panorama_images_storage_path, $name, $imageFileName, $crop_width, $crop_height) {

        $fileName = $panorama_images_storage_path . $imageFileName;

        $img = new Imagick($fileName);
        $imgHeight = $img->getImageHeight();
        $imgWidth = $img->getImageWidth();

        $crop_width_num_times = ceil($imgWidth / $crop_width);
        $crop_height_num_times = ceil($imgHeight / $crop_height);
        for ($i = 0; $i < $crop_width_num_times; $i++) {
            for ($j = 0; $j < $crop_height_num_times; $j++) {
                $img = new Imagick($fileName);
                $x = ($i * $crop_width);
                $y = ($j * $crop_height);
                $img->cropImage($crop_width, $crop_height, $x, $y);
                $data = $img->getImageBlob();

                $newFileName = $panorama_images_storage_path . $name . "_" . $x . "_" . $y . ".jpg";
                $result = file_put_contents($newFileName, $data);
            }
        }
    }

    public function import_validation_sample_images_data($data = false) {

        if ($data) {

            foreach ($data as $value) {

                // Massage the horkd data
                $insert = implode("','", $value);
                $insert = str_replace('"', '', $insert);
                $insert = "'" . $insert . "'";

                // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
                $statement = $this->db->prepare("
          SELECT panorama_validation_sample_data_id, sequence
          FROM panorama_validation_sample_data
          WHERE fragment_ion = '" . str_replace('"', '', $value[0]) . "'
          AND sequence = '" . str_replace('"', '', $value[13]) . "'
          AND analyte_peptide_id = '" . str_replace('"', '', $value[14]) . "'
          AND laboratory_id = '" . str_replace('"', '', $value[15]) . "'
        ");
                $statement->execute();
                $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

                if ($existing_data) {
                    // Update the table.
                    $statement = $this->db->prepare("
            UPDATE panorama_validation_sample_data
            SET
                fragment_ion = '" . str_replace('"', '', $value[0]) . "'
              , low_intra_CV = '" . str_replace('"', '', $value[1]) . "'
              , med_intra_CV = '" . str_replace('"', '', $value[2]) . "'
              , high_intra_CV = '" . str_replace('"', '', $value[3]) . "'
              , low_inter_CV = '" . str_replace('"', '', $value[4]) . "'
              , med_inter_CV = '" . str_replace('"', '', $value[5]) . "'
              , high_inter_CV = '" . str_replace('"', '', $value[6]) . "'
              , low_total_CV = '" . str_replace('"', '', $value[7]) . "'
              , med_total_CV = '" . str_replace('"', '', $value[8]) . "'
              , high_total_CV = '" . str_replace('"', '', $value[9]) . "'
              , low_count = '" . str_replace('"', '', $value[10]) . "'
              , med_count = '" . str_replace('"', '', $value[11]) . "'
              , high_count = '" . str_replace('"', '', $value[12]) . "'
            WHERE panorama_validation_sample_data_id = " . $existing_data["panorama_validation_sample_data_id"]);
                    $statement->execute();
                } else {
                    // Insert validation sample image data into the database
                    $statement = $this->db->prepare("INSERT INTO
            panorama_validation_sample_data
              (fragment_ion
              ,low_intra_CV
              ,med_intra_CV
              ,high_intra_CV
              ,low_inter_CV
              ,med_inter_CV
              ,high_inter_CV
              ,low_total_CV
              ,med_total_CV
              ,high_total_CV
              ,low_count
              ,med_count
              ,high_count
              ,sequence
              ,analyte_peptide_id
              ,laboratory_id
              ,import_log_id
              ,created_date)
            VALUES (" . $insert . ", NOW() )");
                    $statement->execute();
                }

            }

        }

    }

    public function import_endogenous_data($data = false) {

        if ($data) {

            foreach ($data as $value) {

                // Massage the horkd data
                $insert = implode("','", $value);
                $insert = str_replace('"', '', $insert);
                $insert = "'" . $insert . "'";

                // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
                $statement = $this->db->prepare("
          SELECT panorama_endogenous_data_id, sequence
          FROM panorama_endogenous_data
          WHERE fragment_ion = '" . str_replace('"', '', $value[0]) . "'
          AND sequence = '" . str_replace('"', '', $value[13]) . "'
          AND analyte_peptide_id = '" . str_replace('"', '', $value[14]) . "'
          AND laboratory_id = '" . str_replace('"', '', $value[15]) . "'
        ");
                $statement->execute();
                $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

                if ($existing_data) {
                    // Update the table.
                    $statement = $this->db->prepare("
            UPDATE panorama_validation_sample_data
            SET
                fragment_ion = '" . str_replace('"', '', $value[0]) . "'
              , low_intra_CV = '" . str_replace('"', '', $value[1]) . "'
              , med_intra_CV = '" . str_replace('"', '', $value[2]) . "'
              , high_intra_CV = '" . str_replace('"', '', $value[3]) . "'
              , low_inter_CV = '" . str_replace('"', '', $value[4]) . "'
              , med_inter_CV = '" . str_replace('"', '', $value[5]) . "'
              , high_inter_CV = '" . str_replace('"', '', $value[6]) . "'
              , low_total_CV = '" . str_replace('"', '', $value[7]) . "'
              , med_total_CV = '" . str_replace('"', '', $value[8]) . "'
              , high_total_CV = '" . str_replace('"', '', $value[9]) . "'
              , low_count = '" . str_replace('"', '', $value[10]) . "'
              , med_count = '" . str_replace('"', '', $value[11]) . "'
              , high_count = '" . str_replace('"', '', $value[12]) . "'
            WHERE panorama_validation_sample_data_id = " . $existing_data["panorama_validation_sample_data_id"]);
                    $statement->execute();
                } else {
                    // Insert validation sample image data into the database
                    $statement = $this->db->prepare("INSERT INTO
            panorama_validation_sample_data
              (fragment_ion
              ,low_intra_CV
              ,med_intra_CV
              ,high_intra_CV
              ,low_inter_CV
              ,med_inter_CV
              ,high_inter_CV
              ,low_total_CV
              ,med_total_CV
              ,high_total_CV
              ,low_count
              ,med_count
              ,high_count
              ,sequence
              ,analyte_peptide_id
              ,laboratory_id
              ,import_log_id
              ,created_date)
            VALUES (" . $insert . ", NOW() )");
                    $statement->execute();
                }

            }

        }

    }

    public function panorama_chromatogram_images_failed(
        $import_log_id = false
        , $type = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_chromatogram_images_failed
      ( import_log_id, type, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :type, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":type", $type, PDO::PARAM_STR);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_response_curve_images_failed(
        $import_log_id = false
        , $curve_type = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_response_curve_images_failed
      ( import_log_id, curve_type, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :curve_type, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":curve_type", $curve_type, PDO::PARAM_STR);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_validation_sample_images_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_validation_sample_images_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_validation_sample_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_validation_sample_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function lod_loq_comparison_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO lod_loq_comparison_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function curve_fit_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO response_curves_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_selectivity_images_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_selectivity_images_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_selectivity_summary_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_selectivity_summary_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_selectivity_spike_level_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_selectivity_spike_level_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_stability_images_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_stability_images_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_stability_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_stability_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_endogenous_images_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_endogenous_images_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function panorama_endogenous_data_failed(
        $import_log_id = false
        , $analyte_peptide_id = false
        , $peptide_sequence = false
        , $modified_peptide_sequence = false
        , $laboratory_name = false
        , $laboratory_abbreviation = false
        , $error_response = false
        , $panorama_url = false
    ) {

        $statement = $this->db->prepare("
      INSERT INTO panorama_endogenous_data_failed
      ( import_log_id, analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date )
      VALUES ( :import_log_id, :analyte_peptide_id, :peptide_sequence, :modified_peptide_sequence, :laboratory_name, :laboratory_abbreviation, :error_response, :panorama_url, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":modified_peptide_sequence", $modified_peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_name", $laboratory_name, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
        $statement->bindValue(":error_response", $error_response, PDO::PARAM_STR);
        $statement->bindValue(":panorama_url", $panorama_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function check_for_duplicate($table = false, $filename = false) {

        $data = false;

        if ($table && $filename) {
            $statement = $this->db->prepare("SELECT file_name FROM `" . $table . "` WHERE file_name = '" . $filename . "'");
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }

        return $data;

    }

    public function import_panorama_peptide_url($analyte_peptide_id = false, $panorama_peptide_url = false) {
        $statement = $this->db->prepare("
      UPDATE analyte_peptide
      SET panorama_peptide_url = :panorama_peptide_url
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":panorama_peptide_url", $panorama_peptide_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function import_panorama_protein_url($analyte_peptide_id = false, $panorama_protein_url = false) {
        $statement = $this->db->prepare("
      UPDATE analyte_peptide
      SET panorama_protein_url = :panorama_protein_url
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":panorama_protein_url", $panorama_protein_url, PDO::PARAM_STR);
        $statement->execute();
    }

    public function import_peptide_standard_label_type($peptide_sequence = false, $peptide_standard_label_type = false) {
        $statement = $this->db->prepare("
      UPDATE analyte_peptide
      SET peptide_standard_label_type = :peptide_standard_label_type
      WHERE peptide_sequence = :peptide_sequence");
        $statement->bindValue(":peptide_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->bindValue(":peptide_standard_label_type", $peptide_standard_label_type, PDO::PARAM_STR);
        $statement->execute();
    }

    public function check_for_missed_chromatograms($analyte_peptide_id = false, $laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT
       panorama_chromatogram_images_id
      ,analyte_peptide_id
      ,sequence as peptide_sequence
      ,laboratory_id
      FROM panorama_chromatogram_images
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function check_for_missed_response_curves($analyte_peptide_id = false, $laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT
         response_curve_images_id
        ,analyte_peptide_id
        ,sequence as peptide_sequence
        ,laboratory_id
      FROM panorama_response_curve_images
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function check_for_missed_validation_samples($analyte_peptide_id = false, $laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT
         validation_sample_images_id
        ,analyte_peptide_id
        ,sequence as peptide_sequence
        ,laboratory_id
      FROM panorama_validation_sample_images
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    // public function check_for_missed_validation_sample_tabular_data( $analyte_peptide_id = false, $laboratory_id = false ) {
    //   $statement = $this->db->prepare("
    //     SELECT
    //        panorama_validation_sample_data_id
    //       ,analyte_peptide_id
    //       ,sequence as peptide_sequence
    //       ,laboratory_id
    //     FROM panorama_validation_sample_data
    //     WHERE analyte_peptide_id = :analyte_peptide_id
    //     AND laboratory_id = :laboratory_id");
    //   $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
    //   $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
    //   $statement->execute();
    //   $data = $statement->fetchAll(PDO::FETCH_ASSOC);
    //   return $data;
    // }

    public function get_all_chromatogram_image_file_names($laboratory_id = false, $import_log_id = false) {
        $statement = $this->db->prepare("
      SELECT 
          panorama_chromatogram_images.file_name
        , analyte_peptide.analyte_peptide_id
        , CONCAT('CPTAC-',analyte_peptide.analyte_peptide_id) as cptac_id
        , analyte_peptide.peptide_sequence
        , analyte_peptide.peptide_modified_sequence
        , group.group_id as laboratories_id
        , group.name as laboratory_name
        , group.abbreviation as laboratory_abbreviation
        , assay_parameters_new.celllysate_path
        , protein.import_log_id
      FROM panorama_chromatogram_images
      LEFT JOIN analyte_peptide ON analyte_peptide.analyte_peptide_id = panorama_chromatogram_images.analyte_peptide_id
      
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      
      WHERE panorama_chromatogram_images.laboratory_id = :laboratory_id
      AND panorama_chromatogram_images.import_log_id = :import_log_id
      ORDER BY import_log_id, sequence ASC");
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_all_response_curve_image_file_names($laboratory_id = false, $import_log_id = false) {
        $statement = $this->db->prepare("
      SELECT 
          panorama_response_curve_images.file_name
        , analyte_peptide.analyte_peptide_id
        , CONCAT('CPTAC-',analyte_peptide.analyte_peptide_id) as cptac_id
        , analyte_peptide.peptide_sequence
        , analyte_peptide.peptide_modified_sequence
        , group.group_id as laboratories_id
        , group.name as laboratory_name
        , group.abbreviation as laboratory_abbreviation
        , assay_parameters_new.celllysate_path
        , protein.import_log_id
      FROM panorama_response_curve_images
      LEFT JOIN analyte_peptide ON analyte_peptide.analyte_peptide_id = panorama_response_curve_images.analyte_peptide_id
      
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id

      WHERE panorama_response_curve_images.laboratory_id = :laboratory_id
      AND panorama_response_curve_images.import_log_id = :import_log_id
      ORDER BY import_log_id, sequence ASC");
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_all_repeatability_image_file_names($laboratory_id = false, $import_log_id = false) {
        $statement = $this->db->prepare("
      SELECT 
          panorama_validation_sample_images.file_name
        , analyte_peptide.analyte_peptide_id
        , CONCAT('CPTAC-',analyte_peptide.analyte_peptide_id) as cptac_id
        , analyte_peptide.peptide_sequence
        , analyte_peptide.peptide_modified_sequence
        , group.group_id as laboratories_id
        , group.name as laboratory_name
        , group.abbreviation as laboratory_abbreviation
        , assay_parameters_new.celllysate_path
        , protein.import_log_id
      FROM panorama_validation_sample_images
      LEFT JOIN analyte_peptide ON analyte_peptide.analyte_peptide_id = panorama_validation_sample_images.analyte_peptide_id

      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id

      WHERE panorama_validation_sample_images.laboratory_id = :laboratory_id
      AND panorama_validation_sample_images.import_log_id = :import_log_id
      ORDER BY import_log_id, sequence ASC");
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_images_data($table = false, $analyte_peptide_id = false, $laboratory_id = false) {

        // Hack... because I failed in naming the column names correctly.
        $id_column_name = ($table == "panorama_chromatogram_images") ? "panorama_chromatogram_images_id" : str_replace("panorama_", "", $table) . "_id";

        $statement = $this->db->prepare("
      SELECT " . $id_column_name . ", file_name
      FROM `" . $table . "`
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function  get_single_image_data($table = false, $data = false) {

        // Hack... because I failed in naming the column names correctly.
        $id_column_name = ($table == "panorama_chromatogram_images") ? "panorama_chromatogram_images_id" : str_replace("panorama_", "", $table) . "_id";

        if (isset($data["file_name"])) {
            $file_name = $data["file_name"];
        }
        if (isset($data["response_curve_image_linear"])) {
            $file_name = $data["response_curve_image_linear"];
        }
        if (isset($data["response_curve_image_log"])) {
            $file_name = $data["response_curve_image_log"];
        }
        if (isset($data["response_curve_image_residual"])) {
            $file_name = $data["response_curve_image_residual"];
        }

        $statement = $this->db->prepare("
      SELECT
          " . $id_column_name . "
        , analyte_peptide_id
        , laboratory_id
        , sequence
        , file_name
      FROM `" . $table . "`
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id
      AND sequence = :sequence
      AND file_name = :file_name");
        $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
        $statement->bindValue(":sequence", $data["peptide_sequence"], PDO::PARAM_STR);
        $statement->bindValue(":file_name", $file_name, PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_single_image_data_by_image_id($table = false, $id_column_name = false, $image_id = false) {

        $sql = "SELECT
                  {$id_column_name}
                , analyte_peptide_id
                , laboratory_id
                , sequence
                , file_name
              FROM `{$table}`
              WHERE {$id_column_name} = :$image_id";

        echo $sql;

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":$image_id", $image_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        return $data;
    }


    public function check_for_missed_images($laboratory_id = false, $import_log_id = false, $all_sequences = false) {

        $sequences = false;
        $sequences["chromatograms"] = array();
        $sequences["response_curves"] = array();
        $sequences["validation_samples"] = array();

        $panorama_images_storage_path = $this->final_global_template_vars["panorama_images_storage_path"];
        $panorama_image_path = $this->final_global_template_vars['panorama_images_path'];


        /*
         * Chromatogram image checks
         */

        $missed_sequences = array();

        // Check for missing Chromatogram images in the database, but not in the filesystem.
        $chromatogram_images_data = $this->get_all_chromatogram_image_file_names($laboratory_id, $import_log_id);
        $i = 1;
        foreach ($chromatogram_images_data as $file_data) {
            $file_path = $panorama_images_storage_path .
                "/" . $import_log_id .
                "/" . "chromatogram_images" .
                "/" . $file_data['file_name'];

            if (!file_exists($file_path) || (filesize($file_path) < '5808')) {
                $file_data['file_name'] = $panorama_image_path .
                    "/" . $import_log_id .
                    "/" . "chromatogram_images" .
                    "/" . $file_data['file_name'];
                $sequences["chromatograms"][] = $file_data;
                $i++;
            }
        }

        // Get all of the missed chromatogram images in the database.
        foreach ($all_sequences as $sequence) {
            $missed["chromatograms"] = $this->check_for_missed_chromatograms($sequence["analyte_peptide_id"], $sequence["laboratories_id"]);
            if (count($missed["chromatograms"]) < 3) {
                $missed_sequences[] = $sequence;
            }
        }

        $sequences["chromatograms"] = array_merge($missed_sequences, $sequences["chromatograms"]);

        // Add chromatogram image errors from Panorama to the array.
        $i = 0;
        foreach ($sequences["chromatograms"] as $single) {
            $sequences["chromatograms"][$i]["error"] = $this->get_panorama_chromatogram_images_failed($single["analyte_peptide_id"]);
            $i++;
        }
        $i = 0;
        foreach ($sequences["chromatograms"] as $sequence) {
            if (isset($sequence["error"]) && !empty($sequence["error"])) {
                foreach ($sequence["error"] as $error) {
                    if (isset($error["error_response"]) && !empty($error["error_response"])) {
                        $stripped = strip_tags(json_encode($error["error_response"]));
                        $sequences["chromatograms"][$i]["error_response"] = trim(json_decode($stripped));
                        $sequences["chromatograms"][$i]["cptac_id"] = "CPTAC-" . $sequence["analyte_peptide_id"];
                    }
                }
            }
            $i++;
        }

        /*
         * Response Curve image checks
         */

        $missed_sequences = array();

        // Check for missing Response Curve images in the database, but not in the filesystem.
        $response_curve_images_data = $this->get_all_response_curve_image_file_names($laboratory_id, $import_log_id);
        $i = 1;
        foreach ($response_curve_images_data as $file_data) {
            $file_path = $panorama_images_storage_path .
                "/" . $import_log_id .
                "/" . "response_curve_images" .
                "/" . $file_data['file_name'];

            if (!file_exists($file_path) || (filesize($file_path) < '5808')) {
                $file_data['file_name'] = $panorama_image_path .
                    "/" . $import_log_id .
                    "/" . "response_curve_images" .
                    "/" . $file_data['file_name'];

                $sequences["response_curves"][] = $file_data;
                $i++;
            }
        }

        // Get all of the missed response curve images in the database.
        foreach ($all_sequences as $sequence) {
            $missed["response_curves"] = $this->check_for_missed_response_curves($sequence["analyte_peptide_id"], $sequence["laboratories_id"]);
            if (count($missed["response_curves"]) < 3) {
                $missed_sequences[] = $sequence;
            }
        }

        $sequences["response_curves"] = array_merge($missed_sequences, $sequences["response_curves"]);

        // Add response curve errors from Panorama to the array.
        $i = 0;
        foreach ($sequences["response_curves"] as $single) {
            $sequences["response_curves"][$i]["error"] = $this->get_panorama_response_curve_images_failed($single["analyte_peptide_id"]);
            $i++;
        }

        $i = 0;
        foreach ($sequences["response_curves"] as $sequence) {
            if (isset($sequence["error"]) && !empty($sequence["error"])) {
                foreach ($sequence["error"] as $error) {
                    if (isset($error["error_response"]) && !empty($error["error_response"])) {
                        $stripped = strip_tags(json_encode($error["error_response"]));
                        $sequences["response_curves"][$i]["error_response"] = trim(json_decode($stripped));
                        $sequences["response_curves"][$i]["cptac_id"] = "CPTAC-" . $sequence["analyte_peptide_id"];
                    }
                }
            }
            $i++;
        }

        /*
         * Validation Sample image checks
         */

        $missed_sequences = array();

        // Check for missing Validation Sample (Repeatability) images in the database, but not in the filesystem.
        $repeatability_images_data = $this->get_all_repeatability_image_file_names($laboratory_id, $import_log_id);

        $i = 1;
        foreach ($repeatability_images_data as $file_data) {
            $file_path = $panorama_images_storage_path .
                "/" . $import_log_id .
                "/" . "validation_sample_images" .
                "/" . $file_data['file_name'];

            if (!file_exists($file_path) || (filesize($file_path) < '5808')) {
                $file_data['file_name'] = $panorama_image_path .
                    "/" . $import_log_id .
                    "/" . "validation_sample_images" .
                    "/" . $file_data['file_name'];

                $sequences["validation_samples"][] = $file_data;
                $i++;
            }
        }

        // Get all of the missed validation sample images in the database.
        foreach ($all_sequences as $sequence) {
            $missed["validation_samples"] = $this->check_for_missed_validation_samples($sequence["analyte_peptide_id"], $sequence["laboratories_id"]);
            if (count($missed["validation_samples"]) < 1) {
                $missed_sequences[] = $sequence;
            }
        }

        $sequences["validation_samples"] = array_merge($missed_sequences, $sequences["validation_samples"]);

        // Add validation sample errors from Panorama to the array.
        $i = 0;
        foreach ($sequences["validation_samples"] as $single) {
            $sequences["validation_samples"][$i]["error"] = $this->get_validation_sample_images_failed($single["analyte_peptide_id"], $sequence["cptac_id"]);
            $i++;
        }

        $missed_sequences = array();

        return $sequences;

    }

    public function check_for_missed_lod_loq_data($analyte_peptide_id = false, $laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT 
         lod_loq_comparison_id
        ,analyte_peptide_id
        ,peptide as peptide_sequence
        ,laboratory_id
      FROM lod_loq_comparison
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function check_for_missed_response_curves_data($analyte_peptide_id = false, $laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT
         response_curves_data_id
        ,analyte_peptide_id
        ,peptide as peptide_sequence
        ,laboratory_id
      FROM response_curves_data
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function check_for_missed_validation_samples_data($analyte_peptide_id = false, $laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT 
         panorama_validation_sample_data_id
        ,analyte_peptide_id
        ,sequence as peptide_sequence
        ,laboratory_id
      FROM panorama_validation_sample_data
      WHERE analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function check_for_missed_images_data($laboratory_id = false, $import_log_id = false, $all_sequences = false) {

        $sequences = false;
        $sequences["lod_loq_data"] = array();
        $sequences["response_curves_data"] = array();
        $sequences["validation_samples_data"] = array();

        if ($laboratory_id && $import_log_id) {

            /*
             * LOD/LOQ data checks
             */

            $missed_sequences = array();

            // Get all of the failed LOD/LOQ data images data.
            $i = 0;
            foreach ($all_sequences as $sequence) {
                $missed["lod_loq_data"] = $this->check_for_missed_lod_loq_data($sequence["analyte_peptide_id"], $sequence["laboratories_id"]);
                if (count($missed["lod_loq_data"]) < 1) {
                    $missed_sequences[$i] = $sequence;
                }
                $i++;
            }

            // Add all of the failed LOD/LOQ errors from Panorama to the array.
            $i = 0;
            foreach ($all_sequences as $sequence) {
                $data = $this->get_lod_loq_comparison_data_failed($sequence["analyte_peptide_id"], $sequence["cptac_id"]);
                if ($data) {
                    unset($missed_sequences[$i]);
                    $sequences["lod_loq_data"][$i] = $data;
                }
                $i++;
            }

            $sequences["lod_loq_data"] = array_merge($missed_sequences, $sequences["lod_loq_data"]);

            // Massage the data a bit.
            $i = 0;
            foreach ($sequences["lod_loq_data"] as $sequence) {
                if (isset($sequence["error_response"]) && !empty($sequence["error_response"])) {
                    $stripped = strip_tags(json_encode($sequence["error_response"]));
                    $sequences["lod_loq_data"][$i]["error_response"] = trim(json_decode($stripped));
                    $sequences["lod_loq_data"][$i]["laboratories_id"] = $laboratory_id;
                    $sequences["lod_loq_data"][$i]["import_log_id"] = $import_log_id;
                    $sequences["lod_loq_data"][$i]["celllysate_path"] = $all_sequences[0]["celllysate_path"];
                } else {
                    $sequences["lod_loq_data"][$i]["error_response"] = false;
                    $sequences["lod_loq_data"][$i]["panorama_url"] = false;
                }
                $i++;
            }

            /*
             * Response Curves (Curve Fit) data checks
             */

            $missed_sequences = array();

            // Get all of the failed Response Curves (Curve Fit) data images data.
            $i = 0;
            foreach ($all_sequences as $sequence) {
                $missed["response_curves_data"] = $this->check_for_missed_response_curves_data($sequence["analyte_peptide_id"], $sequence["laboratories_id"]);
                if (count($missed["response_curves_data"]) < 1) {
                    $missed_sequences[$i] = $sequence;
                }
                $i++;
            }

            // Add all of the failed Response Curves (Curve Fit) errors from Panorama to the array.
            $i = 0;
            foreach ($all_sequences as $sequence) {
                $data = $this->get_response_curves_data_failed($sequence["analyte_peptide_id"], $sequence["cptac_id"]);
                if ($data) {
                    unset($missed_sequences[$i]);
                    $sequences["response_curves_data"][$i] = $data;
                }
                $i++;
            }

            $sequences["response_curves_data"] = array_merge($missed_sequences, $sequences["response_curves_data"]);

            // Massage the data a bit.
            $i = 0;
            foreach ($sequences["response_curves_data"] as $sequence) {
                if (isset($sequence["error_response"]) && !empty($sequence["error_response"])) {
                    $stripped = strip_tags(json_encode($sequence["error_response"]));
                    $sequences["response_curves_data"][$i]["error_response"] = trim(json_decode($stripped));
                    $sequences["response_curves_data"][$i]["laboratories_id"] = $laboratory_id;
                    $sequences["response_curves_data"][$i]["import_log_id"] = $import_log_id;
                    $sequences["response_curves_data"][$i]["celllysate_path"] = $all_sequences[0]["celllysate_path"];
                } else {
                    $sequences["response_curves_data"][$i]["error_response"] = false;
                    $sequences["response_curves_data"][$i]["panorama_url"] = false;
                }
                $i++;
            }


            /*
             * Repeatability (Validation Sample) data checks
             */

            $missed_sequences = array();

            // Get all of the failed Repeatability (Validation Sample) data.
            $i = 0;
            foreach ($all_sequences as $sequence) {
                $missed["validation_samples_data"] = $this->check_for_missed_validation_samples_data($sequence["analyte_peptide_id"], $sequence["laboratories_id"]);
                if (count($missed["validation_samples_data"]) < 1) {
                    $missed_sequences[$i] = $sequence;
                }
                $i++;
            }

            // Add all of the failed Repeatability (Validation Sample) errors from Panorama to the array.
            $i = 0;
            foreach ($all_sequences as $sequence) {
                $data = $this->get_validation_sample_data_failed($sequence["analyte_peptide_id"], $sequence["cptac_id"]);
                if ($data) {
                    unset($missed_sequences[$i]);
                    $sequences["validation_samples_data"][$i] = $data;
                }
                $i++;
            }

            $sequences["validation_samples_data"] = array_merge($missed_sequences, $sequences["validation_samples_data"]);

            // Massage the data a bit.
            $i = 0;
            foreach ($sequences["validation_samples_data"] as $sequence) {
                if (isset($sequence["error_response"]) && !empty($sequence["error_response"])) {
                    $stripped = strip_tags(json_encode($sequence["error_response"]));
                    $sequences["validation_samples_data"][$i]["error_response"] = trim(json_decode($stripped));
                    $sequences["validation_samples_data"][$i]["laboratories_id"] = $laboratory_id;
                    $sequences["validation_samples_data"][$i]["import_log_id"] = $import_log_id;
                    $sequences["validation_samples_data"][$i]["celllysate_path"] = $all_sequences[0]["celllysate_path"];
                } else {
                    $sequences["validation_samples_data"][$i]["error_response"] = false;
                    $sequences["validation_samples_data"][$i]["panorama_url"] = false;
                }
                $i++;
            }

        }

        return $sequences;

    }

    public function get_panorama_chromatogram_images_failed($analyte_peptide_id = false) {
        $statement = $this->db->prepare("
      SELECT type, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date
      FROM panorama_chromatogram_images_failed
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_panorama_response_curve_images_failed($analyte_peptide_id = false) {
        $statement = $this->db->prepare("
      SELECT curve_type, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date
      FROM panorama_response_curve_images_failed
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_lod_loq_comparison_data_failed($analyte_peptide_id = false, $cptac_id = false) {
        $statement = $this->db->prepare("
      SELECT analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date
      FROM lod_loq_comparison_data_failed
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data["cptac_id"] = $cptac_id;
            return $data;
        }
    }

    public function get_response_curves_data_failed($analyte_peptide_id = false, $cptac_id = false) {
        $statement = $this->db->prepare("
      SELECT analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date
      FROM response_curves_data_failed
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data["cptac_id"] = $cptac_id;
            return $data;
        }
    }

    public function get_validation_sample_images_failed($analyte_peptide_id = false, $cptac_id = false) {
        $data = false;
        $statement = $this->db->prepare("
      SELECT analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date
      FROM panorama_validation_sample_images_failed
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data["cptac_id"] = $cptac_id;
            return $data;
        }
    }

    public function get_validation_sample_data_failed($analyte_peptide_id = false, $cptac_id = false) {
        $statement = $this->db->prepare("
      SELECT analyte_peptide_id, peptide_sequence, modified_peptide_sequence, laboratory_name, laboratory_abbreviation, error_response, panorama_url, created_date
      FROM panorama_validation_sample_data_failed
      WHERE analyte_peptide_id = :analyte_peptide_id");
        $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data["cptac_id"] = $cptac_id;
        }
        return $data;
    }

    public function purge_error_logs($table = false, $import_log_id = false) {
        if ($table && $import_log_id) {
            $statement = $this->db->prepare("
        DELETE
        FROM `" . $table . "`
        WHERE `import_log_id` = " . (int)$import_log_id);
            $statement->execute();
            // Reset the auto increment to the highest value in the ID field.
            $statement = $this->db->prepare("ALTER TABLE `" . $table . "` AUTO_INCREMENT = 1");
            $statement->execute();
        }
    }

    public function insert_executed_imports($data = false) {
        $statement = $this->db->prepare("INSERT INTO imports_executed_log 
      (import_log_id, laboratory_id, import_in_progress, import_executed_date, executed_by_user_id)
      VALUES (:import_log_id, :laboratory_id, 1, NOW(), :executed_by_user_id)
    ");
        $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $data["laboratory_id"], PDO::PARAM_INT);
        $statement->bindValue(":executed_by_user_id", $data["executed_by_user_id"], PDO::PARAM_STR);
        $statement->execute();
        //return $this->db->lastInsertId();

        //run update_executed_import_end_date to complete test import.  RG
        $imports_executed_log_id = $this->db->lastInsertId();
        //$this->update_executed_import_end_date($imports_executed_log_id);
        return $imports_executed_log_id;
    }

    public function update_executed_import_end_date($imports_executed_log_id = false) {
        if ($imports_executed_log_id) {

            // Get the total number of records imported.
            $statement = $this->db->prepare("SELECT SQL_CALC_FOUND_ROWS protein_id
        FROM protein
        LEFT JOIN imports_executed_log ON imports_executed_log.import_log_id = protein.import_log_id
        WHERE imports_executed_log.imports_executed_log_id = :imports_executed_log_id");
            $statement->bindValue(":imports_executed_log_id", $imports_executed_log_id, PDO::PARAM_INT);
            $statement->execute();
            $statement = $this->db->prepare("SELECT FOUND_ROWS()");
            $statement->execute();
            $count = $statement->fetch(PDO::FETCH_ASSOC);

            // Update the 'number_of_records' and 'import_end_date' fields in the 'imports_executed_log' table.
            $statement = $this->db->prepare("UPDATE imports_executed_log
        SET number_of_records = " . $count["FOUND_ROWS()"] . "
          , import_end_date = NOW()
          , import_in_progress = 0
        WHERE imports_executed_log_id = :imports_executed_log_id
      ");
            $statement->bindValue(":imports_executed_log_id", $imports_executed_log_id, PDO::PARAM_INT);
            $statement->execute();
        }
    }

    public function update_reimport_in_progress($data = false) {
        // Update the 'reimport_in_progress' fields in the 'imports_executed_log' table.
        $statement = $this->db->prepare("UPDATE imports_executed_log 
      SET reimport_in_progress = :reimport_in_progress
      WHERE imports_executed_log_id = :imports_executed_log_id
    ");
        $statement->bindValue(":reimport_in_progress", $data["reimport_in_progress"], PDO::PARAM_INT);
        $statement->bindValue(":imports_executed_log_id", $data["imports_executed_log_id"], PDO::PARAM_INT);
        $statement->execute();
    }

    public function delete_data($table = false, $id = false) {

        // Hack... because I failed in naming the column names correctly.
        $id_column_name = ($table == "panorama_chromatogram_images") ? "panorama_chromatogram_images_id" : str_replace("panorama_", "", $table) . "_id";

        $statement = $this->db->prepare("
      DELETE
      FROM `" . $table . "`
      WHERE " . $id_column_name . " = " . (int)$id);
        $statement->execute();
    }

}