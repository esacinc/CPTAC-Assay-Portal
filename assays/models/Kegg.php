<?php
namespace assays\models;

use \PDO;

class Kegg {

    public $db;

    public function __construct($db_connection = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
    }

    public function flatten_kegg_hierarchy($kegg_hierarchy) {
        $single_level_array = array();
        foreach ($kegg_hierarchy as $single_node) {
            $descendants = false;
            if (isset($single_node["descendants"]) && $single_node["descendants"]) {
                $descendants = $single_node["descendants"];
                unset($single_node["descendants"]);
            }
            $single_level_array[] = $single_node;
            if ($descendants) {
                $single_level_array = array_merge($single_level_array, $this->flatten_kegg_hierarchy($descendants));
            }
        }
        return $single_level_array;
    }

    public function get_descendants(&$keggs, $level = 0, $indent_char = "-") {
        $level += 1;
        $indent_string = "";
        for ($i = 1; $i <= $level; $i++) {
            $indent_string .= $indent_char;
        }
        foreach ($keggs as &$single_kegg) {
            $statement = $this->db->prepare("
            SELECT descendant AS kegg_id
            ,name
            ,real_kegg_id
            ,'{$indent_string}' AS indent
        FROM kegg_closure_table
        LEFT JOIN `kegg` ON `kegg`.kegg_id = kegg_closure_table.descendant
        WHERE ancestor = :kegg_id
        AND ancestor != descendant
        AND pathlength = 1
        GROUP BY descendant
        ORDER BY name ASC");
            $statement->bindValue(":kegg_id", $single_kegg["kegg_id"], PDO::PARAM_INT);
            $statement->execute();
            $descendants = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($descendants) {
                $single_kegg["descendants"] = $descendants;
                $this->get_descendants($single_kegg["descendants"], $level, $indent_char);
            }
        }
    }

    public function get_kegg_hierarchy($indent_char = "-") {
        //get the root nodes
        $statement = $this->db->prepare("
      SELECT descendant AS kegg_id
            ,COUNT(ancestor) AS total_parents
          ,name
      FROM kegg_closure_table
      LEFT JOIN `kegg` ON `kegg`.kegg_id = kegg_closure_table.descendant
      GROUP BY descendant
      HAVING total_parents = 1
      ORDER BY name ASC");
        $statement->execute();
        $root_nodes = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->get_descendants($root_nodes, 0, $indent_char);
        return $root_nodes;
    }

    public function get_kegg_record($kegg_id) {
        $statement = $this->db->prepare("
      SELECT *
      FROM `kegg`
      WHERE kegg_id = :kegg_id");
        $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        //get parent kegg
        $statement = $this->db->prepare("
      SELECT ancestor
      FROM kegg_closure_table
      WHERE descendant = :kegg_id
      AND pathlength = 1");
        $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
        $statement->execute();
        $parent_kegg = $statement->fetch(PDO::FETCH_ASSOC);
        $data["kegg_parent"] = $parent_kegg["ancestor"];

        return $data;
    }

    public function get_keggs($kegg_ids = false) {
        $pdo_params = array(
            1 //active
        );
        $kegg_sql = "";
        if ($kegg_ids && is_array($kegg_ids)) {
            $question_marks = array();
            foreach ($kegg_ids as $single_kegg_id) {
                $pdo_params[] = $single_kegg_id;
                $question_marks[] = "?";
            }
            $kegg_sql = " AND kegg_id IN (" . implode(",", $question_marks) . ") ";
        } elseif ($kegg_ids && is_numeric($kegg_ids)) {
            $pdo_params[] = $kegg_ids;
            $kegg_sql = " AND kegg_id = ? ";
        }

        $statement = $this->db->prepare("
      SELECT kegg_id
          ,name 
      FROM `kegg`
      WHERE active = ?
      {$kegg_sql}
      ORDER BY name");
        $statement->execute($pdo_params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_kegg_id($name) {
        $statement = $this->db->prepare("
      SELECT kegg_id
      FROM kegg
      WHERE name = '$name'");
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_real_kegg_id($kegg_id) {
        $statement = $this->db->prepare("
      SELECT real_kegg_id
      FROM kegg
      WHERE kegg_id = '$kegg_id'");
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public function is_descendant($kegg_id = false) {
        //get parent kegg
        $statement = $this->db->prepare("
      SELECT ancestor
      FROM kegg_closure_table
      WHERE descendant = :kegg_id
      AND pathlength = 1");
        $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
        $statement->execute();
        $parent_kegg = $statement->fetch(PDO::FETCH_ASSOC);
        $data["kegg_parent"] = $parent_kegg["ancestor"];

        return $data;
    }

    public function insert_update_kegg($data, $kegg_id = false) {

        $pdo_params = array(
            $data["name"]
        );

        if (!empty($kegg_id)) {
            $pdo_params[] = $kegg_id;
            $statement = $this->db->prepare("
            UPDATE `kegg`
            SET name = ?
            WHERE kegg_id = ?");
            $statement->execute($pdo_params);
        } else {
            $statement = $this->db->prepare("
            INSERT INTO `kegg`
            (name)
            VALUES
            (?)");
            $statement->execute($pdo_params);
            $kegg_id = $this->db->lastInsertId();
        }

        //update the keggs closure table per Bill Karwin's SQL Antipatterns Chapter 3
        //the pathlengh column refers to the jumps in between the ancestor and descendant - self-reference = 0, first child = 1 and so forth
        //check to see if we need to update or insert records first
        $kegg_parent = (isset($data["kegg_parent"]) && $data["kegg_parent"]) ? $data["kegg_parent"] : false;
        $statement = $this->db->prepare("
          SELECT *
          FROM kegg_closure_table
          WHERE descendant = :kegg_id");
        $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
        $statement->execute();
        $closure_check = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($closure_check) {
            //we need to move everything under it as well
            //first, detatch the node subtree
            $statement = $this->db->prepare("
            DELETE FROM kegg_closure_table
        WHERE descendant IN (
          SELECT tmpdescendant.d FROM (
            SELECT descendant AS d FROM kegg_closure_table WHERE ancestor = :kegg_id
          ) AS tmpdescendant
        )
        AND ancestor IN (
          SELECT tmpancestor.a FROM (
            SELECT ancestor AS a FROM kegg_closure_table WHERE descendant = :kegg_id2 AND ancestor != descendant
          ) AS tmpancestor
        )");
            $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
            $statement->bindValue(":kegg_id2", $kegg_id, PDO::PARAM_INT);
            $statement->execute();

            //now attached the subtree under the updated kegg
            $statement = $this->db->prepare("
            INSERT INTO kegg_closure_table
              (ancestor, descendant, pathlength)
              SELECT supertree.ancestor, subtree.descendant, subtree.pathlength+1
              FROM kegg_closure_table AS supertree
              CROSS JOIN kegg_closure_table AS subtree
              WHERE supertree.descendant = :new_parent
              AND subtree.ancestor = :kegg_id");
            $statement->bindValue(":new_parent", $kegg_parent, PDO::PARAM_INT);
            $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
            $statement->execute();
        } else {
            //just insert the leaf node
            $statement = $this->db->prepare("
            INSERT INTO kegg_closure_table
              (ancestor, descendant, pathlength)
              SELECT gct.ancestor, :kegg_id, pathlength+1
              FROM kegg_closure_table AS gct
              WHERE gct.descendant = :parent_kegg
              UNION ALL
              SELECT :kegg_id2, :kegg_id3,0");
            $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
            $statement->bindValue(":parent_kegg", $kegg_parent, PDO::PARAM_INT);
            $statement->bindValue(":kegg_id2", $kegg_id, PDO::PARAM_INT);
            $statement->bindValue(":kegg_id3", $kegg_id, PDO::PARAM_INT);
            $statement->execute();
        }

        return $kegg_id;
    }


    public function insert_kegg_id($data_ids = false) {

        if ($data_ids) {
            $statement = $this->db->prepare("
          SELECT *
          FROM kegg
          WHERE name = :name");
            $statement->bindValue(":name", $data_ids[1], PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $statement = $this->db->prepare("
            UPDATE `kegg`
            SET real_kegg_id = LPAD(:real_kegg_id, 5, '0')
            WHERE kegg_id = :kegg_id");
                $statement->bindValue(":real_kegg_id", $data_ids[0], PDO::PARAM_INT);
                $statement->bindValue(":kegg_id", $data["kegg_id"], PDO::PARAM_INT);
                $statement->execute();
            }
        }

    }


    public function import_kegg_uniprot_data($biodbnet_api_url, $xml2array) {

        // Get all of the KEGG ids
        $statement = $this->db->prepare("
        SELECT real_kegg_id
        FROM kegg
        WHERE real_kegg_id IS NOT NULL");
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Insert into the kegg_uniprot_map table
        // $i=0;
        foreach ($data as $kegg_data) {
            $biodbnet_result = file_get_contents($biodbnet_api_url . "?input=keggpathwayid&inputValues=hsa" . $kegg_data["real_kegg_id"] . "&outputs=uniprotaccession&taxonId=9606");

            // Convert into an associative PHP array
            $biodbnet_result = json_decode($biodbnet_result, true);

            if (!empty($biodbnet_result[0]["outputs"])) {
                foreach ($biodbnet_result[0]["outputs"]["UniProt Accession"] as $uniprot_accession_id) {
                    $statement = $this->db->prepare("
            INSERT INTO `kegg_uniprot_map`
              (kegg_id, uniprot_accession_id)
              VALUES ( LPAD(" . $kegg_data["real_kegg_id"] . ", 5, '0'), :uniprot_accession_id )");
                    $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
                    $statement->execute();
                }
            }

        }

    }

    public function truncate_all_kegg_tables() {
        // Clear the kegg table
        $statement = $this->db->prepare("
        TRUNCATE TABLE kegg");
        $statement->execute();
        // Clear the kegg_closure_table table
        $statement = $this->db->prepare("
        TRUNCATE TABLE kegg_closure_table");
        $statement->execute();
        // Clear the kegg_uniprot_map table
        $statement = $this->db->prepare("
        TRUNCATE TABLE kegg_uniprot_map");
        $statement->execute();
    }

}

?>