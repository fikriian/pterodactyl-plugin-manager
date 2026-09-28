<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Core Settings</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label class="control-label">Enabled</label>
                        <div>
                            <select id="enabled" class="form-control">
                                <option value="1" @if($enabled) selected @endif>Enabled</option>
                                <option value="0" @if(!$enabled) selected @endif>Disabled</option>
                            </select>
                            <p class="text-muted"><small>If enabled, MCPluginManager will be enabled.</small></p>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="control-label">Curseforge API Key</label>
                        <div>
                            <input type="hidden" id="oldCurseforgeApiKey" value="{{ $curseforge_api_key }}">
                            <input type="password" id="curseforgeApiKey" class="form-control" placeholder="{{ $curseforge_api_key ? '••••••••' : '' }}" value="">
                            <p class="text-muted"><small>Fill in your Curseforge API Key. Grab one from <a href="https://studios.curseforge.com">Curseforge Studio</a>. Leave blank to keep current, or type <code>!e</code> to clear.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button class="btn btn-sm btn-primary pull-right" id="submit-settings">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Plugin Settings</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label class="control-label">Default Platform</label>
                        <div>
                            <select id="defaultPlatform" class="form-control">
                                <option value="modrinth" @if($default_platform == 'modrinth') selected @endif>Modrinth</option>
                                <option value="curseforge" @if($default_platform == 'curseforge') selected @endif>Curseforge</option>
                                <option value="hangar" @if($default_platform == 'hangar') selected @endif>Hangar</option>
                                <option value="spigotmc" @if($default_platform == 'spigotmc') selected @endif>SpigotMC</option>
                            </select>
                            <p class="text-muted"><small>Default platform in Plugin List widget.</small></p>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="control-label">Default Results</label>
                        <div>
                            <select id="defaultResults" class="form-control">
                                <option value="6" @if($default_results == '6') selected @endif>6</option>
                                <option value="12" @if($default_results == '12') selected @endif>12</option>
                                <option value="18" @if($default_results == '18') selected @endif>18</option>
                                <option value="24" @if($default_results == '24') selected @endif>24</option>
                                <option value="30" @if($default_results == '30') selected @endif>30</option>
                                <option value="36" @if($default_results == '36') selected @endif>36</option>
                                <option value="42" @if($default_results == '42') selected @endif>42</option>
                                <option value="48" @if($default_results == '48') selected @endif>48</option>
                            </select>
                            <p class="text-muted"><small>Default results in Plugin List widget.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button class="btn btn-sm btn-primary pull-right" id="submit-plugin-settings">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
@parent
<script>
     function saveSettings() {
        const enabled = document.getElementById('enabled').value;
        let curseforgeApiKey = document.getElementById('curseforgeApiKey').value;
        const defaultPlatform = document.getElementById('defaultPlatform').value;
        const defaultResults = document.getElementById('defaultResults').value;
        
        if (curseforgeApiKey === "") {
            curseforgeApiKey = document.getElementById('oldCurseforgeApiKey').value;
        } else if (curseforgeApiKey === "!e") {
            curseforgeApiKey = "";
        }

        return $.ajax({
            method: 'PATCH',
            url: '/admin/extensions/mcpluginmanager',
            contentType: 'application/json',
            data: JSON.stringify({
                enabled: enabled == '1',
                curseforge_api_key: curseforgeApiKey,
                default_platform: defaultPlatform,
                default_results: defaultResults
            }),
            headers: { 'X-CSRF-Token': $('input[name="_token"]').val() }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            swal({
                title: 'Error',
                text: 'An error occurred while attempting to save the MCPluginManager settings. ' + errorThrown,
                icon: 'error'
            });
        });
    }
    const submitSettingsButton = document.getElementById('submit-settings');
    submitSettingsButton.addEventListener('click', function () {
        saveSettings().done(function () {
            swal({
                title: 'Success',
                text: 'MCPluginManager settings have been updated successfully.',
                icon: 'success'
            }, function () {
                location.reload();
            });
        });
    });

    function savePluginSettings() {
        const enabled = document.getElementById('enabled').value;
        let curseforgeApiKey = document.getElementById('curseforgeApiKey').value;
        const defaultPlatform = document.getElementById('defaultPlatform').value;
        const defaultResults = document.getElementById('defaultResults').value;

        if (curseforgeApiKey === "") {
            curseforgeApiKey = document.getElementById('oldCurseforgeApiKey').value;
        } else if (curseforgeApiKey === "!e") {
            curseforgeApiKey = "";
        }

        return $.ajax({
            method: 'PATCH',
            url: '/admin/extensions/mcpluginmanager',
            contentType: 'application/json',
            data: JSON.stringify({
                enabled: enabled == '1',
                curseforge_api_key: curseforgeApiKey,
                default_platform: defaultPlatform,
                default_results: defaultResults
            }),
            headers: { 'X-CSRF-Token': $('input[name="_token"]').val() }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            swal({
                title: 'Error',
                text: 'An error occurred while attempting to save the MCPluginManager plugin settings. ' + errorThrown,
                icon: 'error'
            });
        });
    }
    const submitPluginSettingsButton = document.getElementById('submit-plugin-settings');
    submitPluginSettingsButton.addEventListener('click', function () {
        savePluginSettings().done(function () {
            swal({
                title: 'Success',
                text: 'MCPluginManager plugin settings have been updated successfully.',
                icon: 'success'
            }, function () {
                location.reload();
            });
        });
    });
</script>