<?php
namespace Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager;

use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Client\BlueprintClientLibrary as BlueprintExtensionLibrary;

class CurseforgeController extends Controller
{
    public function __construct(private BlueprintExtensionLibrary $blueprint) {}

    public function search(Request $request)
    {
        try {
            $apiKey = $this->blueprint->dbGet('pluginmanager', 'curseforge_api_key');
            if (empty($apiKey)) {
                return response()->json(['error' => 'CurseForge API Key is not configured.'], 400);
            }

            $query = $request->input('query', '');
            $version = $request->input('version', '');
            $sort = $request->input('sort', 1);
            $limit = (int) $request->input('size', 48);
            $page = (int) $request->input('page', 1);
            $offset = ($page - 1) * $limit;

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json'
            ])->get('https://api.curseforge.com/v1/mods/search', [
                'gameId' => 432,
                'classId' => 5, // Bukkit Plugins
                'searchFilter' => $query,
                'sortField' => $sort,
                'sortOrder' => 'desc',
                'pageSize' => $limit,
                'index' => $offset,
                'gameVersion' => $version
            ]);

            $data = array_map(function ($mod) {
                return [
                    'id' => (string) $mod['id'],
                    'name' => $mod['name'],
                    'author' => $mod['authors'][0]['name'] ?? 'Unknown',
                    'description' => $mod['summary'],
                    'downloads' => $mod['downloadCount'],
                    'hearts' => 0,
                    'icon_url' => $mod['logo']['thumbnailUrl'] ?? null,
                    'url' => $mod['links']['websiteUrl'] ?? ''
                ];
            }, $response->json('data', []));

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $response->json('pagination.totalCount', 0)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
