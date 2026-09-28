import React from 'react';
import { NavLink, Route, Switch, useRouteMatch, Redirect, useLocation, useHistory } from 'react-router-dom';
import PageContentBlock from '@/components/elements/PageContentBlock';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faSearch, faPuzzlePiece, faDownload, faHeart, faExternalLinkAlt, faInfoCircle, faSync, faTrash } from '@fortawesome/free-solid-svg-icons';
import useSWR from 'swr';
import http from '@/api/http';
import { ServerContext } from '@/state/server';

const GLOBAL_VERSIONS = ['1.21.5', '1.21.4', '1.21.3', '1.21.1', '1.20.4', '1.20.1', '1.19.4', '1.18.2', '1.17.1', '1.16.5', '1.12.2', '1.8.8'];
const GLOBAL_LOADERS = ['Paper', 'Purpur', 'Spigot', 'Folia', 'BungeeCord', 'Velocity', 'Waterfall'];

const PLATFORM_SORTS: Record<string, {label: string, value: string}[]> = {
  modrinth: [
    { label: 'Relevance', value: 'relevance' },
    { label: 'Downloads', value: 'downloads' },
    { label: 'Recently Updated', value: 'updated' },
    { label: 'Newest', value: 'newest' }
  ],
  curseforge: [
    { label: 'Featured', value: '1' },
    { label: 'Popularity', value: '2' },
    { label: 'Last Updated', value: '3' },
    { label: 'Total Downloads', value: '6' }
  ],
  hangar: [
    { label: 'Recently Updated', value: 'updated' },
    { label: 'Downloads', value: 'downloads' },
    { label: 'Stars', value: 'stars' },
    { label: 'Newest', value: 'newest' }
  ],
  spigotmc: [
    { label: 'Downloads', value: '-downloads' },
    { label: 'Recently Updated', value: '-updateDate' },
    { label: 'Rating', value: '-rating' }
  ]
};

function useQuery() {
  return new URLSearchParams(useLocation().search);
}

const fetchPluginsDirectly = async (
  platform: string, 
  query: string, 
  version: string, 
  loader: string, 
  sort: string, 
  size: number, 
  page: number,
  uuid: string
) => {
  const params = new URLSearchParams({
    query: query,
    version: version,
    loader: loader,
    sort: sort,
    size: size.toString(),
    page: page.toString()
  });

  const res = await http.get(`/api/client/servers/${uuid}/extensions/mcpluginmanager/search/${platform}?${params.toString()}`);
  return res.data.data;
};

