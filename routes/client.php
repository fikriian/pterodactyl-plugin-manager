<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager\mcpluginmanagerExtensionController;
use Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager\ModrinthController;
use Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager\CurseforgeController;
use Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager\HangarController;
use Pterodactyl\Http\Controllers\Client\Extensions\mcpluginmanager\SpigotmcController;

Route::get('/extensions/mcpluginmanager/settings', [mcpluginmanagerExtensionController::class, 'settings']);
Route::get('/extensions/mcpluginmanager/search/modrinth', [ModrinthController::class, 'search']);
Route::get('/extensions/mcpluginmanager/search/curseforge', [CurseforgeController::class, 'search']);
Route::get('/extensions/mcpluginmanager/search/hangar', [HangarController::class, 'search']);
Route::get('/extensions/mcpluginmanager/search/spigotmc', [SpigotmcController::class, 'search']);
