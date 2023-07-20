<?php

namespace user_account\models;

use core\models\Db\AbstractDao;
use core\models\Db\DbResource;
use core\models\Db\SqlUtils;
use PDO;

class UserAccountDao extends AbstractDao {
    const ACCOUNT_DATA_TYPES = [
        "account_id" => PDO::PARAM_INT,
        "account_type_id" => PDO::PARAM_INT,
        "username" => PDO::PARAM_STR,
        "displayname" => PDO::PARAM_STR,
        "given_name" => PDO::PARAM_STR,
        "sn" => PDO::PARAM_STR,
        "middle_name" => PDO::PARAM_STR,
        "initials" => PDO::PARAM_STR,
        "email" => PDO::PARAM_STR,
        "phone" => PDO::PARAM_STR,
        "personal_title" => PDO::PARAM_STR,
        "title" => PDO::PARAM_STR,
        "building_name" => PDO::PARAM_STR,
        "room_number" => PDO::PARAM_STR,
        "address" => PDO::PARAM_STR,
        "street" => PDO::PARAM_STR,
        "location" => PDO::PARAM_STR,
        "state_abbr" => PDO::PARAM_STR,
        "postalcode" => PDO::PARAM_STR,
        "country_abbr" => PDO::PARAM_STR,
        "oauth_id" => PDO::PARAM_STR,
        "ned_id" => PDO::PARAM_STR,
        "manager_ned_id" => PDO::PARAM_STR,
        "nihprimarysmtp" => PDO::PARAM_STR,
        "organization_acronym" => PDO::PARAM_STR,
        "organization" => PDO::PARAM_STR,
        "organization_path" => PDO::PARAM_STR,
        "ou_acronym" => PDO::PARAM_STR,
        "ou_name" => PDO::PARAM_STR,
        "domain" => PDO::PARAM_STR,
        "site" => PDO::PARAM_STR,
        "organizationalstat" => PDO::PARAM_STR,
        "password" => PDO::PARAM_STR,
        "title_suffix" => PDO::PARAM_STR,
        "password_reset_selector" => PDO::PARAM_STR,
        "password_reset_token" => PDO::PARAM_STR,
        "password_reset_timestamp" => PDO::PARAM_STR
    ];

    const USER_ACCOUNT_DATA_TYPES = [
        "account_id" => PDO::PARAM_INT,
        "acceptable_use_policy" => PDO::PARAM_BOOL,
        "created_date" => PDO::PARAM_STR,
        "modified_date" => PDO::PARAM_STR,
        "send_emails" => PDO::PARAM_BOOL,
        "comments" => PDO::PARAM_STR
    ];

    public function __construct(DbResource $db, $session_key = false) {
        parent::__construct($db, $session_key);
    }

