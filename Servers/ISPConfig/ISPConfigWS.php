<?php

namespace ISPConfigWS;

use SOAPclient;

/**
 * ISPConfig 3 API wrapper PHP.
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

    public function getFunctionsList(): ISPConfigWS
    {
        $this->wsResponse = $this->ws()->get_function_list($this->sessionId);

        return $this;
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
    public function changeClientPassword(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $password = $this->extractParameter('password');
        $this->wsResponse = $this->ws()->client_change_password($this->sessionId, $client_id, $password);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteClient(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->client_delete($this->sessionId, $client_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getClient(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->client_get($this->sessionId, $client_id);

        return $this;
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
    public function getClientByUsername(): ISPConfigWS
    {
        $username = $this->extractParameter('username');
        $this->wsResponse = $this->ws()->client_get_by_username($this->sessionId, $username);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     */
    public function getClientID(): ISPConfigWS
    {
        $user_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->client_get_id($this->sessionId, $user_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getClientSites(): ISPConfigWS
    {
        $user_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->client_get_sites_by_user($this->sessionId, $user_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getClientTemplates(): ISPConfigWS
    {
        $user_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->client_templates_get_all($this->sessionId, $user_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateClient(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $resellerId = $this->extractParameter('reseller_id');

        $this->wsResponse = $this->ws()->client_add($this->sessionId, $client_id, $resellerId, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addDnsRecord(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $call = 'dns_' . $this->params['type'] . '_add';

        $this->wsResponse = $this->ws()->{$call}($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteDnsRecord(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $call = 'dns_' . $this->params['type'] . '_delete';
        $this->wsResponse = $this->ws()->{$call}($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDnsRecord(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $call = 'dns_' . $this->params['type'] . '_get';

        $this->wsResponse = $this->ws()->$call($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateDnsRecord(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $call = 'dns_' . $this->params['type'] . '_update';

        $this->wsResponse = $this->ws()->$call($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addDnsZone(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->dns_zone_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteDnsZone(): ISPConfigWS
    {
        $zone_id = $this->extractParameter('zone_id');
        $this->wsResponse = $this->ws()->dns_zone_delete($this->sessionId, $zone_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDnsZone(): ISPConfigWS
    {
        $zone_id = $this->extractParameter('zone_id');
        $this->wsResponse = $this->ws()->dns_zone_get($this->sessionId, $zone_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDnsZonesByUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $server_id = $this->extractParameter('server_id');
        $this->wsResponse = $this->ws()->dns_zone_get_by_user($this->sessionId, $client_id, $server_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function setDnsZoneStatus(): ISPConfigWS
    {
        $zone_id = $this->extractParameter('zone_id');
        $status = $this->extractParameter('status');

        $this->wsResponse = $this->ws()->dns_zone_set_status($this->sessionId, $zone_id, $status);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateDnsZone(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $zone_id = $this->extractParameter('zone_id');
        $this->wsResponse = $this->ws()->dns_zone_update($this->sessionId, $client_id, $zone_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addDnsDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->domains_domain_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteDnsDomain(): ISPConfigWS
    {
        $domain_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->domains_domain_delete($this->sessionId, $domain_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDnsDomain(): ISPConfigWS
    {
        $domain_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->domains_domain_get($this->sessionId, $domain_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getUserDnsDomains(): ISPConfigWS
    {
        $user_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->domains_get_all_by_user($this->sessionId, $user_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailAlias(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_alias_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailAlias(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('alias_id');
        $this->wsResponse = $this->ws()->mail_alias_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailAlias(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('alias_id');
        $this->wsResponse = $this->ws()->mail_alias_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailAlias(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('alias_id');
        $this->wsResponse = $this->ws()->mail_alias_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailBlacklist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_blacklist_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailBlacklist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('blacklist_id');
        $this->wsResponse = $this->ws()->mail_blacklist_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailBlacklist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('blacklist_id');
        $this->wsResponse = $this->ws()->mail_blacklist_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailBlacklist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('blacklist_id');
        $this->wsResponse = $this->ws()->mail_blacklist_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailCatchall(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_catchall_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailCatchall(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('catchall_id');
        $this->wsResponse = $this->ws()->mail_catchall_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailCatchall(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('catchall_id');
        $this->wsResponse = $this->ws()->mail_catchall_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailCatchall(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('catchall_id');
        $this->wsResponse = $this->ws()->mail_catchall_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_domain_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailDomain(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->mail_domain_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailDomain(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->mail_domain_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailDomainByDomain(): ISPConfigWS
    {
        $domain = $this->extractParameter('domain_name');
        $this->wsResponse = $this->ws()->mail_domain_get_by_domain($this->sessionId, $domain);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->mail_domain_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailFetchMail(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_fetchmail_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailFetchmal(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('fetchmail_id');
        $this->wsResponse = $this->ws()->mail_fetchmail_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailFetchmail(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('fetchmail_id');
        $this->wsResponse = $this->ws()->mail_fetchmail_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailFetchmail(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('fetchmail_id');
        $this->wsResponse = $this->ws()->mail_fetchmail_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailForward(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_forward_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailForward(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('forward_id');
        $this->wsResponse = $this->ws()->mail_forward_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailForward(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('forward_id');
        $this->wsResponse = $this->ws()->mail_forward_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailForward(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('forward_id');
        $this->wsResponse = $this->ws()->mail_forward_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailinglist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_mailinglist_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailinglist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('mailinglist_id');
        $this->wsResponse = $this->ws()->mail_mailinglist_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailinglist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('mailinglist_id');
        $this->wsResponse = $this->ws()->mail_mailinglist_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailinglist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('mailinglist_id');
        $this->wsResponse = $this->ws()->mail_mailinglist_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailPolicy(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_policy_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailPolicy(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('policy_id');
        $this->wsResponse = $this->ws()->mail_policy_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailPolicy(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('policy_id');
        $this->wsResponse = $this->ws()->mail_policy_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailPolicy(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('policy_id');
        $this->wsResponse = $this->ws()->mail_policy_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addSpamfilterBlacklist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_blacklist_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteSpamfilterBlacklist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('spamfilterblacklist_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_blacklist_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getSpamfilterBlacklist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('spamfilterblacklist_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_blacklist_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateSpamfilterBlacklist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('spamfilterblacklist_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_blacklist_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addSpamfilterUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_user_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteSpamfilterUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('spamfilteruser_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_user_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getSpamfilterUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('spamfilteruser_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_user_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateSpamfilterUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('spamfilteruser_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_user_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addSpamfilterWhitelist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_whitelist_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteSpamfilterWhitelist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('spamfilterwhitelist_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_whitelist_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getSpamfilterWhitelist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('spamfilterwhitelist_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_whitelist_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateSpamfilterWhitelist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('spamfilterwhitelist_id');
        $this->wsResponse = $this->ws()->mail_spamfilter_whitelist_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailTransport(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_transport_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailTransport(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('transport_id');
        $this->wsResponse = $this->ws()->mail_transport_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailTransport(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('transport_id');
        $this->wsResponse = $this->ws()->mail_transport_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailTransport(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('transport_id');
        $this->wsResponse = $this->ws()->mail_transport_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailuser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_user_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->mail_user_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->mail_user_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('user_id');
        $this->wsResponse = $this->ws()->mail_user_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailUserFilter(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_user_filter_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailUserfilter(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('userfilter_id');
        $this->wsResponse = $this->ws()->mail_user_filter_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailUserFilter(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('userfilter_id');
        $this->wsResponse = $this->ws()->mail_user_filter_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailUserfilter(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('userfilter_id');
        $this->wsResponse = $this->ws()->mail_user_filter_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addMailWhitelist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->mail_whitelist_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteMailWhitelist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('whitelist_id');
        $this->wsResponse = $this->ws()->mail_whitelist_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getMailWhitelist(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('whitelist_id');
        $this->wsResponse = $this->ws()->mail_whitelist_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateMailWhitelist(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('whitelist_id');
        $this->wsResponse = $this->ws()->mail_whitelist_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getServer(): ISPConfigWS
    {
        $server_id = $this->extractParameter('server_id');
        $this->wsResponse = $this->ws()->server_get($this->sessionId, $server_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getServerByIp(): ISPConfigWS
    {
        $ip_address = $this->extractParameter('ip_address');
        $this->wsResponse = $this->ws()->server_get_serverid_by_ip($this->sessionId, $ip_address);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addCron(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_cron_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteCron(): ISPConfigWS
    {
        $cron_id = $this->extractParameter('cron_id');
        $this->wsResponse = $this->ws()->sites_cron_delete($this->sessionId, $cron_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getCron(): ISPConfigWS
    {
        $cron_id = $this->extractParameter('cron_id');
        $this->wsResponse = $this->ws()->sites_cron_get($this->sessionId, $cron_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateCron(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $cron_id = $this->extractParameter('cron_id');
        $this->wsResponse = $this->ws()->sites_cron_update($this->sessionId, $client_id, $cron_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addDatabase(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_database_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteDatabase(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('database_id');
        $this->wsResponse = $this->ws()->sites_database_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDatabase(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('database_id');
        $this->wsResponse = $this->ws()->sites_database_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDatabasesByUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_database_get_all_by_user($this->sessionId, $client_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateDatabase(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('database_id');
        $this->wsResponse = $this->ws()->sites_database_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addDatabaseUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_database_user_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteDatabaseUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('databaseuser_id');
        $this->wsResponse = $this->ws()->sites_database_user_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getDatabaseUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('databaseuser_id');
        $this->wsResponse = $this->ws()->sites_database_user_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateDatabaseUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('databaseuser_id');
        $this->wsResponse = $this->ws()->sites_database_user_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addFtpUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_ftp_user_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteFtpUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('ftpuser_id');
        $this->wsResponse = $this->ws()->sites_ftp_user_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getFtpUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('ftpuser_id');
        $this->wsResponse = $this->ws()->sites_ftp_user_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateFtpUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('ftpuser_id');
        $this->wsResponse = $this->ws()->sites_ftp_user_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addShellUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_shell_user_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteShellUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('shelluser_id');
        $this->wsResponse = $this->ws()->sites_shell_user_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getShellUser(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('shelluser_id');
        $this->wsResponse = $this->ws()->sites_shell_user_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateShellUser(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('shelluser_id');
        $this->wsResponse = $this->ws()->sites_shell_user_update($this->sessionId, $client_id, $primary_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addAliasDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');

        $this->wsResponse = $this->ws()->sites_web_aliasdomain_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteAliasDomain(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('aliasdomain_id');
        $this->wsResponse = $this->ws()->sites_web_aliasdomain_delete($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getAliasDomain(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('aliasdomain_id');
        $this->wsResponse = $this->ws()->sites_web_aliasdomain_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateAliasDomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $primary_id = $this->extractParameter('aliasdomain_id');
        $this->wsResponse = $this->ws()->sites_web_aliasdomain_update($this->sessionId, $client_id, $primary_id, $this->params);

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
    public function getWebDomain(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('domain_id');
        $this->wsResponse = $this->ws()->sites_web_domain_get($this->sessionId, $primary_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function setWebDomainStatus(): ISPConfigWS
    {
        $primary_id = $this->extractParameter('domain_id');
        $status = $this->extractParameter('status');
        $this->wsResponse = $this->ws()->sites_web_domain_set_status($this->sessionId, $primary_id, $status);

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
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function addWebSubdomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $this->wsResponse = $this->ws()->sites_web_subdomain_add($this->sessionId, $client_id, $this->params);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function deleteWebsubdomain(): ISPConfigWS
    {
        $subdomain_id = $this->extractParameter('subdomain_id');
        $this->wsResponse = $this->ws()->sites_web_subdomain_delete($this->sessionId, $subdomain_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function getWebSubdomain(): ISPConfigWS
    {
        $subdomain_id = $this->extractParameter('subdomain_id');
        $this->wsResponse = $this->ws()->sites_web_subdomain_get($this->sessionId, $subdomain_id);

        return $this;
    }

    /**
     * @return $this
     * @throws \SoapFault
     * @throws \SoapFault
     */
    public function updateWebSubdomain(): ISPConfigWS
    {
        $client_id = $this->extractParameter('client_id');
        $subdomain_id = $this->extractParameter('subdomain_id');
        $this->wsResponse = $this->ws()->sites_web_subdomain_update($this->sessionId, $client_id, $subdomain_id, $this->params);

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
