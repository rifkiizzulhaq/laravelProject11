<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'redirect' => '/dashboard'
                ]);
            }

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'The provided credentials are incorrect.'
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Handle login with face recognition.
     */
    public function loginFace(Request $request)
    {
        try {
            Log::info('Face login attempt started');

            if (!$request->hasFile('image')) {
                Log::error('No image provided in request');
                return response()->json(['error' => 'No image provided'], 400);
            }

            // Send image to Python server for face recognition
            try {
                Log::info('Sending request to Python server');
                $response = Http::attach(
                    'image',
                    file_get_contents($request->file('image')),
                    'face.jpg'
                )->post('http://127.0.0.1:5000/recognize_face');

                Log::info('Python server response', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);

                if (!$response->successful()) {
                    return response()->json([
                        'error' => $response->json()['error'] ?? 'Face recognition failed',
                        'details' => 'Python server error'
                    ], 400);
                }

                $result = $response->json();
                
                if (!isset($result['face_id'])) {
                    Log::error('No face_id in response', ['response' => $result]);
                    return response()->json(['error' => 'Invalid response from recognition server'], 400);
                }

                // Find user by face_id
                $user = User::where('face_id', $result['face_id'])->first();
                
                if (!$user) {
                    Log::error('User not found for face_id', ['face_id' => $result['face_id']]);
                    return response()->json(['error' => 'User not found'], 404);
                }

                // Login user
                Auth::login($user);

                return response()->json([
                    'message' => 'Login successful',
                    'redirect' => '/dashboard'
                ]);

            } catch (\Exception $e) {
                Log::error('Python server communication error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => 'Face recognition service unavailable'], 503);
            }

        } catch (\Exception $e) {
            Log::error('Face login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Face login failed'], 500);
        }
    }
}
