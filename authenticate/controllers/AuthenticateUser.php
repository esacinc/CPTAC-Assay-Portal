<?php
namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use authenticate\models\Db\UserAuthenticationDao;
use authenticate\models\Nih\WsseAuthHeader;

use swpg\models\db;
use swpg\models\utility;
use user_account\models\UserAccountDao;
use user_account\models\AccountTypeEnum;
use \GUMP;
use \SoapClient;

class AuthenticateUser {

    private $container;
    private $user_account_dao;
    private $user_auth_dao;

    public function __construct($container) {
        global $final_global_template_vars;

        $this->container = $container;

        $user_account_db = (new db($final_global_template_vars["user_account_db_connection"]))->get_resource();
        $this->user_account_dao = new UserAccountDao($user_account_db, $final_global_template_vars["session_key"]);
        $audit_db = (new db($final_global_template_vars["core_framework_db"]))->get_resource();
        $this->user_auth_dao = new UserAuthenticationDao($user_account_db, $audit_db, $this->user_account_dao);
    }

    function __invoke(Request $request, Response $response, $next) {
        global $final_global_template_vars;

        $gump = new GUMP();
        $rules = array(
            "username" => "alpha_dash"
            ,"password" => "min_len,6"
        );

        $username = $request->getParam('username');
        $password = $request->getParam('password');
        $validated_status = [];

        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $gump_validated = $gump->validate($request->getParams(), $rules);
            //@@CAP-34 - fix user login
            $ab = is_array($gump_validated ) ? is_countable($gump_validated ) : 0 ;

            //if (count($gump_validated) > 0 && $gump_validated !== true)  {
            //@@CAP-34 - fix user login
            if (is_countable($ab) > 0 && $ab !== true)  {
                $this->user_auth_dao->log_login_attempt(false, $request->getParam('username'));
                $request = $request->withAttribute("errors", [
                    "Invalid username or password"
                ]);
                return $next($request, $response);
            }
        }

        $local_user = $this->user_auth_dao->check_local_user_data_with_password($username, AccountTypeEnum::LOCAL);

        //@@CAP-34 - fix user login
        $ab2 = is_array($local_user ) ? count($local_user ) : 0 ;

        //if (count($local_user) == 1 && $local_user[0]['password'] == $password) {
        //@@CAP-34 - fix user login
        if ($ab2 === 1 && $local_user[0]['password'] == $password) {
            foreach ($final_global_template_vars["session_keys"] as $single_key) {
                //for backward compat.
                switch ($single_key) {
                    case "username":
                        $_SESSION[$final_global_template_vars["session_key"]]['cn'] = $username;
                        break;
                    case "given_name":
                        $_SESSION[$final_global_template_vars["session_key"]]['givenname'] = $local_user[0]['given_name'];
                        break;
                }
                //$_SESSION[$final_global_template_vars["session_key"]][$single_key] = $ad_validated[$single_key];
            }
            //$local_data = $user_auth_dao->check_local_user_data($ad_validated["username"],$ad_validated["ned_id"],1);
            $account_id = $local_user[0]['account_id'];
            $_SESSION[$final_global_template_vars["session_key"]]['account_id'] = $account_id;

            $this->valid_user($username, $account_id);
            return $next($request, $response);

        }

        //$this->validate_nih_user($request, $response, $next);

        $validated_status = [
            "Invalid username or password."
        ];

        $this->user_auth_dao->log_login_attempt(false, $request->getParam('username'));
        $request = $request->withAttribute("errors", $validated_status);

        return $next($request, $response);
    }

    private function valid_user($username, $account_id) {
        global $final_global_template_vars;

        $this->user_auth_dao->log_login_attempt(true, $username);
        $this->container['validated'] = true;

        $_SESSION[$final_global_template_vars["session_key"]][$final_global_template_vars["current_user_roles_session_key"]] =
            utility::array_flatten($this->user_account_dao->get_user_roles_list($account_id));

        $user_role_list = $_SESSION[$final_global_template_vars["session_key"]][$final_global_template_vars["current_user_roles_session_key"]];

        $associated_user_groups = [];

        $_SESSION[$final_global_template_vars["session_key"]]["associated_groups"] =
            utility::array_flatten($this->user_account_dao->get_user_account_groups($account_id), $associated_user_groups, "group_id");

        $associated_user_groups = [];

        $_SESSION[$final_global_template_vars["session_key"]]["associated_groups_with_subgroups"] =
            utility::array_flatten($this->user_account_dao->get_user_account_groups_with_subgroups($account_id), $associated_user_groups, "group_id");
    }

    private function validate_nih_user(Request $request, Response $response, $next) {
        global $final_global_template_vars;
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        $validated_status = [];

        $validated_status = array(array("field" => "username", "value" => "", "rule" => ""));
        //check it against AD
        try {
            $wsse_header = new WsseAuthHeader($username, $password, $final_global_template_vars["wss_ns"]);
            $options = array(
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'keep_alive' => true,
                'connection_timeout' => 1800,
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                'location' => $final_global_template_vars["endpoint"]
            );
            $client = new SoapClient($final_global_template_vars["wsdl"], $options);

            $client->__setSoapHeaders(array($wsse_header));
            $web_service_data = [];
            try {
                $web_service_data = $client->ByADaccount(array('Identifier' => $request->getParam('username')));
            } catch (\SoapFault $e) {
                $validated_status = [
                    "Invalid username or password."
                ];

                $this->user_auth_dao->log_login_attempt(false, $request->getParam('username'));
                $request = $request->withAttribute("errors", $validated_status);
                return $next($request, $response);
            }
            if (!empty($web_service_data)) {
                $web_service_data = json_decode(json_encode($web_service_data), true);
                $ad_validated = $this->user_auth_dao->map_ned_fields($web_service_data);
            } else {
                $validated_status = [
                    "Invalid username or password."
                ];

                $this->user_auth_dao->log_login_attempt(false, $request->getParam('username'));
                $request = $request->withAttribute("errors", $validated_status);
                return $next($request, $response);
            }
        } catch (Exception $e) {
            $validated_status = [
                "Invalid username or password."
            ];

            $this->user_auth_dao->log_login_attempt(false, $request->getParam('username'));
            $request = $request->withAttribute("errors", $validated_status);
            return $next($request, $response);
        }
        session_regenerate_id();
        foreach ($final_global_template_vars["session_keys"] as $single_key) {
            //for backward compat.
            switch ($single_key) {
                case "username":
                    $_SESSION[$final_global_template_vars["session_key"]]['cn'] = $ad_validated[$single_key];
                    break;
                case "given_name":
                    $_SESSION[$final_global_template_vars["session_key"]]['givenname'] = $ad_validated[$single_key];
                    break;
                default:
                    $_SESSION[$final_global_template_vars["session_key"]][$single_key] = $ad_validated[$single_key];
                    break;
            }
        }
        $local_data = $this->user_auth_dao->check_local_user_data($ad_validated["username"], $ad_validated["ned_id"], AccountTypeEnum::NIH_NED);
        $account_id = false;

        if (!$local_data) {
            $account_id = $this->user_auth_dao->insert_nih_account($ad_validated, AccountTypeEnum::NIH_NED);
        } else {
            //may want to update account data, but is kinda risky - would rather trust the nvision db
            //$user_auth_dao->update_user($ad_validated,1);
            $account_id = $local_data["account_id"];
        }

        $_SESSION[$final_global_template_vars["session_key"]]["account_id"] = $account_id;

        $this->user_auth_dao->log_login_attempt(true, $username, $account_id);
        $this->valid_user($username, $account_id);
        return $next($request, $response);
    }

}
