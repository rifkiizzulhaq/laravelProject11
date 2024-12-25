<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
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
            $response = Http::post("http://127.0.0.1:5000/recognize_face");
            
            \Log::info('Face Recognition API Response:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $face_id = isset($responseData['face_id']) ? (int)$responseData['face_id'] : null;
                
                \Log::info('Processing face_id:', [
                    'face_id' => $face_id,
                    'type' => gettype($face_id)
                ]);

                if (!$face_id) {
                    return response()->json(['error' => 'No face_id received'], 400);
                }

                // Debug: Tampilkan query yang akan dijalankan
                \Log::info('Searching for user with query:', [
                    'face_id' => $face_id,
                    'sql' => User::where('face_id', $face_id)->toSql()
                ]);

                $user = User::where('face_id', $face_id)->first();
                
                \Log::info('User search result:', [
                    'found' => $user ? true : false,
                    'user' => $user
                ]);

                // Debug: Tampilkan semua user dengan face_id
                $allUsers = User::whereNotNull('face_id')->get(['id', 'name', 'face_id']);
                \Log::info('All users with face_id:', $allUsers->toArray());

                if ($user) {
                    Auth::login($user);
                    return response()->json([
                        'message' => 'Login successful',
                        'user' => $user,
                        'redirect' => route('dashboard')
                    ]);
                }

                return response()->json([
                    'message' => 'Face recognized but no user found',
                    'searched_face_id' => $face_id,
                    'available_face_ids' => $allUsers->pluck('face_id')
                ], 404);
            }

            return response()->json(['error' => 'Face recognition failed'], 500);

        } catch (\Exception $e) {
            \Log::error('Exception during face login:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
