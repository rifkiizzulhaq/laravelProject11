<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate face_id baru
        $lastUser = User::whereNotNull('face_id')->orderBy('face_id', 'desc')->first();
        $newFaceId = $lastUser ? ($lastUser->face_id + 1) : 1;

        // 1. Register face
        $registerResponse = Http::post("http://127.0.0.1:5000/register_face", [
            'face_id' => $newFaceId,
        ]);

        \Log::info('Face Registration Response:', [
            'face_id' => $newFaceId,
            'response' => $registerResponse->json()
        ]);

        if (!$registerResponse->successful()) {
            return back()->withErrors(['face_id' => 'Face registration failed']);
        }

        // 2. Train model
        $trainResponse = Http::post("http://127.0.0.1:5000/train_face");

        \Log::info('Face Training Response:', [
            'response' => $trainResponse->json()
        ]);

        if (!$trainResponse->successful()) {
            return back()->withErrors(['face_id' => 'Face training failed']);
        }

        // 3. Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'face_id' => $newFaceId,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Generate a unique face ID for the user.
     */
    protected function generateFaceId(): int
    {
        do {
            $faceId = rand(1000, 9999); // Random 4-digit ID
        } while (User::where('face_id', $faceId)->exists());

        return $faceId;
    }
}
