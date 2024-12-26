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
            if (!$request->hasFile('image')) {
                return response()->json(['error' => 'No image file received'], 400);
            }

            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')->path()),
                'image.jpg'
            )->post('http://127.0.0.1:5000/recognize_face');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['error'])) {
                    return response()->json(['error' => $data['error']], 401);
                }

                if (!isset($data['face_id'])) {
                    return response()->json(['error' => 'Face recognition failed'], 400);
                }

                $face_id = (int)$data['face_id'];
                $user = User::where('face_id', $face_id)->first();

                if ($user) {
                    Auth::login($user);
                    return response()->json([
                        'message' => 'Login successful',
                        'redirect' => '/dashboard'
                    ]);
                }

                return response()->json(['error' => 'Face recognized but no user found'], 404);
            }

            return response()->json([
                'error' => $response->json()['error'] ?? 'Face recognition failed'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Face login error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
