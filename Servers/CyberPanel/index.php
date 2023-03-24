<?php

use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;

function CyberPanel_getConfig()
{
    return [
        [
            'name' => 'host',
            'friendlyName' => 'Url to CyberPanel server (with port)',
            'type' => 'text',
            'required' => true,
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

function CyberPanel_getProductConfig()
{
    return [
        [
            'name' => 'packageName',
            'friendlyName' => 'Package Name',
            'type' => 'text',
            'required' => true,
            'description' => 'Package Name for the CyberPanel server',
        ],
    ];
}

function CyberPanel_getUserConfig()
{
    return [
        [
            'name' => 'domain',
            'friendlyName' => 'Domain',
            'type' => 'text',
            'required' => true,
            'description' => 'Domain for the webhost',
        ],
        [
            'name' => 'username',
            'friendlyName' => 'Username',
            'type' => 'text',
            'required' => true,
            'description' => 'Username to login to the website',
        ],
        [
            'name' => 'password',
            'friendlyName' => 'Password',
            'type' => 'text',
            'required' => true,
            'description' => 'Password to login to the website',
        ]
        ];
}

function CyberPanel_createServer($user, $params, $order, $product)
{
    $response = Http::post(ExtensionHelper::getConfig('CyberPanel', 'host') . '/api/createWebsite', [
        'adminUser' => ExtensionHelper::getConfig('CyberPanel', 'username'),
        'adminPass' => ExtensionHelper::getConfig('CyberPanel', 'password'),
        'domainName' => $params['config']['domain'],
        'packageName' => $params['packageName'],
        'ownerEmail' => $user->email,
        'websiteOwner' => $params['config']['username'],
        'ownerPassword' => $params['config']['password'],
    ]);
    if(!$response->successful()){
        ExtensionHelper::error('CyberPanel', 'Failed to create server: ' . $response->body());
    }
}

function CyberPanel_suspendServer($user, $params, $order, $product)
{
    $response = Http::post(ExtensionHelper::getConfig('CyberPanel', 'host') . '/api/suspendWebsite', [
        'adminUser' => ExtensionHelper::getConfig('CyberPanel', 'username'),
        'adminPass' => ExtensionHelper::getConfig('CyberPanel', 'password'),
        'domainName' => $params['config']['domain'],
        'state' => 'Suspend',
    ]);
    if(!$response->successful()){
        ExtensionHelper::error('CyberPanel', 'Failed to suspend server: ' . $response->body());
    }
}

function CyberPanel_unsuspendServer($user, $params, $order, $product)
{
    $response = Http::post(ExtensionHelper::getConfig('CyberPanel', 'host') . '/api/suspendWebsite', [
        'adminUser' => ExtensionHelper::getConfig('CyberPanel', 'username'),
        'adminPass' => ExtensionHelper::getConfig('CyberPanel', 'password'),
        'domainName' => $params['config']['domain'],
        'state' => 'Active',
    ]);
    if(!$response->successful()){
        ExtensionHelper::error('CyberPanel', 'Failed to unsuspend server: ' . $response->body());
    }
}

function CyberPanel_terminateServer($user, $params, $order, $product)
{
    $response = Http::post(ExtensionHelper::getConfig('CyberPanel', 'host') . '/api/deleteWebsite', [
        'adminUser' => ExtensionHelper::getConfig('CyberPanel', 'username'),
        'adminPass' => ExtensionHelper::getConfig('CyberPanel', 'password'),
        'domainName' => $params['config']['domain'],
    ]);
    if(!$response->successful()){
        ExtensionHelper::error('CyberPanel', 'Failed to terminate server: ' . $response->body());
    }
}
