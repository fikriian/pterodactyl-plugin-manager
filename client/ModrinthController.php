<?php
namespace Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager;

use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ModrinthController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $version = $request->input('version', '');
            $loader = strtolower($request->input('loader', 'paper'));
            $sort = $request->input('sort', 'relevance');
            $limit = (int) $request->input('size', 48);
            $page = (int) $request->input('page', 1);
            $offset = ($page - 1) * $limit;

            $facets = [
                ['categories:' . $loader],
                ['project_type:mod'] // Modrinth uses mod/plugin for plugins
            ];
            
            if ($version) {
                $facets[] = ['versions:' . $version];
            }

            $response = Http::withHeaders([
                'User-Agent' => 'PterodactylPluginManager/1.0'
            ])->get('https://api.modrinth.com/v2/search', [
                'query' => $query,
                'index' => $sort,
                'limit' => $limit,
                'offset' => $offset,
                'facets' => json_encode($facets)
            ]);

            $data = array_map(function ($hit) {
                return [
                    'id' => (string) $hit['project_id'],
                    'name' => $hit['title'],
                    'author' => $hit['author'],
                    'description' => $hit['description'],
                    'downloads' => $hit['downloads'],
                    'hearts' => $hit['follows'],
                    'icon_url' => $hit['icon_url'],
                    'url' => 'https://modrinth.com/project/' . $hit['slug']
                ];
            }, $response->json('hits', []));

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $response->json('total_hits', 0)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
