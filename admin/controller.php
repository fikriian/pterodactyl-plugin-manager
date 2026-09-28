<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\mcpluginmanager;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Support\Facades\Crypt;

// https://blueprint.zip/docs/?page=documentation/$blueprint
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;

class mcpluginmanagerExtensionController extends Controller
{
    public function __construct(private ViewFactory $view, private BlueprintExtensionLibrary $blueprint) {}

    public function index(): View
    {
        $enabled = $this->blueprint->dbGet('pluginmanager', 'enabled');
        $curseforgeApiKey = $this->blueprint->dbGet('pluginmanager', 'curseforge_api_key');
        $defaultPlatform = $this->blueprint->dbGet('pluginmanager', 'default_platform');
        $defaultResults = $this->blueprint->dbGet('pluginmanager', 'default_results');

        return $this->view->make('admin.extensions.mcpluginmanager.index', [
            'enabled' => $enabled,
            'curseforge_api_key' => $curseforgeApiKey,
            'default_platform' => $defaultPlatform,
            'default_results' => $defaultResults,

            'root' => '/admin/extensions/mcpluginmanager',
            'blueprint' => $this->blueprint
        ]);
    }

    public function update(mcpluginmanagerSettingsFormRequest $request): Response
    {
        $this->blueprint->dbSet('pluginmanager', 'enabled', $request->normalize()['enabled']);
        $this->blueprint->dbSet('pluginmanager', 'curseforge_api_key', $request->normalize()['curseforge_api_key'] ?? "");
        $this->blueprint->dbSet('pluginmanager', 'default_platform', $request->normalize()['default_platform'] ?? "");
        $this->blueprint->dbSet('pluginmanager', 'default_results', $request->normalize()['default_results'] ?? "");

        return response('', 204);
    }
}

class mcpluginmanagerSettingsFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'enabled' => 'required|boolean',
            'curseforge_api_key' => 'nullable|string',
            'default_platform' => 'required|in:modrinth,curseforge,hangar,spigotmc',
            'default_results' => 'required|in:6,12,18,24,30,36,42,48'
        ];
    }
}