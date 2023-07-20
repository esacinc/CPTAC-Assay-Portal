<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 5/1/18
 * Time: 10:18 AM
 */

namespace assays\models;
use \PDO;

class WikiPathway {

    private $db;

    public function __construct($db_connection = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
    }

    public function find_wikipathway_category_by_name($name) {
        if($name) {
            $statement = $this->db->prepare("
                SELECT wikipathway_category_id, name FROM `wikipathway_category`
                WHERE name = :name;
            ");
            $statement->bindValue(":name", $name, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (count($data) > 0) {
                return $data;
            }
        }
        return false;
    }

    public function insert_wikipathway_category($name, $description, $parent_category_id) {
        if($name) {
            $pdo_params = [$name, $description, $parent_category_id];
            $statement = $this->db->prepare("
                INSERT INTO `wikipathway_category`
                (name, description, parent_category_id)
                VALUES
                (?, ?, ?)
            ");
            $statement->execute($pdo_params);
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function find_wikipathway_by_name($name) {
        if($name) {
            $statement = $this->db->prepare("
                SELECT wikipathway_id, name FROM `wikipathway`
                WHERE name = :name;
            ");
            $statement->bindValue(":name", $name, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (count($data) > 0) {
                return $data;
            }
        }
        return false;
    }

    public function find_wikipathway_by_id($wikipathway_id) {
        if($wikipathway_id) {
            $statement = $this->db->prepare("
                SELECT wikipathway_id, name, filename, wp_id FROM `wikipathway`
                WHERE wikipathway_id = :wikipathway_id;
            ");
            $statement->bindValue(":wikipathway_id", $wikipathway_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (count($data) > 0) {
                return $data;
            }
        }
        return false;
    }

    public function find_wikipathway_id_by_wp_id($wp_id) {
        if($wp_id) {
            $statement = $this->db->prepare("
                SELECT wikipathway_id, name, filename, wp_id FROM `wikipathway`
                WHERE wp_id = :wp_id;
            ");
            $statement->bindValue(":wp_id", $wp_id, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (count($data) > 0) {
                return $data;
            }
        }
        return false;
    }

    public function find_pathways_by_category($category_id) {
        if ($category_id) {
            $statement = $this->db->prepare("
                SELECT wikipathway_id, name FROM `wikipathway`
                WHERE category_id = :category_id;
            ");
            $statement->bindValue(":category_id", $category_id, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function find_categories_by_parent($parent_id) {
        if ($parent_id) {
            $statement = $this->db->prepare("
                SELECT wikipathway_category_id, name FROM `wikipathway_category`
                WHERE parent_category_id = :parent_id;
            ");
            $statement->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function add_category_and_wikipathways($category, $categories) {

        $pathways = $this->find_pathways_by_category($category['wikipathway_category_id']);
        if(count($pathways) > 0) {

            //$category['name'] = "-- " . $category['name'];
            $category['indent'] = "--";
            $categories[] = $category;

            foreach ($pathways as $key => $value) {

                //$value['name'] = "---- " . $value['name'];
                $value['indent'] = "----";
                $value['enabled'] = 1;

                $categories[] = $value;
            }
        }


        return $categories;
    }


    public function find_all_wikipathways() {
        $statement = $this->db->prepare("
                SELECT wikipathway_id, name FROM `wikipathway`;
            ");

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find_all_wikipathways_with_categories() {
        $categories = [];

        $categories[] = array("wikipathway_category_id" => 1, "name" => "CPTAC Wikipathway");
        $cptac_categories = $this->find_categories_by_parent(1);
        foreach ($cptac_categories as $cptac_category) {
            $categories = $this->add_category_and_wikipathways($cptac_category, $categories);
        }

        $categories[] = array("wikipathway_category_id" => 2, "name" => "General");
        $cptac_categories = $this->find_categories_by_parent(2);
        foreach ($cptac_categories as $cptac_category) {
            $categories = $this->add_category_and_wikipathways($cptac_category, $categories);
        }

        return $categories;
    }

    public function insert_wikipathway($name, $filename, $description, $category_id) {
        if($name && $filename) {
            $pdo_params = [$name, $filename, $description, $category_id];
            $statement = $this->db->prepare("
                INSERT INTO `wikipathway`
                (name, filename, description, category_id)
                VALUES
                (?, ?, ?, ?)
            ");
            $statement->execute($pdo_params);
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function find_wikipathway_uniprot_accession($wikipathway_id, $uniprot_accession_id) {
        if($wikipathway_id && $uniprot_accession_id) {
            $statement = $this->db->prepare("
                SELECT wikipathway_id, uniprot_accession_id  FROM `wikipathway_uniprot_accession`
                WHERE wikipathway_id = :wikipathway_id and uniprot_accession_id = :uniprot_accession_id;
            ");
            $statement->bindValue(":wikipathway_id", $wikipathway_id, PDO::PARAM_INT);
            $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (count($data) > 0) {
                return $data;
            }
        }
        return false;
    }

    public function find_uniprot_accession($wikipathway_id) {
        if($wikipathway_id) {
            $statement = $this->db->prepare("
                SELECT uniprot_accession_id  FROM `wikipathway_uniprot_accession`
                WHERE wikipathway_id = :wikipathway_id;
            ");
            $statement->bindValue(":wikipathway_id", $wikipathway_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (count($data) > 0) {
                return $data;
            }
        }
        return false;
    }

    public function insert_wikipathway_uniprot($wikipathway_id, $uniprot_accession_id) {
        if($wikipathway_id && $uniprot_accession_id) {
            $statement = $this->db->prepare("
                INSERT INTO `wikipathway_uniprot_accession`
                (wikipathway_id, uniprot_accession_id)
                VALUES
                (:wikipathway_id, :uniprot_accession_id);
            ");
            $statement->bindValue(":wikipathway_id", $wikipathway_id, PDO::PARAM_INT);
            $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
            $statement->execute();
            return true;
        }
        return false;
    }

}