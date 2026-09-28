<?php
namespace Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager;

use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HangarController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $sort = $request->input('sort', 'updated');
            $limit = (int) $request->input('size', 48);
            $page = (int) $request->input('page', 1);
            $offset = ($page - 1) * $limit;

            $response = Http::withHeaders([
                'User-Agent' => 'PterodactylPluginManager/1.0'
            ])->get('https://hangar.papermc.io/api/v1/projects', [
                'q' => $query,
                'sort' => $sort,
                'limit' => $limit,
                'offset' => $offset
            ]);

            $data = array_map(function ($proj) {
                return [
                    'id' => $proj['name'],
                    'name' => $proj['name'],
                    'author' => $proj['owner']['name'] ?? 'Unknown',
                    'description' => $proj['description'],
                    'downloads' => $proj['stats']['downloads'] ?? 0,
                    'hearts' => $proj['stats']['stars'] ?? 0,
                    'icon_url' => $proj['avatarUrl'] ?? null,
                    'url' => 'https://hangar.papermc.io/' . ($proj['namespace']['owner'] ?? '') . '/' . ($proj['namespace']['slug'] ?? '')
                ];
            }, $response->json('result', []));

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $response->json('pagination.count', 0)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
