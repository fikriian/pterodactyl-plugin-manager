<?php

namespace Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager;

use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Client\BlueprintClientLibrary as BlueprintExtensionLibrary;

class mcpluginmanagerExtensionController extends Controller
{
    public function __construct(private BlueprintExtensionLibrary $blueprint) {}

    public function settings()
    {
        return response()->json([
            'default_platform' => $this->blueprint->dbGet('pluginmanager', 'default_platform'),
            'default_results' => $this->blueprint->dbGet('pluginmanager', 'default_results'),
            'curseforge_api_key' => $this->blueprint->dbGet('pluginmanager', 'curseforge_api_key'),
        ]);
    }
}
