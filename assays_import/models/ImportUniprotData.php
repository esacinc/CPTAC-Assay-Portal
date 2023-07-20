<?php

namespace assays_import\models;

use \PDO;

class ImportUniprotData {
    private $session_key = "";
    public $db;

    public function __construct($db_connection = false, $session_key = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
    }

    public function import_uniprot_data($data = false) {
        if ($data) {

            $gene_synonyms = (!empty($data["gene_synonym"])) ? implode(",", $data["gene_synonym"]) : NULL;
            $hgnc_gene_id = (!empty($data["hgnc_gene_id"])) ? $data["hgnc_gene_id"] : NULL;
            $protein_name = is_array($data["protein_name"]) ? $data["protein_name"]["@value"] : $data["protein_name"];

            // Update the protein table.
            $statement = $this->db->prepare("
        UPDATE protein
        SET uniprot_gene_synonym = :gene_synonyms
            ,uniprot_hgnc_gene_id = :hgnc_gene_id
            ,uniprot_kb = :uniprot_kb
            ,uniprot_protein_name = :protein_name
            ,uniprot_source_taxon_id = :source_taxon_id
            ,uniprot_sequence = :sequence
            ,uniprot_sequence_raw = :sequence_raw
            ,uniprot_sequence_length = :sequence_length
            ,protein_molecular_weight = :protein_molecular_weight
        WHERE uniprot_accession_id = :uniprot_accession_id");
            $statement->bindValue(":uniprot_accession_id", $data["uniprot_ac"], PDO::PARAM_STR);
            $statement->bindValue(":gene_synonyms", $gene_synonyms, PDO::PARAM_STR);
            $statement->bindValue(":hgnc_gene_id", $hgnc_gene_id, PDO::PARAM_STR);
            $statement->bindValue(":uniprot_kb", $data["uniprot_kb"], PDO::PARAM_STR);
            $statement->bindValue(":protein_name", $protein_name, PDO::PARAM_STR);
            $statement->bindValue(":source_taxon_id", $data["source_taxon_id"], PDO::PARAM_STR);
            $statement->bindValue(":sequence", $data["sequence"], PDO::PARAM_STR);
            $statement->bindValue(":sequence_raw", $data["sequence_raw"], PDO::PARAM_STR);
            $statement->bindValue(":sequence_length", $data["sequence_length"], PDO::PARAM_INT);
            $statement->bindValue(":protein_molecular_weight", $data["mass"], PDO::PARAM_INT);
            $statement->execute();

            // Update the peptide_start and peptide_end in the analyte_peptide table.
            $statement = $this->db->prepare("
        UPDATE analyte_peptide
        SET
          peptide_start = :peptide_start
         ,peptide_end = :peptide_end
        WHERE peptide_sequence = :peptide_sequence
      ");
            $statement->bindValue(":peptide_sequence", $data["peptide_sequence"], PDO::PARAM_STR);
            $statement->bindValue(":peptide_start", $data["peptide_start"], PDO::PARAM_INT);
            $statement->bindValue(":peptide_end", $data["peptide_end"], PDO::PARAM_INT);
            $statement->execute();
        }
    }

    public function truncate_uniprot_splice_junctions() {
        $statement = $this->db->prepare("
      TRUNCATE TABLE uniprot_splice_junctions");
        $statement->execute();
    }

    public function import_uniprot_splice_junctions($data = false, $uniprot_accession_id = false) {

        if ($data && $uniprot_accession_id) {
            foreach ($data as $value) {

                $status = $value["status"] ? $value["status"] : NULL;

                // Query the table to see if the record exists, which will determine if we're updating or inserting.
                $statement = $this->db->prepare("
          SELECT uniprot_accession_id, start, stop, type, description, status
          FROM uniprot_splice_junctions
          WHERE uniprot_accession_id = :uniprot_accession_id
          AND start = :start
          AND stop = :stop
          AND type = :type
          AND description = :description
          ");
                $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                $statement->bindValue(":start", $value["start"], PDO::PARAM_STR);
                $statement->bindValue(":stop", $value["stop"], PDO::PARAM_STR);
                $statement->bindValue(":type", $value["type"], PDO::PARAM_STR);
                $statement->bindValue(":description", $value["description"], PDO::PARAM_STR);
                $statement->execute();
                $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

                if (!$existing_data) {
                    // Insert a new record.
                    $statement = $this->db->prepare("
            INSERT INTO uniprot_splice_junctions
            (uniprot_accession_id, start, stop, type, description, status)
            VALUES(:uniprot_accession_id, :start, :stop, :type, :description, :status)");
                    $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                    $statement->bindValue(":start", $value["start"], PDO::PARAM_INT);
                    $statement->bindValue(":stop", $value["stop"], PDO::PARAM_INT);
                    $statement->bindValue(":type", $value["type"], PDO::PARAM_STR);
                    $statement->bindValue(":description", $value["description"], PDO::PARAM_STR);
                    $statement->bindValue(":status", $status, PDO::PARAM_STR);
                    $statement->execute();
                } else {
                    // Update the record.
                    $statement = $this->db->prepare("
            UPDATE uniprot_splice_junctions
            SET start = :start
                ,stop = :stop
                ,type = :type
                ,description = :description
                ,status = :status
            WHERE uniprot_accession_id = :uniprot_accession_id
            AND start = :start
            AND stop = :stop
            AND type = :type
            AND description = :description
          ");
                    $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                    $statement->bindValue(":start", $value["start"], PDO::PARAM_INT);
                    $statement->bindValue(":stop", $value["stop"], PDO::PARAM_INT);
                    $statement->bindValue(":type", $value["type"], PDO::PARAM_STR);
                    $statement->bindValue(":description", $value["description"], PDO::PARAM_STR);
                    $statement->bindValue(":status", $status, PDO::PARAM_STR);
                    $statement->execute();
                }
            }
        }
    }

    // Remove duplicate uniprot_splice_junctions
    // CREATE TABLE `uniprot_splice_junctions_new` as
    // SELECT * FROM `uniprot_splice_junctions` WHERE 1 GROUP BY `uniprot_accession_id`, `start`, `stop`

    public function truncate_uniprot_snps() {
        $statement = $this->db->prepare("
      TRUNCATE TABLE uniprot_snps");
        $statement->execute();
    }

    public function import_uniprot_snps($data = false, $uniprot_accession_id = false) {
        if ($data && $uniprot_accession_id) {
            foreach ($data as $value) {

                // Query the table to see if the record exists, which will determine if we're updating or inserting.
                $statement = $this->db->prepare("
          SELECT uniprot_accession_id, position, original, variation
          FROM uniprot_snps
          WHERE uniprot_accession_id = :uniprot_accession_id
          AND position = :position
          AND original = :original
          AND variation = :variation
          ");
                $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                $statement->bindValue(":position", $value["position"], PDO::PARAM_INT);
                $statement->bindValue(":original", $value["original"], PDO::PARAM_STR);
                $statement->bindValue(":variation", $value["variation"], PDO::PARAM_STR);
                $statement->execute();
                $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

                if (!$existing_data) {
                    // Insert a new record.
                    $statement = $this->db->prepare("
            INSERT INTO uniprot_snps
            (uniprot_accession_id, position, original, variation)
            VALUES(:uniprot_accession_id, :position, :original, :variation)");
                    $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                    $statement->bindValue(":position", $value["position"], PDO::PARAM_INT);
                    $statement->bindValue(":original", $value["original"], PDO::PARAM_STR);
                    $statement->bindValue(":variation", $value["variation"], PDO::PARAM_STR);
                    $statement->execute();
                } else {
                    // Update the record.
                    $statement = $this->db->prepare("
            UPDATE uniprot_snps
            SET position = :position
                ,original = :original
                ,variation = :variation
            WHERE uniprot_accession_id = :uniprot_accession_id
            AND position = :position
            AND original = :original
            AND variation = :variation
          ");
                    $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                    $statement->bindValue(":position", $value["position"], PDO::PARAM_INT);
                    $statement->bindValue(":original", $value["original"], PDO::PARAM_STR);
                    $statement->bindValue(":variation", $value["variation"], PDO::PARAM_STR);
                    $statement->execute();
                }
            }
        }
    }

    // Remove duplicate uniprot_snps
    // CREATE TABLE `uniprot_snps_new` as
    // SELECT * FROM `uniprot_snps` WHERE 1 GROUP BY `uniprot_accession_id`, `position`, `original`, `variation`

    public function truncate_uniprot_isoforms() {
        $statement = $this->db->prepare("
      TRUNCATE TABLE uniprot_isoforms");
        $statement->execute();
    }

    public function import_uniprot_isoforms($data = false, $uniprot_accession_id = false) {
        if ($data && $uniprot_accession_id) {
            foreach ($data as $value) {

                $note = (isset($value["note"]) && !empty($value["note"]) && !is_array($value["note"])) ? $value["note"] : NULL;
                $name = is_array($value["name"]) ? json_encode($value["name"]) : $value["name"];
                $id = is_array($value["id"]) ? json_encode($value["id"]) : $value["id"];
                $sequence = is_array($value["sequence"]) ? json_encode($value["sequence"]) : $value["sequence"];
                $sequence_length = (isset($value["sequence_length"]) && !empty($value["sequence_length"])) ? $value["sequence_length"] : NULL;

                if ($sequence_length !== NULL) {

                    // Query the table to see if the record exists, which will determine if we're updating or inserting.
                    $statement = $this->db->prepare("
            SELECT uniprot_accession_id, id, name, sequence, note, sequence_length
            FROM uniprot_isoforms
            WHERE uniprot_accession_id = :uniprot_accession_id
            AND id = :id
            AND name = :name
            AND sequence = :sequence
            AND sequence_length = :sequence_length
            ");
                    $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                    $statement->bindValue(":id", $value["id"], PDO::PARAM_STR);
                    $statement->bindValue(":name", $name, PDO::PARAM_STR);
                    $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
                    $statement->bindValue(":sequence_length", $sequence_length, PDO::PARAM_INT);
                    $statement->execute();
                    $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

                    if (!$existing_data) {
                        // Insert into the database
                        $statement = $this->db->prepare("
              INSERT INTO uniprot_isoforms
              (uniprot_accession_id, id, name, sequence, note, sequence_length)
              VALUES(:uniprot_accession_id, :id, :name, :sequence, :note, :sequence_length)");
                        $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                        $statement->bindValue(":id", $value["id"], PDO::PARAM_STR);
                        $statement->bindValue(":name", $name, PDO::PARAM_STR);
                        $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
                        $statement->bindValue(":note", $note, PDO::PARAM_STR);
                        $statement->bindValue(":sequence_length", $sequence_length, PDO::PARAM_INT);
                        $statement->execute();
                    } else {
                        // Update the record.
                        $statement = $this->db->prepare("
              UPDATE uniprot_isoforms
              SET id = :id
                  ,name = :name
                  ,sequence = :sequence
                  ,note = :note
                  ,sequence_length = :sequence_length
              WHERE uniprot_accession_id = :uniprot_accession_id
              AND id = :id
              AND name = :name
              AND sequence = :sequence
              AND note = :note
              AND sequence_length = :sequence_length
            ");
                        $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                        $statement->bindValue(":id", $value["id"], PDO::PARAM_STR);
                        $statement->bindValue(":name", $name, PDO::PARAM_STR);
                        $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
                        $statement->bindValue(":note", $note, PDO::PARAM_STR);
                        $statement->bindValue(":sequence_length", $sequence_length, PDO::PARAM_INT);
                        $statement->execute();
                    }
                }
            }
        }
    }

    // Remove duplicate uniprot_isoforms
    // CREATE TABLE `uniprot_isoforms_new` as
    // SELECT * FROM `uniprot_isoforms` WHERE 1 GROUP BY `uniprot_accession_id`, `id`, `name`, `sequence`, `note`, `uniprot_sequence_length`

    public function get_incomplete_protein_records($import_log_id = false) {
        $statement = $this->db->prepare("
      SELECT DISTINCT uniprot_accession_id
      FROM protein 
      WHERE import_log_id = :import_log_id
      AND uniprot_kb IS NULL
      AND uniprot_sequence IS NULL
      AND uniprot_source_taxon_id IS NULL
    ");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function import_missed_uniprot_data($uniprot_accession_id = false, $uniprot_data = false) {
        $data["uniprot_kb"] = isset($uniprot_data["uniprot_kb"]) ? $uniprot_data["uniprot_kb"] : NULL;
        $data["uniprot_protein_name"] = isset($uniprot_data["protein_name"]["@value"])
            ? $uniprot_data["protein_name"]["@value"]
            : NULL;
        $data["uniprot_source_taxon_id"] = isset($uniprot_data["source_taxon_id"]) ? $uniprot_data["source_taxon_id"] : NULL;
        $data["uniprot_sequence"] = isset($uniprot_data["sequence"]) ? $uniprot_data["sequence"] : NULL;
        $data["uniprot_sequence_raw"] = isset($uniprot_data["sequence_raw"]) ? $uniprot_data["sequence_raw"] : NULL;
        $data["uniprot_sequence_length"] = isset($uniprot_data["sequence_length"]) ? $uniprot_data["sequence_length"] : NULL;
        $data["protein_molecular_weight"] = isset($uniprot_data["mass"]) ? $uniprot_data["mass"] : NULL;

        // Update the database
        $statement = $this->db->prepare("
      UPDATE protein
      SET uniprot_kb = :uniprot_kb
          ,uniprot_protein_name = :uniprot_protein_name
          ,uniprot_source_taxon_id = :uniprot_source_taxon_id
          ,uniprot_sequence = :uniprot_sequence
          ,uniprot_sequence_raw = :uniprot_sequence_raw
          ,uniprot_sequence_length = :uniprot_sequence_length
          ,protein_molecular_weight = :protein_molecular_weight
      WHERE uniprot_accession_id = :uniprot_accession_id");
        $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
        $statement->bindValue(":uniprot_kb", $data["uniprot_kb"], PDO::PARAM_STR);
        $statement->bindValue(":uniprot_protein_name", $data["uniprot_protein_name"], PDO::PARAM_STR);
        $statement->bindValue(":uniprot_source_taxon_id", $data["uniprot_source_taxon_id"], PDO::PARAM_STR);
        $statement->bindValue(":uniprot_sequence", $data["uniprot_sequence"], PDO::PARAM_STR);
        $statement->bindValue(":uniprot_sequence_raw", $data["uniprot_sequence_raw"], PDO::PARAM_STR);
        $statement->bindValue(":uniprot_sequence_length", $data["uniprot_sequence_length"], PDO::PARAM_INT);
        $statement->bindValue(":protein_molecular_weight", $data["protein_molecular_weight"], PDO::PARAM_INT);
        $statement->execute();
    }

}