const BrowseTab = ({ defaultPlatform, defaultResults, curseForgeApiKey }: { defaultPlatform: string, defaultResults: string, curseForgeApiKey?: string }) => {
  const query = useQuery();
  const history = useHistory();
  const match = useRouteMatch();
  const uuid = ServerContext.useStoreState(state => state.server.data!.uuid);

  const platform = query.get('platform') || defaultPlatform;
  const page = parseInt(query.get('page') || '1', 10);
  const size = parseInt(query.get('size') || defaultResults, 10);
  const sort = query.get('sort') || PLATFORM_SORTS[platform]?.[0]?.value || '';
  const loader = query.get('loader') || GLOBAL_LOADERS[0];
  const selectedVersion = query.get('version') || GLOBAL_VERSIONS[0];
  const searchQuery = query.get('q') || '';

  const updateQuery = (updates: Record<string, string | number>) => {
    const params = new URLSearchParams(query.toString());
    
    let isPlatformChange = false;
    Object.entries(updates).forEach(([key, value]) => {
      if (key === 'platform' && value !== platform) isPlatformChange = true;
      if (value !== '') {
        params.set(key, String(value));
      } else {
        params.delete(key);
      }
    });

    if (isPlatformChange) {
      params.set('page', '1');
      params.delete('sort'); // Reset sort to default for new platform
    }

    history.push(`${match.url}?${params.toString()}`);
  };

  const { data: plugins, error } = useSWR(
    ['plugins', platform, searchQuery, selectedVersion, loader, sort, size, page, uuid],
    () => fetchPluginsDirectly(platform, searchQuery, selectedVersion, loader, sort, size, page, uuid)
  );

  const isLoading = !plugins && !error;
  
  return (
    <div>
      {/* Filter Bar */}
      <div className="bg-neutral-800 p-4 rounded-md shadow-sm mb-4 border border-neutral-700/50">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
          <div className="flex flex-col">
            <label className="text-xs text-neutral-400 mb-1 uppercase font-semibold">Provider</label>
            <select 
              value={platform} 
              onChange={e => updateQuery({ platform: e.target.value })}
              className="bg-neutral-900 border border-neutral-700/50 text-neutral-200 text-sm rounded px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
              <option value="modrinth">Modrinth</option>
              <option value="curseforge">Curseforge</option>
              <option value="hangar">Hangar</option>
              <option value="spigotmc">SpigotMC</option>
            </select>
          </div>
          <div className="flex flex-col">
            <label className="text-xs text-neutral-400 mb-1 uppercase font-semibold">Size</label>
            <select 
              value={size} 
              onChange={e => updateQuery({ size: e.target.value })}
              className="bg-neutral-900 border border-neutral-700/50 text-neutral-200 text-sm rounded px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
              <option value="12">12</option>
              <option value="24">24</option>
              <option value="48">48</option>
            </select>
          </div>
          <div className="flex flex-col">
            <label className="text-xs text-neutral-400 mb-1 uppercase font-semibold">Sort By</label>
            <select 
              value={sort} 
              onChange={e => updateQuery({ sort: e.target.value })}
              className="bg-neutral-900 border border-neutral-700/50 text-neutral-200 text-sm rounded px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
              {PLATFORM_SORTS[platform]?.map(s => (
                <option key={s.value} value={s.value}>{s.label}</option>
              ))}
            </select>
          </div>
          <div className="flex flex-col">
            <label className="text-xs text-neutral-400 mb-1 uppercase font-semibold">Loader</label>
            <select 
              value={loader} 
              onChange={e => updateQuery({ loader: e.target.value })}
              className="bg-neutral-900 border border-neutral-700/50 text-neutral-200 text-sm rounded px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
              {GLOBAL_LOADERS.map(l => (
                <option key={l} value={l}>{l}</option>
              ))}
            </select>
          </div>
          <div className="flex flex-col">
            <label className="text-xs text-neutral-400 mb-1 uppercase font-semibold">Version</label>
            <select
              value={selectedVersion}
              onChange={(e) => updateQuery({ version: e.target.value })}
              className="bg-neutral-900 border border-neutral-700/50 text-neutral-200 text-sm rounded px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
            >
              {GLOBAL_VERSIONS.map((v) => (
                <option key={v} value={v}>{v}</option>
              ))}
            </select>
          </div>
          <div className="flex flex-col">
            <label className="text-xs text-neutral-400 mb-1 uppercase font-semibold">Search</label>
            <input 
              type="text" 
              value={searchQuery}
              onChange={e => updateQuery({ q: e.target.value })}
              placeholder="Search plugins..." 
              className="bg-neutral-900 border border-neutral-700/50 text-neutral-200 text-sm rounded px-3 py-2 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" 
            />
          </div>
        </div>
      </div>

      {/* Grid */}
      {isLoading ? (
        <div className="flex justify-center items-center py-10">
          <p className="text-neutral-400">Loading plugins...</p>
        </div>
      ) : error ? (
        <div className="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-md">
          Failed to load plugins. Please ensure API keys are configured and the platform is available.
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {plugins.map((plugin: any) => (
            <div key={plugin.id} className="bg-neutral-800/80 rounded-md shadow-sm border border-neutral-700/50 flex flex-col p-4 hover:border-neutral-500 transition-colors">
              <div className="flex items-start mb-4">
                <div className="w-12 h-12 rounded-lg bg-neutral-900 mr-4 flex-shrink-0 flex items-center justify-center overflow-hidden border border-neutral-700/50">
                  {plugin.icon_url ? (
                    <img src={plugin.icon_url} alt={plugin.name} className="w-full h-full object-cover" />
                  ) : (
                    <FontAwesomeIcon icon={faPuzzlePiece} className="text-neutral-500 text-xl" />
                  )}
                </div>
                <div className="min-w-0 flex-1">
                  <h3 className="text-base font-bold text-neutral-100 leading-tight truncate">{plugin.name}</h3>
                  <div className="text-xs text-neutral-400 mt-1 flex items-center space-x-3">
                    <span><FontAwesomeIcon icon={faDownload} className="mr-1"/> {plugin.downloads?.toLocaleString() || 0}</span>
                    <span><FontAwesomeIcon icon={faHeart} className="mr-1"/> {plugin.hearts?.toLocaleString() || 0}</span>
                  </div>
                  <div className="text-xs text-neutral-400 mt-1 truncate">
                    by <span className="text-blue-400 hover:underline cursor-pointer">{plugin.author}</span>
                  </div>
                </div>
              </div>
              <p className="text-sm text-neutral-400 mb-4 flex-grow line-clamp-3">
                {plugin.description || 'No description provided.'}
              </p>
              <div className="flex items-center justify-between mt-auto">
                <a href={plugin.url} target="_blank" rel="noreferrer" className="bg-neutral-700/50 hover:bg-neutral-600 text-neutral-300 p-2 rounded transition-colors focus:outline-none border border-neutral-700/50">
                  <FontAwesomeIcon icon={faExternalLinkAlt} />
                </a>
                <div className="space-x-2">
                  <button className="bg-neutral-700/50 hover:bg-neutral-600 text-neutral-200 px-3 py-2 rounded text-sm transition-colors focus:outline-none border border-neutral-700/50">
                    <FontAwesomeIcon icon={faInfoCircle} className="mr-2" />
                    Details
                  </button>
                  <button className="bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded text-sm transition-colors focus:outline-none shadow-sm">
                    <FontAwesomeIcon icon={faDownload} className="mr-2" />
                    Install
                  </button>
                </div>
              </div>
            </div>
          ))}
          {plugins.length === 0 && (
            <div className="col-span-full text-center py-10 text-neutral-400">
              No plugins found for the current filters.
            </div>
          )}
        </div>
      )}
    </div>
  );
};