    public function browse_user_accounts($sort_field = false, $sort_order = 'DESC', $start_record = 0, $stop_record = 20, $search = false, $column_filters = false, $sortable_fields = false) {
        $sort = "";
        $search_sql = "";
        $column_filter_sql = "";
        $pdo_params = array();

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if ($sort_field) {
            switch ($sort_field) {
                case 'last_modified':
                    $sort = " ORDER BY user_account_groups.last_modified {$sort_order} ";
                    break;
                default:
                    $sort = " ORDER BY {$sort_field} {$sort_order} ";
            }
        }

        if ($search) {
            $pdo_params[] = '%' . $search . '%';
            $search_sql = "
				AND (
					account.displayname LIKE ?
				) ";
        }

        $comparison_array = array(
            "gt" => " > "
        , "gt_or_eq" => " >= "
        , "lt" => "<"
        , "lt_or_eq" => " <= "
        , "equals" => " = "
        , "contains" => "contains"
        , "not_contain" => "not_contain"
        , "start_with" => "start_with"
        , "end_with" => "end_with"
        );

        if ($column_filters) {
            $column_filter_array = array();
            foreach ($column_filters as $filter) {
                $params = $filter;
                if (is_object($filter)) {
                    $params = get_object_vars($filter);
                }

                if (isset($params['value'])
                    && $params['value']
                    && isset($params['comparison'])
                    && $params['comparison']
                    && isset($comparison_array[$params['comparison']])
                    && $comparison_array[$params['comparison']]
                    && isset($params['column'])
                    && array_key_exists($params['column'], $sortable_fields)) // $sortable_fields -- insuring against SQL injection
                {

                    switch ($params['comparison']) {
                        case "contains": //contains
                            $comparison_and_value = " LIKE ? ";
                            $pdo_params[] = "%" . $params['value'] . "%";
                            break;
                        case "not_contains": //does not contain
                            $comparison_and_value = " NOT LIKE ? ";
                            $pdo_params[] = "%" . $params['value'] . "%";
                            break;
                        case "start_with": //starts with
                            $comparison_and_value = " LIKE ? ";
                            $pdo_params[] = $params['value'] . "%";
                            break;
                        case "end_with": //ends with
                            $comparison_and_value = " LIKE ? ";
                            $pdo_params[] = "%" . $params['value'];
                            break;
                        default:
                            $comparison_and_value = $comparison_array[$params['comparison']] . " ? ";
                            $pdo_params[] = $params['value'];
                    }

                    $column_filter_array[] = " ({$params['column']} {$comparison_and_value}) ";
                }
            }

            if ($column_filter_array) {
                $column_filter_sql = " AND (" . implode(' AND ', $column_filter_array) . ") ";
            }

        }

        $statement = $this->db->prepare("
        	SELECT SQL_CALC_FOUND_ROWS
        		user_account_groups.account_id AS manage
        		,user_account_groups.account_id
        		,account.displayname AS name
        		,GROUP_CONCAT(DISTINCT group.name SEPARATOR ', ') AS groups
        		,user_account_groups.account_id AS DT_RowId
			FROM user_account_groups
			LEFT JOIN account ON account.account_id = user_account_groups.account_id
			LEFT JOIN `group` ON `group`.group_id = user_account_groups.group_id
			{$search_sql}
			GROUP BY user_account_groups.account_id
			HAVING 1=1
			{$column_filter_sql}
      		{$sort}
			 ");
        $statement->execute($pdo_params);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = $this->db->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
    
        return $data;
    }

    public function get_user_account_groups($account_id) {
        $statement = $this->db->prepare("
        	SELECT `group`.group_id
        		   ,`group`.name AS group_name
        	FROM user_account_groups
        	LEFT JOIN `group` ON `group`.group_id = user_account_groups.group_id
        	WHERE user_account_groups.account_id = :account_id
        	GROUP BY `group`.group_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_user_account_groups_with_subgroups($account_id) {
        $statement = $this->db->prepare("
        	SELECT `group`.group_id
        		   ,`group`.name AS group_name
        	FROM user_account_groups
        	LEFT JOIN group_closure_table ON group_closure_table.ancestor = user_account_groups.group_id
        	LEFT JOIN `group` ON `group`.group_id = group_closure_table.descendant
        	WHERE user_account_groups.account_id = :account_id
        	GROUP BY `group`.group_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_user_group_roles($account_id, $group_id) {
        $statement = $this->db->prepare("
        	SELECT user_account_roles.role_id
        		   ,user_account_roles.label AS role_label
        	FROM user_account_groups
        	LEFT JOIN user_account_roles ON user_account_roles.role_id = user_account_groups.role_id
        	WHERE user_account_groups.account_id = :account_id
        	AND user_account_groups.group_id = :group_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_roles($exclude_ids = array()) {
        $exclude_id_sql = "";
        if (!empty($exclude_ids)) {
            $exclude_id_sql = " AND user_account_roles.role_id NOT IN (" . implode(",", $exclude_ids) . ") ";
        }
        $statement = $this->db->prepare("
        	SELECT *
        	FROM user_account_roles
        	WHERE 1=1
        	AND active=1
        	{$exclude_id_sql}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_user_account_info($account_id = false, $ned_number = false) {
        $pdo_params = array();

        $account_id_sql = "";
        if (!empty($account_id)) {
            $account_id_sql = " AND account_id = ? ";
            $pdo_params[] = $account_id;
        }

        $ned_number_sql = "";
        if (!empty($ned_number)) {
            $ned_number_sql = " AND ned_id = ? ";
            $pdo_params[] = $ned_number;
        }

        $statement = $this->db->prepare("
        	SELECT sn
        		  ,username AS cn
        		  ,given_name
        		  ,displayname
        		  ,account_id
        		  ,email
        	FROM account
        	WHERE 1=1
        	{$account_id_sql}
        	{$ned_number_sql}");
        $statement->execute($pdo_params);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function get_user_accounts($role_id = array(), $group_id = array()) {
        $pdo_params = array();

        $role_id_sql = "";
        if (!empty($role_id)) {
            $placeholders = array();
            foreach ($role_id as $single_role) {
                $placeholders[] = "?";
                $pdo_params[] = $single_role;
            }
            $role_id_sql = " AND user_account_groups.role_id IN (" . implode(",", $placeholders) . ")";
        }

        $group_id_sql = "";
        if (!empty($group_id)) {
            $placeholders = array();
            foreach ($group_id as $single_group) {
                $placeholders[] = "?";
                $pdo_params[] = $single_group;
            }
            $group_id_sql = " AND user_account_groups.group_id IN (" . implode(",", $placeholders) . ")";
        }

        $statement = $this->db->prepare("
        	SELECT account.sn
        		  ,account.username AS cn
        		  ,account.given_name
        		  ,account.displayname
        		  ,account.account_id
        		  ,account.email
        	FROM account
        	LEFT JOIN user_account_groups ON user_account_groups.account_id = account.account_id
        	WHERE 1=1
        	{$role_id_sql}
        	{$group_id_sql}");
        $statement->execute($pdo_params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert_update_user_account($data, $account_id, $update_groups = true, $proxy_role_id = false) {
        $account_exists = $this->account_exists($account_id);
        if (!$account_exists) {
            //insert
            $statement = $this->db->prepare("
	        	INSERT INTO user_account
	        	(account_id
	        	,created_date
	        	,modified_date)
	        	VALUES
	        	(:account_id
	        	,NOW()
				,NOW())");
            $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
            $statement->execute();
        } else {
            //update
            $statement = $this->db->prepare("
	        	UPDATE user_account
	        	SET modified_date = NOW()
	        	WHERE account_id = :account_id");
            $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
            $statement->execute();
        }

        if ($update_groups) {
            //remove all groups/roles because we are going to add them all back in
            $this->delete_user_groups($account_id);
            if (isset($data["group_data"]) && $data["group_data"]) {
                $group_array = array_filter(json_decode($data["group_data"], true));
                foreach ($group_array as $single_group_data) {
                    if (!empty($single_group_data) && !empty($single_group_data["roles"])) {
                        foreach ($single_group_data["roles"] as $single_role) {
                            $statement = $this->db->prepare("
					        	INSERT INTO user_account_groups
					        	(role_id
					        	,account_id
					        	,group_id)
					        	VALUES
					        	(:role_id
					        	,:account_id
					        	,:group_id)");
                            $statement->bindValue(":role_id", $single_role, PDO::PARAM_INT);
                            $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
                            $statement->bindValue(":group_id", $single_group_data["group_id"], PDO::PARAM_INT);
                            $statement->execute();

                            if ($single_role == $proxy_role_id) {
                                if (!empty($single_group_data["proxy_users"])) {
                                    $user_account_groups_id = $this->db->lastInsertId();
                                    foreach ($single_group_data["proxy_users"] as $single_proxy_user) {
                                        $statement = $this->db->prepare("
								        	INSERT INTO user_account_proxy
								        	(user_account_groups_id
								        	,proxy_account_id)
								        	VALUES
								        	(:user_account_groups_id
								        	,:proxy_account_id)");
                                        $statement->bindValue(":user_account_groups_id", $user_account_groups_id, PDO::PARAM_INT);
                                        $statement->bindValue(":proxy_account_id", $single_proxy_user["account_id"], PDO::PARAM_INT);
                                        $statement->execute();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function find_user_account($search) {
        $statement = $this->db->prepare("
        	SELECT CONCAT(displayname, ' (', username , ')') AS displayname
        		  ,account_id
        		  ,email
        		  ,phone
        	FROM account
        	WHERE displayname LIKE :displayname
        	LIMIT 20");
        $statement->bindValue(":displayname", "%" . $search . "%", PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete_user_account($account_id) {
        $statement = $this->db->prepare("
        	DELETE FROM user_account
        	WHERE account_id = :account_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();

        $statement = $this->db->prepare("
        	DELETE FROM user_account_groups
        	WHERE account_id = :account_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function delete_user_groups($account_id) {
        $statement = $this->db->prepare("
        	DELETE FROM user_account_groups
        	WHERE account_id = :account_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function account_exists($account_id) {
        $statement = $this->db->prepare("
        	SELECT *
			FROM user_account
			WHERE account_id = :account_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function is_registered($account_id) {

        $statement = $this->db->prepare("
        	SELECT user_account.acceptable_use_policy
        		  ,GROUP_CONCAT(user_account_groups.group_id SEPARATOR ', ') AS groups
        	FROM user_account
        	LEFT JOIN user_account_groups ON user_account.account_id = user_account_groups.account_id
        	WHERE user_account.account_id = :account_id
        	AND user_account.acceptable_use_policy = 1
        	GROUP BY user_account.account_id
        	HAVING groups != ''");


        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    //gets all the roles that the user is associated to
    //returns an array all all the roles
    public function get_user_roles_list($account_id) {
        $statement = $this->db->prepare("
        	SELECT DISTINCT role_id
			FROM user_account_groups
			WHERE account_id = :account_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_acceptable_use_policy($account_id, $value) {
        $statement = $this->db->prepare("
        	UPDATE user_account
        	SET acceptable_use_policy = :acceptable_use_policy
        	WHERE account_id = :account_id");
        $statement->bindValue(":acceptable_use_policy", $value, PDO::PARAM_INT);
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
    }

    /* gets all the proxies that the user is associated with for a specific group
     */
    public function get_users_proxies_for_group($account_id, $group_id) {
        $statement = $this->db->prepare("
        	SELECT CONCAT(account.displayname, ' (', account.username , ')') AS displayname
        		  ,account.account_id
        	FROM user_account_groups
        	RIGHT JOIN user_account_proxy ON user_account_proxy.user_account_groups_id = user_account_groups.user_account_groups_id
        	LEFT JOIN account ON account.account_id = user_account_proxy.proxy_account_id
			WHERE user_account_groups.account_id = :account_id
			AND user_account_groups.group_id = :group_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_user_group_roles_map($account_id, $proxy_id = false) {
        $current_group_values = $this->get_user_account_groups($account_id);
        foreach ($current_group_values as $index => $single_group) {
            $roles_array = array();
            $selected_roles = $this->get_user_group_roles($account_id, $single_group["group_id"]);
            $proxy_users = array();
            foreach ($selected_roles as $single_role) {
                $roles_array[] = $single_role["role_id"];
                if (!empty($proxy_id) && $single_role["role_id"] == $proxy_id) {
                    $proxy_users = $this->get_users_proxies_for_group($account_id, $single_group["group_id"]);
                }
            }
            $current_group_values[$index]["roles"] = $roles_array;
            $current_group_values[$index]["proxy_users"] = $proxy_users;
        }
        return $current_group_values;
    }

    /* if you are assigned a role for a group, that role applies to all of that group's decendants */
    public function has_role($account_id, $roles = array(), $group_id = false) {
        $statement = $this->db->prepare("
        	SELECT ancestor
			FROM group_closure_table
			LEFT JOIN user_account_groups ON user_account_groups.group_id = group_closure_table.ancestor
			WHERE descendant = :group_id
			AND user_account_groups.role_id IN (" . implode(",", $roles) . ")
			AND user_account_groups.account_id = :account_id");
        $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_user_preferences($account_id) {
        $statement = $this->db->prepare("
        	SELECT user_account.send_emails
			FROM user_account
			WHERE user_account.account_id = :account_id");
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function update_preferences($data, $account_id) {
        $statement = $this->db->prepare("
        	UPDATE user_account
        	SET user_account.send_emails = :send_emails
			WHERE user_account.account_id = :account_id");
        $statement->bindValue(":send_emails", !empty($data["send_emails"]) ? $data["send_emails"] : 0, PDO::PARAM_INT);
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function send_mail_preference($user_data) {
        $statement = $this->db->prepare("
        	SELECT user_account.send_emails
			FROM user_account
			WHERE user_account.account_id = :account_id");
        $statement->bindValue(":account_id", $user_data["account_id"], PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if (!empty($data["send_emails"]) && (int)$data["send_emails"] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function get_user_account_info_by_ned_cn($cn = false) {
        $statement = $this->db->prepare("
        	SELECT sn
        		  ,username
        		  ,given_name
        		  ,displayname
        		  ,account_id
        	FROM account
        	WHERE account_type_id = 1
        	AND username = :username");
        $statement->bindValue(":username", $cn, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function get_user_account_emails_by_group_id($group_id = false) {
        $statement = $this->db->prepare("
      SELECT
        account.email
      FROM user_account_groups
      LEFT JOIN account ON account.account_id = user_account_groups.account_id
      WHERE user_account_groups.group_id = :group_id
      AND user_account_groups.role_id = 1
    ");
        $statement->bindValue(":group_id", $group_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_account(int $account_id, array $data = [], array $fields = []): array {
        $field_assignments = SqlUtils::implodeFieldAssignments($fields);

        return $this->db->transactionally(function () use (&$account_id, &$data, &$field_assignments): array {
            $this->db->prepare(<<<SQL
update account
set {$field_assignments}
where account_id = :account_id;
SQL
            )->bindAll($data, self::ACCOUNT_DATA_TYPES)->bind(":account_id", $account_id, \PDO::PARAM_INT)->execute();

            $this->update_user_account($account_id);

            return $data;
        });
    }

    public function insert_account(array $data, array $fields, $accept_policy = false): array {
        if (!isset($fields["displayname"])) {
            $fields["displayname"] = "concat(:sn, ', ', :given_name)";
        }

        if (!isset($fields["username"])) {
            $fields["username"] = "''";
        }

        $field_names = SqlUtils::implodeFieldNames($fields);
        $field_values = SqlUtils::implodeFieldValues($fields);

        return $this->db->transactionally(function () use (&$data, &$field_names, &$field_values, $accept_policy): array {
            $this->db->prepare("
                insert into account
                    ({$field_names})
                values ({$field_values});
            ")->bindAll($data, self::ACCOUNT_DATA_TYPES)->execute();

            $account_id = $this->db->lastInsertId();
            $data["account_id"] = $account_id;

            if ($accept_policy) {
                $this->insert_user_account_accept_policy($account_id);
            } else {
                $this->insert_user_account($account_id);
            }

            return $data;
        });
    }

    public function update_user_account(int $account_id, array $data = [], array $fields = []): array {
        $fields["modified_date"] = "now()";
        $field_assignments = SqlUtils::implodeFieldAssignments($fields);

        $this->db->prepare("
            update user_account
            set {$field_assignments}
            where account_id = :account_id;
        ")->bindAll($data, self::USER_ACCOUNT_DATA_TYPES)
            ->bind(":account_id", $account_id, \PDO::PARAM_INT)
            ->execute();

        return $data;
    }

    public function insert_user_account(int $account_id): int {
        $this->db->prepare("
            insert into user_account
                (account_id,  created_date, modified_date)
            values
                (:account_id, now(),        now())
        ")->bind(":account_id", $account_id, \PDO::PARAM_INT)->execute();

        return $account_id;
    }

    public function insert_user_account_accept_policy(int $account_id): int {
            $this->db->prepare("
                insert into user_account
                    (account_id,  created_date, modified_date, acceptable_use_policy)
                values
                    (:account_id, now(),        now(),          1)
            ")->bind(":account_id", $account_id, \PDO::PARAM_INT)->execute();

            $this->db->prepare("
                insert into user_account_groups
                    (role_id, group_id, account_id)
                values
                    (8,       8,        :account_id)
            ")->bind(":account_id", $account_id, \PDO::PARAM_INT)->execute();

        return $account_id;
    }
}

?>
