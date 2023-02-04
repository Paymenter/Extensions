<?php

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
            'friendlyName' => 'Server ID',
            'type' => 'text',
            'required' => true,
            'description' => 'Server ID for the Minecraft server<br>Is outputted in the console when starting the server',
        ],
    ];
}

function Minecraft_createServer($user, $params, $order)
{
    error_log('Minecraft_createServer() called');
}
