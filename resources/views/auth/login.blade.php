<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Face Recognition Login Section -->
    <div id="faceLoginSection" class="space-y-6">
        <div class="text-center">
            <h2 class="text-lg font-semibold mb-4">Face Recognition Login</h2>
        </div>

        <div id="cameraSection" class="relative">
            <video id="video" class="w-full rounded-lg shadow-lg" autoplay playsinline></video>
            <canvas id="canvas" class="hidden"></canvas>
            <div id="loadingIndicator" class="hidden absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-lg">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            </div>
        </div>

        <div class="space-y-6 mt-6">
            <x-primary-button type="button" id="startFaceLogin" class="w-full justify-center">
                Start Face Login
            </x-primary-button>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>

            <div class="space-y-3">
                <button type="button" id="emailLoginBtn" 
                    class="w-full py-2 px-4 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login with Email
                </button>

                <div class="text-center text-sm text-gray-600">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Register here
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Traditional Login Form -->
    <form method="POST" action="{{ route('login') }}" id="passwordLoginForm" class="hidden" style="display: none;">
        @csrf
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Login with Email</h2>
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ml-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <!-- Divider -->
        <div class="relative mt-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Other Options</span>
            </div>
        </div>

        <!-- Additional Options -->
        <div class="mt-6 space-y-4">
            <!-- Back to Face Login Button - Styling baru -->
            <a href="#" id="backToFaceLogin"
                class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <div class="flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    <span>Login with Face Recognition</span>
                </div>
            </a>

            <!-- Register Link -->
            <div class="text-center text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" 
                    class="font-medium text-indigo-600 hover:text-indigo-500">
                    Register here
                </a>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const startFaceLoginBtn = document.getElementById('startFaceLogin');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const emailLoginBtn = document.getElementById('emailLoginBtn');
        const backToFaceLogin = document.getElementById('backToFaceLogin');
        const faceLoginSection = document.getElementById('faceLoginSection');
        const passwordLoginForm = document.getElementById('passwordLoginForm');
        let stream = null;

        // Face login process
        async function startFaceLogin(e) {
            e.preventDefault();
            try {
                console.log('Starting face login process...');
                loadingIndicator.classList.remove('hidden');
                startFaceLoginBtn.disabled = true;

                // Capture image from video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                const blob = await new Promise(resolve => {
                    canvas.toBlob(resolve, 'image/jpeg');
                });

                const formData = new FormData();
                formData.append('image', blob, 'face.jpg');

                console.log('Sending face data to server...');
                const response = await fetch('/login/face', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                console.log('Server response:', result);

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Login successful!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.location.href = result.redirect || '/dashboard';
                } else {
                    throw new Error(result.error || 'Face login failed');
                }

            } catch (error) {
                console.error('Face login error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: error.message || 'Face login failed. Please try again.',
                    showConfirmButton: true
                });
            } finally {
                loadingIndicator.classList.add('hidden');
                startFaceLoginBtn.disabled = false;
            }
        }

        // Initialize camera
        async function initializeCamera() {
            try {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }

                if (video.srcObject) {
                    video.srcObject = null;
                }

                console.log('Requesting camera access...');
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: "user"
                    } 
                });

                if (video) {
                    video.srcObject = stream;
                    video.onloadedmetadata = async () => {
                        try {
                            await video.play();
                            console.log('Video started playing');
                        } catch (e) {
                            console.error('Error playing video:', e);
                        }
                    };
                    console.log('Camera initialized');
                }
            } catch (err) {
                console.error('Error accessing camera:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Camera Error',
                    text: 'Could not access camera. Please ensure camera access is allowed.'
                });
            }
        }

        // Add event listener for Start Face Login button
        if (startFaceLoginBtn) {
            startFaceLoginBtn.addEventListener('click', startFaceLogin);
            console.log('Start Face Login button listener added');
        } else {
            console.error('Start Face Login button not found');
        }

        // Simplified toggle functions
        window.showFaceLogin = async function() {
            if (faceLoginSection && passwordLoginForm) {
                console.log('Switching to face login');
                
                // First show the section
                faceLoginSection.style.display = 'block';
                passwordLoginForm.style.display = 'none';
                
                // Then initialize camera
                try {
                    await initializeCamera();
                    console.log('Camera initialized after switching to face login');
                } catch (err) {
                    console.error('Error initializing camera:', err);
                }
            } else {
                console.error('Required elements not found');
            }
        };

        window.showEmailLogin = function() {
            if (faceLoginSection && passwordLoginForm) {
                console.log('Switching to email login');
                faceLoginSection.style.display = 'none';
                passwordLoginForm.style.display = 'block';
                
                // Explicitly show the back button
                const backButton = document.getElementById('backToFaceLogin');
                if (backButton) {
                    backButton.style.cssText = `
                        display: flex !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                    `;
                }
                
                // Stop camera
                if (stream) {
                    console.log('Stopping camera stream');
                    stream.getTracks().forEach(track => {
                        track.stop();
                        console.log('Track stopped:', track.kind);
                    });
                    stream = null;
                }
            }
        };

        // Add click handlers with preventDefault
        if (backToFaceLogin) {
            backToFaceLogin.addEventListener('click', async (e) => {
                e.preventDefault();
                await showFaceLogin();
            });
            
            // Force visibility
            backToFaceLogin.style.cssText = `
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            `;
        }

        // Add click handlers
        if (emailLoginBtn) {
            emailLoginBtn.addEventListener('click', showEmailLogin);
        }

        // Initial setup
        await showFaceLogin();

        // Debug logging
        console.log('Initial setup complete:', {
            faceLoginVisible: faceLoginSection?.style.display,
            passwordFormVisible: passwordLoginForm?.style.display,
            backButtonExists: !!backToFaceLogin,
            videoExists: !!video,
            streamActive: stream ? 'active' : 'inactive',
            startButtonExists: !!startFaceLoginBtn
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    });
    </script>

    <style>
    #backToFaceLogin {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        background-color: #4F46E5 !important;
        color: white !important;
        padding: 0.5rem 1rem !important;
        border-radius: 0.375rem !important;
        text-align: center !important;
        width: 100% !important;
        margin-top: 1rem !important;
    }

    #backToFaceLogin:hover {
        background-color: #4338CA !important;
    }
    </style>
</x-guest-layout>
