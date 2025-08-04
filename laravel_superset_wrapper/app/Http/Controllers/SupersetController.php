<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;


class SupersetController extends Controller
{
    protected $supersetDomain;
    protected $supersetUsername;
    protected $supersetPassword;

    public function __construct()
    {
        $this->supersetDomain = config('superset.domain', 'http://localhost:8088');
        $this->supersetUsername = config('superset.username', 'admin');
        $this->supersetPassword = config('superset.password', 'admin');
    }

    /**
     * Display the dashboard embedding page
     */
    public function showDashboard(Request $request): \Illuminate\View\View
    {
        $dashboardId = $request->get('dashboard_id', '1'); // Default dashboard ID
        $dashboardTitle = $request->get('title', 'Superset Dashboard');

        // Get available dashboards to show in the UI
        try {
            $loginResponse = Http::post($this->supersetDomain . '/api/v1/security/login', [
                'username' => $this->supersetUsername,
                'password' => $this->supersetPassword,
                'provider' => 'db'
            ]);

            if ($loginResponse->successful()) {
                $accessToken = $loginResponse->json('access_token');

                $dashboardsResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken
                ])->get($this->supersetDomain . '/api/v1/dashboard/');

                if ($dashboardsResponse->successful()) {
                    $dashboards = $dashboardsResponse->json('result');
                    // Fetch UUID for each dashboard
                    foreach ($dashboards as &$dashboard) {
                        $embeddedResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $accessToken
                        ])->get($this->supersetDomain . '/api/v1/dashboard/' . $dashboard['id'] . '/embedded');

                        if ($embeddedResponse->successful()) {
                            $embeddedData = $embeddedResponse->json();
                            $dashboard['uuid'] = $embeddedData['result']['uuid'] ?? null;
                        } else {
                            $dashboard['uuid'] = null; // Handle failed UUID fetch
                        }
                    }
                } else {
                    $dashboards = [];
                }
            } else {
                $dashboards = [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching dashboards: ' . $e->getMessage());
            $dashboards = [];
        }
        return view('superset.dashboard', compact('dashboardId', 'dashboardTitle', 'dashboards'));
    }

    /**
     * Fetch guest token from Superset
     */
    public function fetchGuestToken(Request $request): JsonResponse
    {
        try {
            // First, get an access token by logging in
            $loginResponse = Http::post($this->supersetDomain . '/api/v1/security/login', [
                'username' => $this->supersetUsername,
                'password' => $this->supersetPassword,
                'provider' => 'db'
            ]);

            if (!$loginResponse->successful()) {
                throw new \Exception('Failed to login to Superset: ' . $loginResponse->body());
            }

            $accessToken = $loginResponse->json('access_token');

            // Create guest token without CSRF (since we disabled it)
            $guestTokenResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->supersetDomain . '/api/v1/security/guest_token', [
                'user' => [
                    'username' => config('superset.guest_user.username'),
                    'first_name' => config('superset.guest_user.first_name'),
                    'last_name' => config('superset.guest_user.last_name')
                ],
                'resources' => [
                    [
                        'type' => 'dashboard',
                        'id' => $request->get('dashboard_id', config('superset.default_dashboard_id'))
                    ]
                ],
                'rls' => []
            ]);

            if (!$guestTokenResponse->successful()) {
                throw new \Exception('Failed to create guest token: ' . $guestTokenResponse->body());
            }

            return response()->json([
                'success' => true,
                'token' => $guestTokenResponse->json('token')
            ]);

        } catch (\Exception $e) {
            Log::error('Superset guest token error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
