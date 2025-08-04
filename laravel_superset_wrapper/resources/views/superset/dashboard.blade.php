<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dashboardTitle }} - Laravel Superset</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .dashboard-container {
            min-height: 600px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative; /* Ensure child elements can use 100% height */
        }

        #dashboardContainer {
            width: 100%;
            height: 100%;
            position: absolute; /* Fill the parent container */
            top: 0;
            left: 0;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none; /* Remove default iframe border */
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .dashboard-content {
            padding: 20px;
            background-color: #f8f9fa;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .dashboard-controls {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>{{ $dashboardTitle }}</h1>
            <p class="mb-0">Embedded Superset Dashboard</p>
        </div>

        <div class="dashboard-content">
            <!-- Dashboard Controls -->
            <div class="dashboard-controls">
                <div class="row">
                    <div class="col-md-4">
                        @php
                            if (!isset($dashboardId) && count($dashboards) > 0) {
                                $dashboardId = $dashboards[0]['uuid'];
                            }
                        @endphp
                        <label for="dashboardId" class="form-label">Select Dashboard:</label>
                        <select class="form-select" id="dashboardSelect" onchange="updateDashboardId()">
                            @if(count($dashboards) > 0)
                                @foreach($dashboards as $dashboard)
                                @if($dashboard['uuid'])
                                    <option value="{{ $dashboard['id'] }}|{{ $dashboard['uuid'] }}" {{ $dashboard['id'] == $dashboardId ? 'selected' : '' }}>
                                        {{ $dashboard['dashboard_title'] }} (ID: {{ $dashboard['id'] }})
                                    </option>
                                @endif
                                @endforeach
                            @else
                                <option value="{{ $dashboardId }}">Dashboard {{ $dashboardId }}</option>
                            @endif
                        </select>
                        <input type="hidden" id="dashboardId" value="{{ $dashboardId }}">
                        <input type="hidden" id="dashboardUuid" value="{{ $dashboards[0]['uuid'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary d-block" onclick="loadDashboard()">
                            <i class="bi bi-arrow-clockwise"></i> Load Dashboard
                        </button>
                    </div>
                </div>

                @if(count($dashboards) > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="text-muted">
                                Available Dashboards: {{ count($dashboards) }} found
                            </small>
                        </div>
                    </div>
                @else
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> No dashboards found. Please create a dashboard in Superset first.
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Status Messages -->
            <div id="statusMessage"></div>

            <!-- Dashboard Container -->
            <div class="dashboard-container">
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-3">Loading Superset Dashboard...</span>
                </div>

                <div id="dashboardContainer" style="display: none;">
                    <!-- Superset dashboard will be embedded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Superset Embedded SDK -->
    <script src="https://unpkg.com/@superset-ui/embedded-sdk"></script>

    <script>
        // Configuration
        const SUPERSET_DOMAIN = '{{ config("superset.domain") }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // Dashboard embedding function
        async function embedSupersetDashboard(dashboardId) {
            try {
                showStatus('Loading dashboard...', 'info');

                // Fetch guest token from Laravel backend
                const tokenResponse = await fetch('/superset/guest-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ dashboard_id: dashboardId })
                });

                const tokenData = await tokenResponse.json();

                if (!tokenData.success) {
                    throw new Error(tokenData.error || 'Failed to get guest token');
                }

                // Hide loading spinner
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('dashboardContainer').style.display = 'block';

                // Embed the dashboard
                await supersetEmbeddedSdk.embedDashboard({
                    id: dashboardId,
                    supersetDomain: SUPERSET_DOMAIN,
                    mountPoint: document.getElementById('dashboardContainer'),
                    fetchGuestToken: () => Promise.resolve(tokenData.token),
                    dashboardUiConfig: {
                        hideTitle: {{ config('superset.embed_settings.hide_title') ? 'true' : 'false' }},
                        hideTab: {{ config('superset.embed_settings.hide_tab') ? 'true' : 'false' }},
                        hideChartControls: {{ config('superset.embed_settings.hide_chart_controls') ? 'true' : 'false' }},
                        filters: {
                            expanded: {{ config('superset.embed_settings.filters_expanded') ? 'true' : 'false' }},
                            visible: {{ config('superset.embed_settings.filters_visible') ? 'true' : 'false' }}
                        }
                    },
                    debug: false
                });

                showStatus('Dashboard loaded successfully!', 'success');

            } catch (error) {
                console.error('Error embedding dashboard:', error);
                showStatus('Error loading dashboard: ' + error.message, 'error');

                // Show loading spinner again on error
                document.getElementById('loadingSpinner').style.display = 'flex';
                document.getElementById('dashboardContainer').style.display = 'none';
            }
        }

        // Update dashboard ID when dropdown changes
        function updateDashboardId() {
            const dashboardSelect = document.getElementById('dashboardSelect');
            const selectedValue = dashboardSelect.value.split('|'); // Split the value into ID and UUID
            const dashboardIdInput = document.getElementById('dashboardId');
            const dashboardUuidInput = document.getElementById('dashboardUuid');

            dashboardIdInput.value = selectedValue[0]; // Set the ID
            dashboardUuidInput.value = selectedValue[1]; // Set the UUID
        }

        // Load dashboard function
        function loadDashboard() {
            const dashboardId = document.getElementById('dashboardId')?.value;
            const dashboardUuid = document.getElementById('dashboardUuid')?.value;

            if (!dashboardUuid) {
                showStatus('Please select a valid dashboard', 'error');
                return;
            }

            // Clear previous dashboard
            document.getElementById('dashboardContainer').innerHTML = '';
            document.getElementById('loadingSpinner').style.display = 'flex';
            document.getElementById('dashboardContainer').style.display = 'none';

            // Load new dashboard using UUID
            embedSupersetDashboard(dashboardUuid);
        }

        // Show status messages
        function showStatus(message, type) {
            const statusDiv = document.getElementById('statusMessage');
            const alertClass = type === 'error' ? 'error-message' :
                             type === 'success' ? 'success-message' :
                             'alert alert-info';

            statusDiv.innerHTML = `<div class="${alertClass}">${message}</div>`;

            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    statusDiv.innerHTML = '';
                }, 5000);
            }
        }

        // Load dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
        });
    </script>
</body>
</html>
