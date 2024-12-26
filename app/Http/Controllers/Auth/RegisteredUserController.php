<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Http;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'face_id' => ['required', 'numeric']
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'face_id' => $request->face_id
            ]);

            event(new Registered($user));

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Registration successful',
                    'redirect' => route('login')
                ]);
            }

            return redirect()->route('login');

        } catch (\Exception $e) {
            \Log::error('Registration error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function registerFace(Request $request)
    {
        try {
            if (!$request->hasFile('image')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No image file received'
                ], 400);
            }

            // Generate face_id
            $lastUser = User::whereNotNull('face_id')->orderBy('face_id', 'desc')->first();
            $newFaceId = $lastUser ? ($lastUser->face_id + 1) : 1;

            // Kirim gambar ke API Python
            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')->path()),
                'image.jpg'
            )->post("http://127.0.0.1:5000/register_face", [
                'face_id' => $newFaceId
            ]);

            \Log::info('Face Registration Response:', $response->json());

            if ($response->successful()) {
                // Train model setelah registrasi berhasil
                $trainResponse = Http::post("http://127.0.0.1:5000/train_face");
                
                if (!$trainResponse->successful()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Face registration successful but training failed'
                    ], 500);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Face registered and trained successfully',
                    'face_id' => $newFaceId
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $response->json()['message'] ?? 'Face registration failed'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Face Registration Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error during face registration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            // ... validasi request ...

            // Kirim gambar ke API Python
            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')->path()),
                'image.jpg'
            )->post("http://127.0.0.1:5000/register_face", [
                'face_id' => $newFaceId
            ]);

            if ($response->status() === 409) {  // Duplicate face detected
                return response()->json([
                    'status' => 'error',
                    'message' => 'This face is already registered in our system'
                ], 409);
            }

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $response->json()['error'] ?? 'Face registration failed'
                ], $response->status());
            }

            // ... proses registrasi lainnya ...

        } catch (\Exception $e) {
            \Log::error('Registration error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed'
            ], 500);
        }
    }
}