const ManageTab = () => {
  return (
    <div>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {/* Manage Placeholder Card */}
        <div className="bg-neutral-800 rounded-md shadow-sm border border-neutral-700 flex flex-col p-4 hover:border-neutral-600 transition-colors">
          <div className="flex items-start mb-4">
            <div className="w-12 h-12 rounded bg-neutral-900 mr-4 flex-shrink-0 flex items-center justify-center">
              <FontAwesomeIcon icon={faPuzzlePiece} className="text-neutral-500 text-xl" />
            </div>
            <div>
              <h3 className="text-base font-bold text-neutral-100 leading-tight">CalcMod</h3>
              <div className="text-xs text-neutral-400 mt-1 flex items-center space-x-2">
                <span className="bg-neutral-700 border border-neutral-600 px-1.5 py-0.5 rounded text-neutral-300">1.4.2</span>
              </div>
            </div>
          </div>
          <p className="text-sm text-neutral-400 mb-4 flex-grow line-clamp-3">
            An installed plugin ready to be updated or removed.
          </p>
          <div className="flex items-center justify-end mt-auto space-x-2">
            <button className="bg-neutral-700 hover:bg-neutral-600 text-neutral-200 px-3 py-2 rounded text-sm transition-colors focus:outline-none">
              <FontAwesomeIcon icon={faSync} className="mr-2" />
              Update
            </button>
            <button className="bg-neutral-700 hover:bg-neutral-600 text-neutral-200 px-3 py-2 rounded text-sm transition-colors focus:outline-none">
              <FontAwesomeIcon icon={faInfoCircle} className="mr-2" />
              Details
            </button>
            <button className="bg-red-600 hover:bg-red-500 text-white px-3 py-2 rounded text-sm transition-colors focus:outline-none shadow-sm">
              <FontAwesomeIcon icon={faTrash} className="mr-2" />
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

const PluginSection = () => {
  const match = useRouteMatch<{ id: string }>();
  const uuid = ServerContext.useStoreState(state => state.server.data!.uuid);

  // Fetch settings from our new API
  const { data: settings } = useSWR(`/api/client/servers/${uuid}/extensions/mcpluginmanager/settings`, () => {
      return http.get(`/api/client/servers/${uuid}/extensions/mcpluginmanager/settings`).then(res => res.data);
  });

  const defaultPlatform = settings?.default_platform || 'modrinth';
  const defaultResults = settings?.default_results || '48';
  const curseForgeApiKey = settings?.curseforge_api_key;

  return (
    <PageContentBlock title={'Plugins'}>
      <div className="flex justify-center mb-6">
        <div className="flex bg-neutral-900 rounded-md overflow-hidden shadow-sm">
          <NavLink 
            to={`${match.url}?platform=${defaultPlatform}&page=1&size=${defaultResults}`}
            className="flex items-center px-6 py-3 text-sm font-medium transition-colors border-b-2 outline-none border-transparent text-neutral-400 hover:text-neutral-200 hover:bg-neutral-800"
            activeClassName="!border-blue-500 !text-blue-500 bg-neutral-900"
            isActive={(_, location) => {
              // Active if the path is NOT manage
              return !location.pathname.endsWith('/manage');
            }}
          >
            <FontAwesomeIcon icon={faSearch} className="mr-2" />
            Browse Plugins
          </NavLink>
          <NavLink 
            to={`${match.url}/manage`}
            className="flex items-center px-6 py-3 text-sm font-medium transition-colors border-b-2 outline-none border-transparent text-neutral-400 hover:text-neutral-200 hover:bg-neutral-800"
            activeClassName="!border-blue-500 !text-blue-500 bg-neutral-900"
          >
            <FontAwesomeIcon icon={faPuzzlePiece} className="mr-2" />
            Manage Plugins
          </NavLink>
        </div>
      </div>

      <div>
        <Switch>
          <Route path={`${match.path}/manage`} exact>
            <ManageTab />
          </Route>
          
          <Route path={`${match.path}`} exact>
            <BrowseTab defaultPlatform={defaultPlatform} defaultResults={defaultResults} curseForgeApiKey={curseForgeApiKey} />
          </Route>
        </Switch>
      </div>
    </PageContentBlock>
  );
};

export default PluginSection;