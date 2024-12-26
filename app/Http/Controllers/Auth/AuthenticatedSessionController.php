<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
    public function loginWithFace(Request $request): JsonResponse
    {
        try {
            Log::info('Face login attempt started');

            if (!$request->hasFile('image')) {
                Log::error('No image file received');
                return response()->json(['error' => 'No image file received'], 400);
            }

            // Get the image file
            $image = $request->file('image');
            
            // Log the image details for debugging
            Log::info('Image received', [
                'size' => $image->getSize(),
                'mime' => $image->getMimeType()
            ]);

            try {
                // Make request to Python face recognition server
                $response = Http::attach(
                    'image',
                    file_get_contents($image->path()),
                    'face.jpg'
                )->post('http://127.0.0.1:5000/recognize_face');

                Log::info('Python server response', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['error'])) {
                        Log::error('Face recognition error', ['error' => $data['error']]);
                        return response()->json(['error' => $data['error']], 400);
                    }

                    if (!isset($data['face_id'])) {
                        Log::error('No face_id in response');
                        return response()->json(['error' => 'Face recognition failed'], 400);
                    }

                    $face_id = (int)$data['face_id'];
                    $user = User::where('face_id', $face_id)->first();

                    if ($user) {
                        Auth::login($user);
                        Log::info('User logged in successfully', ['user_id' => $user->id]);
                        return response()->json([
                            'message' => 'Login successful',
                            'redirect' => '/dashboard'
                        ]);
                    }

                    Log::error('User not found for face_id', ['face_id' => $face_id]);
                    return response()->json(['error' => 'User not found'], 404);
                }

                Log::error('Python server error', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return response()->json([
                    'error' => $response->json()['error'] ?? 'Face recognition failed'
                ], $response->status());

            } catch (\Exception $e) {
                Log::error('Python server connection error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Could not connect to face recognition service'
                ], 500);
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
