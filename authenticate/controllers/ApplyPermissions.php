<?php
namespace authenticate\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class ApplyPermissions {

    private $container;
    private $permission_section;
    private $permission_level;

    public function __construct($container, $permission_section, $permission_level) {
        $this->container = $container;
        $this->permission_section = $permission_section;
        $this->permission_level = $permission_level;
    }

    function __invoke(Request $request, Response $response, $next) {
        global $final_global_template_vars;

        $user_roles = !empty($_SESSION[$final_global_template_vars["session_key"]])
                        && !empty($_SESSION[$final_global_template_vars["session_key"]]["user_role_list"])
                        ?   $_SESSION[$final_global_template_vars["session_key"]]["user_role_list"]
                            : [];
        $has_permission = array_intersect($user_roles, $final_global_template_vars["role_permissions"][$this->permission_section][$this->permission_level]);

        if (empty($has_permission)) {
            return $response->withRedirect($final_global_template_vars["access_denied_url"]);
        }

        return $next($request, $response);
    }

}