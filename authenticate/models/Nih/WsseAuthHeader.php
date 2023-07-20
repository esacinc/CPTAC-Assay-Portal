<?php
namespace authenticate\models\Nih;

class WsseAuthHeader extends \SoapHeader
{
    private $wss_ns = '';

    public function __construct($user, $pass, $ns = null)
    {
        if ($ns) {
            $this->wss_ns = $ns;
        }

        $auth = new \stdClass();
        $auth->Username = new \SoapVar($user, XSD_STRING, null, $this->wss_ns, null, $this->wss_ns);
        $auth->Password = new \SoapVar($pass, XSD_STRING, null, $this->wss_ns, null, $this->wss_ns);

        $username_token = new \stdClass();
        $username_token->UsernameToken = new \SoapVar($auth, SOAP_ENC_OBJECT, null, $this->wss_ns, 'UsernameToken', $this->wss_ns);

        $security_sv = new \SoapVar(new \SoapVar($username_token, SOAP_ENC_OBJECT, null, $this->wss_ns, 'UsernameToken', $this->wss_ns), SOAP_ENC_OBJECT, null,
            $this->wss_ns, 'Security', $this->wss_ns);
        parent::__construct($this->wss_ns, 'Security', $security_sv, true);
    }
}