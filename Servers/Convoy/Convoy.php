<?php

namespace App\Extensions\Servers\Convoy;

use App\Classes\Extensions\Server;
use App\Helpers\ExtensionHelper;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class Convoy extends Server
{
    private $hostname;
    private $apikey;

    public function __construct($extension)
    {
        parent::__construct($extension);
        $this->hostname = ExtensionHelper::getConfig('Convoy', 'hostname');
        $this->apikey = ExtensionHelper::getConfig('Convoy', 'api_key');
    }

    public function getConfig()
    {
        return [
            [
                'name' => 'hostname',
                'type' => 'text',
                'friendlyName' => 'Hostname',
                'required' => true,
                'validation' => 'url:http,https'
            ],
            [
                'name' => 'api_key',
                'type' => 'text',
                'friendlyName' => 'API Key',
                'required' => true
            ]
        ];
    }

    public function getProductConfig($options)
    {
        return [
            [
                'name' => 'cpu',
                'type' => 'text',
                'friendlyName' => 'CPU Cores',
                'required' => true
            ],
            [
                'name' => 'ram',
                'type' => 'text',
                'friendlyName' => 'RAM (MB)',
                'required' => true
            ],
            [
                'name' => 'disk',
                'type' => 'text',
                'friendlyName' => 'Disk (GB)',
                'required' => true
            ],
            [
                'name' => 'bandwidth',
                'type' => 'text',
                'friendlyName' => 'Bandwidth (GB)',
                'required' => false
            ],
            [
                'name' => 'snapshot',
                'type' => 'text',
                'friendlyName' => 'Amount of snapshots',
                'required' => true
            ],
            [
                'name' => 'backups',
                'type' => 'text',
                'friendlyName' => 'Amount of backups',
                'required' => true
            ],
            [
                'name' => 'node',
                'type' => 'dropdown',
                'friendlyName' => 'Nodes',
                'required' => true,
                'options' => $this->getNodes()
            ],
            [
                'name' => 'auto_assign_ip',
                'type' => 'boolean',
                'friendlyName' => 'Auto assign IP',
                'required' => false
            ],
        ];
    }

    public function getUserConfig(Product $product)
    {
        $node = $product->settings()->where('name', 'node')->first();
        return [
            [
                'name' => 'hostname',
                'type' => 'text',
                'friendlyName' => 'Hostname',
                'required' => true,
            ],
            [
                'name' => 'os',
                'type' => 'dropdown',
                'friendlyName' => 'Operating System',
                'required' => true,
                'options' => $this->getOS($node->value)
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'friendlyName' => 'Password',
                'required' => true,
                'validation' => 'min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/'
            ],
        ];
    }

    public function createServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        $node = $params['node'];
        $os = $params['config']['os'];
        $hostname = $params['config']['hostname'];
        $password = $params['config']['password'];
        $cpu = $params['cpu'];
        $ram = $params['ram'];
        $disk = $params['disk'];
        $bandwidth = $params['bandwidth'];
        $snapshot = $params['snapshot'];
        $backups = $params['backups'];
        if ($params['auto_assign_ip']) {
            $ip = $this->request('get', 'nodes/' . $node . '/addresses', [
                'filter[server_id]' => null
            ]);
            $ip = $ip['data'][0]['id'];
        } else {
            $ip = null;
        }

        $data = [
            'user_id' => $this->getUser($user, $params['config']['password']),
            'node_id' => $node,
            'hostname' => $hostname,
            'name' => $hostname . ' ' . $user->name,
            'account_password' => $password,
            'limits' => [
                'cpu' => $cpu,
                'memory' => $ram * 1024 * 1024,
                'disk' => $disk * 1024 * 1024 * 1024,
                'bandwidth' => $bandwidth,
                'snapshots' => $snapshot,
                'backups' => $backups,
                'address_ids' => [
                    $ip
                ]
            ],
            'vmid' => null,
            'template_uuid' => $os,
            'should_create_server' => true,
            'start_on_completion' => true,
        ];


        $server = $this->request('post', 'servers', $data);

        ExtensionHelper::setOrderProductConfig('server_uuid', $server['data']['uuid'], $orderProduct->id);

        return $server['data']['id'];
    }

    private function getUser($user, $password)
    {
        $userr = $this->request('get', 'users', [
            'filter[email]' => $user->email
        ]);
        if (count($userr['data']) == 0) {
            $userr = $this->request('post', 'users', [
                'email' => $user->email,
                'password' => $password,
                'password_confirmation' => $password,
                'name' => $user->name,
                'root_admin' => false
            ]);

            return $userr['data']['id'];
        }
        return $userr['data'][0]['id'];
    }

    private function getOS($node)
    {
        $os = $this->request('get', 'nodes/' . $node . '/template-groups');
        $options = [];
        foreach ($os['data'] as $os) {
            foreach ($os['templates'] as $template) {
                $options[] = [
                    'value' => $template[0]['uuid'],
                    'name' => $template[0]['name']
                ];
            }
        }
        return $options;
    }

    private function getNodes()
    {
        $nodes = $this->request('get', 'nodes');
        $options = [];
        foreach ($nodes['data'] as $node) {
            $options[] = [
                'value' => $node['id'],
                'name' => $node['name']
            ];
        }
        return $options;
    }

    private function request($method, $url, $data = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apikey,
        ])->$method($this->hostname . '/api/application/' . $url, $data);

        return $response->json();
    }

    public function getLink($user, $params, $order, $orderProduct): bool|string
    {
        $server = $this->request('post', 'users/' . $this->getUser($user, $params['config']['password']) . '/generate-sso-token');
        return $this->hostname . '/authenticate?token=' . $server['data']['token'];
    }
}
