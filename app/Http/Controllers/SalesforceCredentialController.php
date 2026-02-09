<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesforceCredential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SalesforceCredentialController extends Controller
{
    public function index()
    {
        $credential = SalesforceCredential::first();
        return view('salesforce-credentials.index', compact('credential'));
    }

    public function create()
    {
        return view('salesforce-credentials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'username' => 'required|email',
            'instance_url' => 'required|url',
        ]);
    
        SalesforceCredential::create([
            'user_id' => auth()->id() ?? 1,
            'client_id' => $request->client_id,
            'username' => $request->username, // <-- this is what’s missing
            'client_secret_encrypted' => Crypt::encryptString($request->client_secret ?? 'dummy'),
            'instance_url' => $request->instance_url,
            'redirect_uri' => $request->redirect_uri,
            'auth_method' => $request->auth_method ?? 'jwt',
        ]);

        return redirect()->route('salesforce-credentials.index')->with('success', 'Saved');
    }

    public function edit(SalesforceCredential $salesforceCredential)
    {
        return view('salesforce-credentials.edit', [
            'credential' => $salesforceCredential,
        ]);
    }

    public function update(Request $request, SalesforceCredential $salesforceCredential)
    {
        $request->validate([
            'client_id' => 'required',
            'client_secret' => 'required',
            'instance_url' => 'required|url',
            'redirect_uri' => 'nullable|url',
            'auth_method' => 'nullable|string',
        ]);
        
        $salesforceCredential->update([
            'client_id' => $request->client_id,
            'client_secret_encrypted' => Crypt::encryptString($request->client_secret),
            'instance_url' => $request->instance_url,
            'redirect_uri' => $request->redirect_uri,
            'auth_method' => $request->auth_method ?? 'client_credentials',
        ]);

        return redirect()->route('salesforce-credentials.index')->with('success', 'Salesforce credentials updated.');
    }

    public function testConnection(Request $request)
    {
        try {
            $request->validate([
                'client_id' => 'required',
                'username' => 'required|email',
                'instance_url' => 'required|url',
            ]);

            $privateKey = Storage::get('oauth/private.key');
            $issuedAt = time();

            $jwt = JWT::encode([
                'iss' => $request->client_id,
                'sub' => $request->username,
                'aud' => rtrim(trim($request->instance_url), '/'),
                'exp' => $issuedAt + 300,
            ], $privateKey, 'RS256');

            $tokenUrl = rtrim(trim($request->instance_url), '/') . '/services/oauth2/token';

            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful!',
                    'access_token' => $response['access_token'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed (' . $response->status() . '): ' . $response->json('error_description', $response->body())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }

}