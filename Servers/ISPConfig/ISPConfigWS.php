<?php

namespace ISPConfigWS;

use SOAPclient;

/**
 * ISPConfig 3 API wrapper PHP. Modfied by Paymenter
 *
 * @author Pablo Medina <pablo.medina@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */
class ISPConfigWS
{
    /**
     * Holds the SOAPclient object.
     *
     * access protected;
     *
     * @var \SOAPclient|null
     */
    protected ?SOAPclient $client = null;

    /**
     * Holds the SOAP session ID.
     *
     * access protected;
     *
     * @var string
     */
    protected string $sessionId;
    /**
     * Holds the ISPConfig login details.
     *
     * access private;
     *
     * @var array
     */
    private array $config;

    /**
     * Holds the SOAP response.
     *
     * access private;
     *
     * @var mixed
     */
    private $wsResponse;

    /**
     * Holds the parameters used for SOAP requests.
     *
     * access private;
     *
     * @var array
     */
    private array $params;

    /**
     * @throws \SoapFault
     */
    public function __construct(array $config = array())
    {
        if (count($config) !== 0) {
            $this->init($config);
        }
    }

    /**
     * @param  array  $config
     *
     * @throws \SoapFault
     */
    public function init(array $config = array())
    {
        if (count($config) !== 0) {
            $this->config = $config;
        }

        $this->client = new SoapClient(
            null,
            array(
                'location' => $this->config['host'] . '/remote/index.php',
                'uri' => $this->config['host'] . '/remote/',
                'trace' => 1,
                'allow_self_siged' => 1,
                'exceptions' => 0,
                'login' => $this->config['user'],
                'password' => $this->config['pass'],
                "stream_context" => stream_context_create(
                    array(
                        'ssl' => array(
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                        )
                    )
                )
            )
        );
        $this->sessionId = $this->client->login($this->config['user'], $this->config['pass']);
    }

    /**
     * Holds the SOAPclient, creating it if needed.
     *
     * @return void
     * @throws \SoapFault
     */
    private function ws(): SOAPclient
    {
        if ($this->client instanceof SoapClient) {
            return $this->client;
        }

        $this->init();
    }

    /**
     * Alias for getResponse.
     *
     * @return string
     */
    public function response(): string
    {
        return $this->getResponse();
    }

    /**
     * Get the API ID.
     *
     * @return string Returns "self"
     */
    public function getResponse(): string
    {
        if (is_soap_fault($this->wsResponse)) {
            return json_encode(
                array('error' => array(
                    'code' => $this->wsResponse->faultcode,
                    'message' => $this->wsResponse->faultstring,
                )),
                JSON_FORCE_OBJECT
            );
        }

        if (!is_array($this->wsResponse)) {
            return json_encode(array('result' => $this->wsResponse), JSON_FORCE_OBJECT);
        }

        return json_encode($this->wsResponse, JSON_FORCE_OBJECT);
    }

    /**
     * Alias for setParams.
     *
     * @param $params
     *
     * @return $this
     */
    public function with($params): ISPConfigWS
    {
        $this->setParams($params);

        return $this;
    }

    /**
     * Set the parameters used for SOAP calls.
     *
     * @param  array  $params
     *
     * @internal param mixed $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addClient(): ISPConfigWS
    {
        $reseller_id = $this->extractParameter('reseller_id');
        $this->wsResponse = $this->ws()->client_add($this->sessionId, $reseller_id, $this->params);

        return $this;
    }

    /**
     * Extracts a parameter from $params and remove it from $params array.
     *
     * @param $param
     *
     * @return mixed
     */
    private function extractParameter($param)
    {
        $parameter = array_key_exists($param, $this->params) ? $this->params[$param] : false;
        unset($this->params[$param]);

        return $parameter;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getClientByCustomerNo(): ISPConfigWS
    {
        $customer_no = $this->extractParameter('customer_no');
        $this->wsResponse = $this->ws()->client_get_by_customer_no($this->sessionId, $customer_no);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addWebDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_web_domain_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteWebDomain(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->sites_web_domain_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateWebDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->sites_web_domain_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     *
     */
    public function logout()
    {
        $this->ws()->logout($this->sessionId);
    }
}
