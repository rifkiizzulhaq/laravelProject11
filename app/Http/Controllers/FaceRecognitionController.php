<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FaceRecognitionController extends Controller
{
    protected $pythonBaseUrl = 'http://127.0.0.1:5000'; // URL Flask API

    public function register(Request $request)
    {
        $response = Http::post("{$this->pythonBaseUrl}/register_face", [
            'face_id' => $request->input('face_id'),
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function train()
    {
        $response = Http::post("{$this->pythonBaseUrl}/train_face");

        return response()->json($response->json(), $response->status());
    }

    public function login(Request $request)
    {
        $response = Http::post("{$this->pythonBaseUrl}/recognize_face");

        return response()->json($response->json(), $response->status());
    }

    public function loginWithFace(Request $request)
{
    // Validasi permintaan
    $request->validate([
        'face_id' => 'required|numeric', // Pastikan face_id di-post
    ]);

    $faceId = $request->input('face_id');

    // Cari user berdasarkan face_id
    $user = User::where('face_id', $faceId)->first();

    if ($user) {
        // Login user
        Auth::login($user);

        return redirect()->intended('dashboard')->with('status', 'Login successful using face recognition.');
    }

    return back()->withErrors(['face_id' => 'Face not recognized or not registered.']);
}
}
