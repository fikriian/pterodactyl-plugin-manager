<?php
namespace Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager;

use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SpigotmcController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $sort = $request->input('sort', '-downloads');
            $limit = (int) $request->input('size', 48);
            $page = (int) $request->input('page', 1);

            if (empty($query)) {
                $url = 'https://api.spiget.org/v2/resources';
            } else {
                $url = 'https://api.spiget.org/v2/search/resources/' . urlencode($query);
            }

            $response = Http::withHeaders([
                'User-Agent' => 'PterodactylPluginManager/1.0'
            ])->get($url, [
                'sort' => $sort,
                'size' => $limit,
                'page' => $page
            ]);

            $resData = $response->json();
            $data = array_map(function ($res) {
                $iconUrl = $res['icon']['url'] ?? null;
                if ($iconUrl && !str_starts_with($iconUrl, 'http')) {
                    $iconUrl = 'https://www.spigotmc.org/' . $iconUrl;
                }
                return [
                    'id' => (string) ($res['id'] ?? ''),
                    'name' => $res['name'] ?? 'Unknown',
                    'author' => 'Unknown', // Spigot API v2 returns author id only usually
                    'description' => $res['tag'] ?? '',
                    'downloads' => $res['downloads'] ?? 0,
                    'hearts' => $res['rating']['count'] ?? 0,
                    'icon_url' => $iconUrl,
                    'url' => 'https://www.spigotmc.org/resources/' . ($res['id'] ?? '')
                ];
            }, is_array($resData) ? $resData : []);

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => 1000 // Spiget doesn't cleanly return total in simple queries
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
