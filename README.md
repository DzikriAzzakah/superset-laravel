# Laravel Superset Wrapper

A Laravel application that demonstrates how to embed Apache Superset dashboards into Laravel Blade views using the Superset Embedded SDK.

## Files that need attention
Laravel Superset Wrapper
- **app/Http/Controllers/SupersetController.php**
- **config/superset.php**
- **resources/views/welcome.blade.php**
- **resources/views/superset/dashboard.blade.php**
- **routes/web.php**

Superset
- **docker\pythonpath_dev\superset_config.py**

## Features

- **Dashboard Embedding**: Embed Superset dashboards directly into Laravel Blade views
- **Guest Token Authentication**: Secure authentication using Superset guest tokens
- **Dynamic Dashboard Loading**: Load different dashboards by ID
- **Responsive Design**: Modern, responsive UI with Bootstrap 5
- **Error Handling**: Comprehensive error handling and user feedback
- **Configuration Management**: Environment-based configuration

## Quick Start

### 1. Prerequisites

- Laravel application (this project)
- Running Superset instance
- Superset admin credentials

### 2. Configuration

Add the following to your `.env` file:

```env
# Superset Configuration
SUPERSET_DOMAIN=http://localhost:8088
SUPERSET_USERNAME=admin
SUPERSET_PASSWORD=admin

# Superset Guest User Settings
SUPERSET_GUEST_USERNAME=guest_user
SUPERSET_GUEST_FIRST_NAME=Guest
SUPERSET_GUEST_LAST_NAME=User
```

### 3. Configuration for Superset
```superset\docker\pythonpath_dev\superset_config.py```
```python
FEATURE_FLAGS = {
    "ALERT_REPORTS": True,
    "EMBEDDED_SUPERSET": True,
}
GUEST_ROLE_NAME = "Gamma"
CORS_OPTIONS = {
    'supports_credentials': True,
    'allow_headers': ['*'],
    'resources': ['*'],
    'origins': ['*']
}

WTF_CSRF_ENABLED = False
TALISMAN_ENABLED = False
```

### 4. Run Superset with Docker Compose
```
1. cd superset
2. docker compose -f docker-compose-non-dev.yml up
```

### 5. Create a Dashboard
1. Log into your Superset instance
2. Create a new dashboard or use an existing one
3. Note the dashboard ID from the URL (e.g., `/superset/dashboard/1/`)

<!-- ### 6. Create Superset Guest User
1. Go to your dashboard
2. Click on the Settings menu in the top right
3. Select "Manage Roles"
4. Create The Guest User
USERNAME=guest_user
FIRST_NAME=Guest
LAST_NAME=User

### 7. Configure Dashboard Roles Permissions
1. Go to your dashboard
2. Click on the Settings menu in the top right
3. Select "Manage Roles"
4. Add the Guest User to Gamma roles -->

### 6. Test the Embedding
1. Go to your dashboard
2. Click on the "..." menu in the top right
3. You should see an "Embed dashboard" option
4. Click it to get the embedding configuration

### 7. Configure and Run Laravel
```
1. cd laravel_superset_wrapper
2. composer install
3. npm install
4. npm run build
5. composer run dev
```
### 8. Access the Dashboard

Visit: `http://your-laravel-app.com/superset/dashboard`

## Available Routes

- `GET /superset/dashboard` - Main dashboard embedding page
- `POST /superset/guest-token` - API endpoint for guest tokens

## Files Created

### Controllers
- `app/Http/Controllers/SupersetController.php` - Main controller for Superset integration

### Views
- `resources/views/superset/dashboard.blade.php` - Standalone dashboard page

### Configuration
- `config/superset.php` - Superset configuration file

## Usage Examples

### Basic Dashboard Embedding

```php
// In your controller
public function showDashboard()
{
    return view('superset.dashboard', [
        'dashboardId' => '1',
        'dashboardTitle' => 'My Dashboard'
    ]);
}
```

### API Usage

```javascript
// Fetch guest token
const response = await fetch('/superset/guest-token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({ dashboard_id: '1' })
});

const data = await response.json();
```

## Security Features

- **Guest Tokens**: Secure authentication without exposing Superset credentials
- **Error Handling**: Graceful error handling and user feedback

## Customization

### Styling
The dashboard uses Bootstrap 5. Customize by modifying CSS in the Blade templates.

### Configuration
All settings are configurable via environment variables or `config/superset.php`.

## Troubleshooting

### Common Issues

1. **"Failed to login to Superset"**
   - Check Superset credentials in `.env`
   - Ensure Superset is running and accessible

2. **"Failed to create guest token"**
   - Verify dashboard ID exists
   - Check "Gamma" role has dashboard access
   - Ensure embedding feature is enabled

3. **Dashboard not loading**
   - Check browser console for JavaScript errors
   - Verify CORS settings if Superset is on different domain
   - Ensure Superset Embedded SDK is loading

## Support

- **Superset**: [Apache Superset Documentation](https://superset.apache.org/docs/)
- **Laravel**: [Laravel Documentation](https://laravel.com/docs/)
- **Embedded SDK**: [Superset Embedded SDK](https://github.com/apache/superset/tree/master/superset-embedded-sdk)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# -------------------------------------------------------------
