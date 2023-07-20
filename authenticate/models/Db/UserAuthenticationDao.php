<?php

namespace authenticate\models\Db;

use core\models\Db\AbstractDao;
use core\models\Db\DbResource;
use core\models\Db\SqlUtils;
use PDO;
use user_account\models\AccountTypeEnum;
use user_account\models\UserAccountDao;

class UserAuthenticationDao extends AbstractDao {

    const LOGIN_ATTEMPT_DATA_TYPES = [
        "username" => PDO::PARAM_STR,
        "domain" => PDO::PARAM_STR,
        "page" => PDO::PARAM_STR,
        "created_date" => PDO::PARAM_STR,
        "result" => PDO::PARAM_BOOL,
        "ip_address" => PDO::PARAM_STR,
        "ad_users_library_id" => PDO::PARAM_STR,
        "account_id" => PDO::PARAM_INT
    ];
    private $audit_db;
    private $user_account_dao;

    public function __construct(DbResource $db, DbResource $audit_db, UserAccountDao $user_account_dao = null) {
        parent::__construct($db);

        $this->audit_db = $audit_db;
        $this->user_account_dao = ($user_account_dao ?? new UserAccountDao($db));
    }

    /**
     * @deprecated
     */
    public function map_ned_fields(array $ned_fields = []) {

        $formatted_fields = array();
        //make sure there is an entry for the queried CN
        if (!empty($ned_fields["NEDPerson"])) {
            //check to see if any of the addresses were returned
            if (!empty($ned_fields["NEDPerson"]["Addresses"])) {
                //get the office address
                if (!empty($ned_fields["NEDPerson"]["Addresses"]["Address"])) {
                    foreach ($ned_fields["NEDPerson"]["Addresses"]["Address"] as $mailing_address) {
                        if (!empty($mailing_address["AddressType"]) && $mailing_address["AddressType"] == "Office") {
                            $formatted_fields["country_abbr"] = (!empty($mailing_address["Country"])) ? $mailing_address["Country"] : false;
                            $formatted_fields["location"] = (!empty($mailing_address["Locality"])) ? $mailing_address["Locality"] : false;
                            $formatted_fields["state_abbr"] = (!empty($mailing_address["State"])) ? $mailing_address["State"] : false;
                            $formatted_fields["address"] = (!empty($mailing_address["Street"])) ? $mailing_address["Street"] : false;
                        }
                    }
                }
            }
            //check to see if any of the email addresses were returned
            if (!empty($ned_fields["NEDPerson"]["EmailAddresses"])) {
                if (!empty($ned_fields["NEDPerson"]["EmailAddresses"]["EmailAddress"])) {
                    foreach ($ned_fields["NEDPerson"]["EmailAddresses"]["EmailAddress"] as $email_address) {
                        if (!empty($email_address["EmailAddressType"]) && $email_address["EmailAddressType"] == "Primary") {
                            $formatted_fields["nihprimarysmtp"] = (!empty($email_address["_"])) ? $email_address["_"] : false;
                        } elseif (!empty($email_address["EmailAddressType"]) && $email_address["EmailAddressType"] == "Preferred") {
                            $formatted_fields["email"] = (!empty($email_address["_"])) ? $email_address["_"] : false;
                        }
                    }
                }
            }

            //department
            if (!empty($ned_fields["NEDPerson"]["NIHOrgAcronym"])) {
                $formatted_fields["organization_acronym"] = $ned_fields["NEDPerson"]["NIHOrgAcronym"];
            }

            //CN and Domain
            if (!empty($ned_fields["NEDPerson"]["NIHSSO"])) {
                $formatted_fields["username"] = (!empty($ned_fields["NEDPerson"]["NIHSSO"]["SSOUsername"])) ? $ned_fields["NEDPerson"]["NIHSSO"]["SSOUsername"] : false;
                $formatted_fields["domain"] = (!empty($ned_fields["NEDPerson"]["NIHSSO"]["SSODomain"])) ? $ned_fields["NEDPerson"]["NIHSSO"]["SSODomain"] : false;
            }

            //title
            if (!empty($ned_fields["NEDPerson"]["Title"])) {
                $formatted_fields["title"] = $ned_fields["NEDPerson"]["Title"];
            }

            //Name
            if (!empty($ned_fields["NEDPerson"]["Names"])) {
                if (!empty($ned_fields["NEDPerson"]["Names"]["Name"])) {
                    foreach ($ned_fields["NEDPerson"]["Names"]["Name"] as $single_name) {
                        if (!empty($single_name["NameType"]) && $single_name["NameType"] == "Legal") {
                            $formatted_fields["sn"] = (!empty($single_name["MixCaseSurname"])) ? $single_name["MixCaseSurname"] : false;
                            $formatted_fields["given_name"] = (!empty($single_name["GivenName"])) ? $single_name["GivenName"] : false;
                            $formatted_fields["middle_name"] = (!empty($single_name["MiddleName"])) ? substr($single_name["MiddleName"], 0, 1) : false;
                        } elseif (!empty($single_name["NameType"]) && $single_name["NameType"] == "Professional") {
                            $formatted_fields["personal_title"] = (!empty($single_name["PersonalTitle"])) ? $single_name["PersonalTitle"] : false;
                        }
                    }
                }
            }

            //status
            if (!empty($ned_fields["NEDPerson"]["OrganizationalStatus"])) {
                $formatted_fields["organizationalstat"] = $ned_fields["NEDPerson"]["OrganizationalStatus"];
            }

            //phone
            if (!empty($ned_fields["NEDPerson"]["PhoneNumbers"])) {
                if (!empty($ned_fields["NEDPerson"]["PhoneNumbers"]["PhoneNumber"])) {
                    foreach ($ned_fields["NEDPerson"]["PhoneNumbers"]["PhoneNumber"] as $single_phone) {
                        if (!empty($single_phone["PhoneNumberType"]) && $single_phone["PhoneNumberType"] == "Office") {
                            $formatted_number = $single_phone["_"];
                            $formatted_fields["phone"] = trim($formatted_number);
                        }
                    }
                }
            }

            //employee ID
            if (!empty($ned_fields["NEDPerson"]["Uniqueidentifier"])) {
                $formatted_fields["ned_id"] = $ned_fields["NEDPerson"]["Uniqueidentifier"];
            }

            //derive the displayname
            $org_id = "";
            if (!empty($formatted_fields["organizationalstat"])) {
                $org_id = " [" . substr($formatted_fields["organizationalstat"], 0, 1) . "]";
            }
            $org_array = array_filter(array(
                !empty($formatted_fields["domain"]) ? $formatted_fields["domain"] : false
            , !empty($formatted_fields["organization_acronym"]) ? $formatted_fields["organization_acronym"] : false
            ));
            if (!empty($org_array)) {
                $org_id = " (" . implode("/", $org_array) . ")" . $org_id;
            }
            $formatted_fields["displayname"] = $formatted_fields["sn"] . ", " . $formatted_fields["given_name"] . $org_id;
        }
        return $formatted_fields;
    }

