<?php

use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;

function Minecraft_getConfig()
{
    return [
        [
            'name' => 'apiKey',
            'friendlyName' => 'API Key',
            'type' => 'text',
            'required' => true,
            'description' => 'API Key for the Minecraft server',
        ],
    ];
}

function Minecraft_getProductConfig()
{
    return [
        [
            'name' => 'serverId',
            'friendlyName' => 'Server UUID',
            'type' => 'text',
            'required' => true,
            'description' => 'Server UUID for the Minecraft server<br>Is outputted in the console when starting the server<br>Can be splitted by using a comma',
        ],
        [
            'name' => 'commands',
            'friendlyName' => 'Commands',
            'type' => 'text',
            'required' => true,
            'description' => 'Commands to be executed when the payment is done<br>Can be splitted by using a comma',
        ]
    ];
}

function Minecraft_getUserConfig()
{
    return [
        [
            'name' => 'username',
            'friendlyName' => 'Username',
            'type' => 'text',
            'required' => true,
            'description' => 'Username for the Minecraft server',
        ]
        ];
}

function Minecraft_createServer($user, $params, $order)
{
    $config = $params['config'];
    $apiKey = ExtensionHelper::getConfig('Minecraft', 'apiKey');
    $serverId = $params['serverId'];
    $commands = $params['commands'];
    $username = $config['username'];
    $serverId = explode(',', $serverId);
    $commands = explode(',', $commands);
    foreach($serverId as $server){
        foreach($commands as $command){
            $command = str_replace('{username}', $username, $command);
            $command = str_replace('{money}', $order->amount ? $order->amount : 0, $command);
            Http::get("https://mc.paymenter.org" . "?api_key=" . $apiKey . "&id=" . $server . "&command=" . $command);
        }
    }
}
