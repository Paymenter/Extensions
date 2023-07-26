<?php

namespace App\Extensions\Servers\ISPConfig;
// ISPConfig API
use App\Helpers\ExtensionHelper;
use App\Models\OrderProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Classes\Extensions\Server;
use App\Extensions\Servers\ISPConfig\ISPConfigWS;

class ISPConfig extends Server
{
    public function getConfig()
    {
        return [
            [
                'name' => 'host',
                'friendlyName' => 'ISPConfig panel url',
                'type' => 'text',
                'required' => true,
                'description' => 'Example: https://panel.example.com:8080/remote/json.php',
            ],
            [
                'name' => 'username',
                'friendlyName' => 'Username',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'password',
                'friendlyName' => 'Password',
                'type' => 'text',
                'required' => true,
            ]
        ];
    }

    public function getProductConfig($options)
    {
        return [
            [
                'name' => 'hd_quota',
                'friendlyName' => 'Disk space MB',
                'type' => 'text',
                'required' => true,
                'description' => 'Disk space in MB',
            ],
            [
                'name' => 'traffic_quota',
                'friendlyName' => 'Traffic MB',
                'type' => 'text',
                'required' => true,
                'description' => 'Traffic in MB',
            ],
            [
                'name' => 'pm_max_requests',
                'friendlyName' => 'Max requests',
                'type' => 'text',
                'required' => true,
                'description' => 'Max requests of the customer website',
            ],
        ];
    }

    public function createServer($user, $params, $order, OrderProduct $product)
    {
        $webService = new ISPConfigWS(
            array(
                'host' => ExtensionHelper::getConfig('ISPConfig', 'host'),
                'user' => ExtensionHelper::getConfig('ISPConfig', 'username'),
                'pass' => ExtensionHelper::getConfig('ISPConfig', 'password'),
            ),
        );

        $client_id = $this->getClient($user, $webService);
        if (!$client_id) {
            return false;
        }
        $result = $webService
            ->with(array(
                'client_id' => $client_id,
                'domain' => $params['config']['domain'],
                'type' => 'vhost',
                'vhost_type' => 'name',
                'ip_address' => '*',
                'active' => 'y',
                'hd_quota' => -1,
                'traffic_quota' => -1,
                'client_group_id' => $client_id + 1,
                'server_id' => 1,
                'http_port' => 80,
                'https_port' => 443,
                'allow_override' => 'All',
                'php' => 'suphp',
                'pm_max_requests' => 500,
                'pm_process_idle_timeout' => 10,
                'added_date' => date('Y-m-d'),
            ))
            ->addWebDomain()
            ->response();

        $result = json_decode($result, true);
        if (isset($result['error'])) {
            return false;
        }
        if (empty($result)) {
            return false;
        }
        if (isset($result['result'])) {
            ExtensionHelper::setOrderProductConfig('domain_id', $result['result'], $product->id);
        }
        return true;
    }

    public function getUserConfig(Product $product)
    {
        return [
            [
                'name' => 'domain',
                'friendlyName' => 'Domain',
                'type' => 'text',
                'required' => true,
                'description' => 'Domain for the webhosting',
            ],
        ];
    }

    public function getClient($user, $webService)
    {
        $reseller_id = 1;
        $result = $webService
            ->with(array('customer_no' => $user->id))
            ->getClientByCustomerNo()
            ->response();
        $result = json_decode($result, true);
        $user->name = str_replace(' ', '', $user->name);
        if (empty($result) || isset($result['error'])) {
            $result = $webService
                ->with(array(
                    'reseller_id' => $reseller_id,
                    'email' => $user->email,
                    'username' => $user->name,
                    'password' => $user->password,
                    'ssh_chroot' => 'no',
                    'contact_name' => $user->name,
                    'web_php_options' => 'no',
                    'customer_no' => $user->id,
                ))
                ->addClient()
                ->response();
            $result = json_decode($result, true);
            if (empty($result)) {
                return false;
            } else {
                $result = $webService
                    ->with(array('customer_no' => $user->id))
                    ->getClientByCustomerNo()
                    ->response();
            }
        }

        if (isset($result['error'])) {
            return false;
        }
        return $result['client_id'];
    }

    public function suspendServer(User $user, $params, Orders $order, OrderProducts $product)
    {
        // deactivate the domain
        $webService = new ISPConfigWS(
            array(
                'host' => ExtensionHelper::getConfig('ISPConfig', 'host'),
                'user' => ExtensionHelper::getConfig('ISPConfig', 'username'),
                'pass' => ExtensionHelper::getConfig('ISPConfig', 'password'),
            ),
        );

        $result = $webService
            ->with(array('customer_no' => $user->id))
            ->getClientByCustomerNo()
            ->response();
        $result = json_decode($result, true);
        if (isset($result['error'])) {
            return false;
        }
        if (empty($result)) {
            return false;
        }
        if (!isset($params['config']['domain_id'])) {
            return false;
        }
        // Get website by domain
        $result = $webService
            ->with(array('domain_id' => $params['config']['domain_id'], 'client_id' => $result['client_id'], 'active' => 'n'))
            ->updateWebDomain()
            ->response();
        $result = json_decode($result, true);
    }

    public function unsuspendServer(User $user, $params, Order $order, OrderProduct $product)
    {
        // deactivate the domain
        $webService = new ISPConfigWS(
            array(
                'host' => ExtensionHelper::getConfig('ISPConfig', 'host'),
                'user' => ExtensionHelper::getConfig('ISPConfig', 'username'),
                'pass' => ExtensionHelper::getConfig('ISPConfig', 'password'),
            ),
        );

        $result = $this->getClient($user, $webService);
        if (!isset($params['config']['domain_id'])) {
            return false;
        }
        // Get website by domain
        $result = $webService
            ->with(array('domain_id' => $params['config']['domain_id'], 'client_id' => $result, 'active' => 'y'))
            ->updateWebDomain()
            ->response();
        $result = json_decode($result, true);
    }

    public function terminateServer($user, $params, $order)
    {

        $webService = new ISPConfigWS(
            array(
                'host' => ExtensionHelper::getConfig('ISPConfig', 'host'),
                'user' => ExtensionHelper::getConfig('ISPConfig', 'username'),
                'pass' => ExtensionHelper::getConfig('ISPConfig', 'password'),
            ),
        );

        $result = $this->getClient($user, $webService);
        if (empty($result)) {
            return false;
        }
        if (!isset($params['config']['domain_id'])) {
            return false;
        }
        // Get website by domain
        $result = $webService
            ->with(array('domain_id' => $params['config']['domain_id'], 'client_id' => $result['client_id']))
            ->deleteWebDomain()
            ->response();
        $result = json_decode($result, true);
    }
}