    /**
     * @deprecated
     */
    public function check_local_user_data($cn, $ned_id, $account_type) {

        $statement = $this->db->prepare("
        	SELECT account_id
			FROM account
			WHERE username = :cn
			AND ned_id = :ned_id
			AND account_type_id = :account_type");
        $statement->bindValue(":cn", $cn, PDO::PARAM_STR);
        $statement->bindValue(":ned_id", $ned_id, PDO::PARAM_STR);
        $statement->bindValue(":account_type", $account_type, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @deprecated
     */
    public function check_local_user_data_with_password($username, $account_type) {

        $statement = $this->db->prepare("
        	SELECT account_id,
				   given_name,
				   password
			FROM account
			WHERE (username = :identifier OR email = :identifier)
			AND account_type_id = :account_type"
        )->bindAll([
            ":identifier" => $username,
            ":account_type" => $account_type
        ], UserAccountDao::ACCOUNT_DATA_TYPES
        );

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @deprecated
     */
    public function insert_new_user($user_data, $account_type) {

        $account_table = $this->get_columns("account");
        $values_array = array();
        $columns_array = array();
        $placeholder_array = array();
        foreach ($account_table as $single_column) {
            if (!empty($user_data[$single_column["Field"]])) {
                $columns_array[] = $single_column["Field"];
                $values_array[] = $user_data[$single_column["Field"]];
                $placeholder_array[] = "?";
            }
        }

        //for the account type
        $columns_array[] = "account_type_id";
        $values_array[] = $account_type;
        $placeholder_array[] = "?";

        $statement = $this->db->prepare("
        	INSERT INTO account
        	(" . implode(",", $columns_array) . ")
        	VALUES
        	(" . implode(",", $placeholder_array) . ")");
        $statement->execute($values_array);

        return $this->db->lastInsertId();
    }

    /**
     * Insert new NIH account to account table
     * @param $data
     * @return string
     */
    public function insert_nih_account($data) {
        $account_table = $this->get_columns("account");
        $values_array = array();
        $columns_array = array();
        $placeholder_array = array();
        foreach ($account_table as $single_column) {
            if (!empty($data[$single_column["Field"]])) {
                $columns_array[] = $single_column["Field"];
                $values_array[] = $data[$single_column["Field"]];
                $placeholder_array[] = "?";
            }
        }

        //for the account type
        $columns_array[] = "account_type_id";
        $values_array[] = AccountTypeEnum::NIH_NED;
        $placeholder_array[] = "?";

        return $this->db->transactionally(function () use (&$data, &$columns_array, &$placeholder_array, &$values_array) {
            $statement = $this->db->prepare("
        	INSERT INTO account
        	(" . implode(",", $columns_array) . ")
        	VALUES
        	(" . implode(",", $placeholder_array) . ")");
            $statement->execute($values_array);

            $account_id = $this->db->lastInsertId();

            $this->user_account_dao->insert_user_account_accept_policy($account_id);

            return $account_id;
        });

    }

    /*
     * Insert new local user
     */
    public function insert_new_local_user($email, $password, $password_reset_selector, $password_reset_token) {
        $this->db->prepare("
        	INSERT INTO account
        	    (username, displayname, email, password, account_type_id, password_reset_selector, password_reset_token, password_reset_timestamp)
        	VALUES
        	    (:username, :displayname, :email, :password, :account_type_id, :password_reset_selector, :password_reset_token, now())
        ")->bindAll([
            ":username" => $email,
            ":displayname" => $email,
            ":email" => $email,
            ":password" => $password,
            ":account_type_id" => AccountTypeEnum::LOCAL,
            ":password_reset_selector" => $password_reset_selector,
            ":password_reset_token" => $password_reset_token
        ], UserAccountDao::ACCOUNT_DATA_TYPES
        )->execute();

        return $this->db->lastInsertId();
    }

    /**
     * @deprecated
     */
    public function update_user($user_data, $account_type) {
        //only want to update the fields that are empty in the db, but not empty here
        $account_table = $this->get_columns("account");
        $values_array = array();
        $columns_array = array();
        foreach ($account_table as $single_column) {
            if (!empty($user_data[$single_column["Field"]])) {
                $columns_array[] = $single_column["Field"] . " = ? ";
                $values_array[] = $user_data[$single_column["Field"]];
            }
        }

        $values_array[] = $user_data["username"];
        $values_array[] = $account_type;

        $statement = $this->db->prepare("
        	UPDATE account
        	SET " . implode(",", $columns_array) . "
        	WHERE username = ?
        	AND account_type_id = ?");
        $statement->execute($values_array);
    }

    /**
     * @deprecated
     */
    private function get_columns($table) {
        $statement = $this->db->prepare("
        	SHOW COLUMNS FROM {$table}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_local_user_password_reset_data(int $account_id, string $password_reset_selector, string $password_reset_token): int {
        $this->db->transactionally(function () use (&$account_id, &$password_reset_selector, &$password_reset_token) {
            $this->db->prepare(<<<SQL
update account
set password_reset_selector = :password_reset_selector,
password_reset_token = :password_reset_token,
password_reset_timestamp = now()
where account_id = :account_id;
SQL
            )->bindAll([
                ":account_id" => $account_id,
                ":password_reset_selector" => $password_reset_selector,
                ":password_reset_token" => $password_reset_token
            ], UserAccountDao::ACCOUNT_DATA_TYPES)->execute();

            $this->user_account_dao->update_user_account($account_id);
        });

        return $account_id;
    }

    public function update_local_user_reset_password(int $account_id, string $password): int {
        $this->db->transactionally(function () use (&$account_id, &$password) {
            $this->db->prepare("
                        update account
                        set password = :password,
                        password_reset_selector = null,
                        password_reset_token = null,
                        password_reset_timestamp = null
                        where account_id = :account_id"
            )->bindAll([
                ":account_id" => $account_id,
                ":password" => $password
            ], UserAccountDao::ACCOUNT_DATA_TYPES)->execute();

            $this->user_account_dao->update_user_account($account_id);
        });

        return $account_id;
    }

    //@@@CAP-50 - user account and password recovery updates
    public function update_local_user_registration(int $account_id): int {
        $this->db->transactionally(function () use (&$account_id) {
            $this->db->prepare("
                        update account
                        set 
                            password_reset_token = null
                        where account_id = :account_id"
            )->bindAll([
                ":account_id" => $account_id
            ], UserAccountDao::ACCOUNT_DATA_TYPES)->execute();

            $this->user_account_dao->insert_user_account_accept_policy($account_id);
        });

        return $account_id;
    }

    public function insert_local_user(array $data): array {
        $fields = SqlUtils::extractFields($data);
        $fields["account_type_id"] = AccountTypeEnum::LOCAL;

        return $this->user_account_dao->insert_account($data, $fields);
    }

    /**
     * @param string $password_reset_selector
     * @param int $password_reset_expiration_interval
     * @return array|null
     */
    public function get_local_user_by_registration_data(string $password_reset_selector, int $password_reset_expiration_interval) {
        return $this->db->prepare(<<<SQL
select account.account_id, account.username, account.displayname, account.given_name, account.sn, account.email, 
       account.country_abbr, account.organization, account.title_suffix, account.password_reset_token, user_account.acceptable_use_policy as acceptable_use_policy
from account
left join user_account on user_account.account_id = account.account_id
where account.account_type_id = :account_type_id
and account.password_reset_selector = :password_reset_selector
and account.password_reset_timestamp >= (now() - :password_reset_expiration_interval);
SQL
        )->bindAll([
            ":account_type_id" => AccountTypeEnum::LOCAL,
            ":password_reset_selector" => $password_reset_selector
        ], UserAccountDao::ACCOUNT_DATA_TYPES)->bind(":password_reset_expiration_interval", $password_reset_expiration_interval, \PDO::PARAM_INT)
            ->executeAndFetch();
    }

    /**
     * @param string $password_reset_selector
     * @param int $password_reset_expiration_interval
     * @return array|null
     */
    public function get_local_user_by_password_reset_data(string $password_reset_selector, int $password_reset_expiration_interval) {
        return $this->db->prepare(<<<SQL
select account_id, username, displayname, given_name, sn, email, country_abbr, organization, title_suffix, password_reset_token
from account
where account_type_id = :account_type_id
and password_reset_selector = :password_reset_selector
and password_reset_timestamp >= (now() - :password_reset_expiration_interval);
SQL
        )->bindAll([
            ":account_type_id" => AccountTypeEnum::LOCAL,
            ":password_reset_selector" => $password_reset_selector
        ], UserAccountDao::ACCOUNT_DATA_TYPES)->bind(":password_reset_expiration_interval", $password_reset_expiration_interval, \PDO::PARAM_INT)
            ->executeAndFetch();
    }

    /**
     * @param string $identifier
     * @return array|null
     */
    public function get_local_user(string $identifier) {
        return $this->db->prepare(<<<SQL
select account_id, username, displayname, given_name, sn, email, country_abbr, organization, title_suffix
from account
where account_type_id = :account_type_id
and (username = :identifier or email = :identifier);
SQL
        )->bindAll([
            ":account_type_id" => AccountTypeEnum::LOCAL,
            ":identifier" => $identifier
        ], UserAccountDao::ACCOUNT_DATA_TYPES)->executeAndFetch();
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function get_google_user(string $email) {
        return $this->db->prepare(<<<SQL
select account_id, username, displayname, given_name, sn, email, country_abbr, oauth_id, organization, title_suffix
from account
where account_type_id = :account_type_id
and email = :email;
SQL
        )->bindAll([
            ":account_type_id" => AccountTypeEnum::GOOGLE,
            ":email" => $email
        ], UserAccountDao::ACCOUNT_DATA_TYPES)->executeAndFetch();
    }

    public function insert_google_user(array $data): array {
        $fields = SqlUtils::extractFields($data);
        $fields["account_type_id"] = AccountTypeEnum::GOOGLE;
        $fields["displayname"] = "concat(:sn, ', ', :given_name, ' (GOOGLE) [EXT]')";

        return $this->user_account_dao->insert_account($data, $fields, $accept_policy = true);
    }

    /**
     * @param string $username
     * @param int $ned_id
     * @return array|null
     */
    public function get_nih_ned_user(string $username, int $ned_id) {
        return $this->db->prepare(<<<SQL
select account_id, username, displayname, given_name, sn, email, country_abbr, ned_id, organization, title_suffix
from account
where account_type_id = :account_type_id
and username = :username
and ned_id = :ned_id;
SQL
        )->bindAll([
            ":account_type_id" => AccountTypeEnum::NIH_NED,
            ":username" => $username,
            ":ned_id" => $ned_id
        ], UserAccountDao::ACCOUNT_DATA_TYPES)->executeAndFetch();
    }

    public function insert_nih_ned_user(array $data): array {
        $fields = SqlUtils::extractFields($data);
        $fields["account_type_id"] = AccountTypeEnum::NIH_NED;

        return $this->user_account_dao->insert_account($data, $fields);
    }

    public function log_login_attempt(bool $result, string $username = null, int $account_id = null) {
        $this->audit_db->prepare(<<<SQL
insert into login_attempt
(username, domain, page, created_date, result, ip_address, account_id)
values
(:username, :domain, :page, now(), :result, :ip_address, :account_id);
SQL
        )->bindAll([
            ":username" => $username,
            ":domain" => $_SERVER["HTTP_HOST"],
            ":page" => $_SERVER["REQUEST_URI"],
            ":result" => $result,
            ":ip_address" => $_SERVER["REMOTE_ADDR"],
            ":account_id" => $account_id
        ], self::LOGIN_ATTEMPT_DATA_TYPES)->execute();
    }
}

?>