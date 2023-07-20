<?php

namespace authenticate\models\Google;

use ArrayIterator;
use authenticate\models\AbstractUserAuthenticationService;
use authenticate\models\AuthenticationFailedException;
use Firebase\JWT\JWT;
use user_account\models\AccountTypeEnum;

class GoogleUserAuthenticationService extends AbstractUserAuthenticationService {
    const CODE_STATE_TOKEN_SESSION_DATA_ENTRY_KEY = "code_state_token";
    const SESSION_DATA_KEY = "cptac_authenticate_user_google";
    const TOKEN_SESSION_DATA_ENTRY_KEY = "token";
    private $new_user = false;

    public function __construct() {
        parent::__construct(AccountTypeEnum::byValue(AccountTypeEnum::GOOGLE));
    }

    public static function clearSessionDataEntries() {
        unset($_SESSION[self::SESSION_DATA_KEY]);
    }

    public function &buildUserDetails(): array {
        $token = self::getSessionDataEntry(self::TOKEN_SESSION_DATA_ENTRY_KEY);

        $client = $this->buildClient();
        $client->setScopes(\Google_Service_PeopleService::CONTACTS_READONLY);
        $client->setAccessToken(JWT::jsonEncode($token));

        $id_token_body = JWT::jsonDecode(JWT::urlsafeB64Decode(explode(".", $token["id_token"])[1]));

        $user_details = [
            "email" => $id_token_body->email,
            "given_name" => $id_token_body->given_name,
            "oauth_id" => $id_token_body->sub,
            "sn" => $id_token_body->family_name
        ];

        $domain_user = !empty($id_token_body->hd);

        $client_service = new \Google_Service_PeopleService($client);

        $person = $client_service->people->get('people/me',
            array('personFields' => 'names,organizations'));
        $person_orgs = $person->getOrganizations();
        $person_primary_org = null;
        $person_work_org = null;

        if ($person_orgs !== null) {
            foreach (new ArrayIterator($person_orgs) as $person_org) {
                if ($person_org->getPrimary()) {
                    $person_primary_org = $person_org;
                } else if ($person_org->getType() === "work") {
                    $person_work_org = $person_org;
                }
            }

            if ($domain_user && ($person_primary_org !== null)) {
                $user_details["organization"] = $person_primary_org->getName();
            } else if ($person_work_org !== null) {
                $user_details["organization"] = $person_work_org->getName();
            }
        }

        return $user_details;
    }

    public function processCode(string $code, string $state_token = null) {
        try {
            self::validateStateToken($state_token, self::getSessionDataEntry(self::CODE_STATE_TOKEN_SESSION_DATA_ENTRY_KEY));
        } finally {
            self::removeSessionDataEntry(self::CODE_STATE_TOKEN_SESSION_DATA_ENTRY_KEY);
        }

        $client = $this->buildClient();

        self::setSessionDataEntry(self::TOKEN_SESSION_DATA_ENTRY_KEY, $client->fetchAccessTokenWithAuthCode($code));
    }

    public function createAuthorizationUrl($new_user = false): string {
        $client = $this->buildClient($new_user);

        self::generateStateToken($client, self::CODE_STATE_TOKEN_SESSION_DATA_ENTRY_KEY);

        return $client->createAuthUrl();
    }

    public function processToken($new_user = false) {
        $token = self::getSessionDataEntry(self::TOKEN_SESSION_DATA_ENTRY_KEY);

        $client = $this->buildClient($new_user);
        $client->setAccessToken(JWT::jsonEncode($token));

        if ($client->isAccessTokenExpired()) {
            self::setSessionDataEntry(self::TOKEN_SESSION_DATA_ENTRY_KEY, ($token = $client->fetchAccessTokenWithRefreshToken()));
        }
    }

    public function hasToken(): bool {
        return self::hasSessionDataEntry(self::TOKEN_SESSION_DATA_ENTRY_KEY);
    }

    private static function validateStateToken(string $state_token = null, string $session_state_token = null) {
        if ($state_token === null) {
            throw new AuthenticationFailedException("No state token provided.");
        } else if ($session_state_token === null) {
            throw new AuthenticationFailedException("No session state token available.");
        } else if (!hash_equals($state_token, $session_state_token)) {
            throw new AuthenticationFailedException("Provided state and session state tokens do not match.");
        }
    }

    private static function generateStateToken(\Google_Client $client, string $state_session_data_entry_key) {
        $state_token = bin2hex(random_bytes(64));
        $client->setState($state_token);
        self::setSessionDataEntry($state_session_data_entry_key, $state_token);
    }

    private static function removeSessionDataEntry(string $key) {
        if (isset($_SESSION[self::SESSION_DATA_KEY])) {
            unset($_SESSION[self::SESSION_DATA_KEY][$key]);
        }
    }

    private static function hasSessionDataEntry(string $key): bool {
        return (isset($_SESSION[self::SESSION_DATA_KEY]) && isset($_SESSION[self::SESSION_DATA_KEY][$key]));
    }

    private static function getSessionDataEntry(string $key) {
        return (self::hasSessionDataEntry($key) ? $_SESSION[self::SESSION_DATA_KEY][$key] : null);
    }

    private static function setSessionDataEntry(string $key, $value) {
        if (!isset($_SESSION[self::SESSION_DATA_KEY])) {
            $_SESSION[self::SESSION_DATA_KEY] = [];
        }

        $_SESSION[self::SESSION_DATA_KEY][$key] = $value;
    }

    private function buildClient(): \Google_Client {
        global $final_global_template_vars;

        $redirect_uri = "https://{$_SERVER["HTTP_HOST"]}/authenticate/oauth/";

        if ($this->new_user) {
            $redirect_uri = "https://{$_SERVER["HTTP_HOST"]}/authenticate/oauth/register";
        }

        $client = new \Google_Client([
            "application_name" => $final_global_template_vars["site_name"],
            "approval_prompt" => "force",
            "client_id" => $final_global_template_vars["google_client_id"],
            "client_secret" => $final_global_template_vars["google_client_secret"],
            "redirect_uri" => $redirect_uri
        ]);
        $client->setAccessType("offline");
        $client->setScopes(["email", "profile"]);

        return $client;
    }

    public function setNewUser($new_user = false) {
        $this->new_user = $new_user;
    }

}