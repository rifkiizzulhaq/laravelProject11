<x-guest-layout>
    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Registration Successful!
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Your account has been created successfully.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="redirectBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Continue to Login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Form -->
    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Face Registration Section -->
        <div class="mt-4">
            <h3 class="font-medium text-gray-900">{{ __('Face Registration') }}</h3>
            <p class="mt-1 text-sm text-gray-500">Please register your face for facial recognition login.</p>
            
            <!-- Camera Preview -->
            <div id="cameraSection" class="mt-2 hidden">
                <video id="video" class="w-full rounded-lg" autoplay playsinline></video>
                <canvas id="canvas" class="hidden"></canvas>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden mt-2">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <span class="ml-2 text-sm text-gray-600">Processing...</span>
                </div>
            </div>

            <!-- Status Message -->
            <div id="faceRegStatus" class="hidden mt-2 text-sm text-green-600"></div>

            <!-- Camera Button -->
            <x-primary-button type="button" id="toggleCameraBtn" class="mt-2">
                {{ __('Open Camera') }}
            </x-primary-button>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4" type="submit">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            const redirectBtn = document.getElementById('redirectBtn');
            const registerForm = document.getElementById('registerForm');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const toggleCameraBtn = document.getElementById('toggleCameraBtn');
            const cameraSection = document.getElementById('cameraSection');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const faceRegStatus = document.getElementById('faceRegStatus');
            let stream = null;
            let registeredFaceId = null;

            // Handle camera toggle
            if (toggleCameraBtn) {
                toggleCameraBtn.addEventListener('click', async function() {
                    if (cameraSection.classList.contains('hidden')) {
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({ video: true });
                            video.srcObject = stream;
                            cameraSection.classList.remove('hidden');
                            this.textContent = 'Capture Face';
                        } catch (err) {
                            alert('Error accessing camera: ' + err.message);
                        }
                    } else {
                        try {
                            loadingIndicator.classList.remove('hidden');
                            this.disabled = true;

                            // Capture image
                            const context = canvas.getContext('2d');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);

                            // Convert to blob
                            const blob = await new Promise(resolve => canvas.toBlob(resolve));
                            const formData = new FormData();
                            formData.append('image', blob);

                            // Register face
                            const response = await fetch('/register/face', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const result = await response.json();

                            if (result.status === 'success') {
                                registeredFaceId = result.face_id;
                                faceRegStatus.textContent = 'Face registered successfully!';
                                faceRegStatus.classList.remove('hidden');
                                
                                // Stop camera and hide preview
                                if (stream) {
                                    stream.getTracks().forEach(track => track.stop());
                                }
                                cameraSection.classList.add('hidden');
                                this.textContent = 'Face Registered ✓';
                                this.disabled = true;
                            } else {
                                throw new Error(result.message || 'Face registration failed');
                            }
                        } catch (error) {
                            alert('Error registering face: ' + error.message);
                            faceRegStatus.textContent = 'Face registration failed. Please try again.';
                            faceRegStatus.classList.remove('hidden');
                        } finally {
                            loadingIndicator.classList.add('hidden');
                            this.disabled = false;
                        }
                    }
                });
            }

            // Handle form submission
            if (registerForm) {
                registerForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    if (!registeredFaceId) {
                        alert('Please register your face first');
                        return;
                    }

                    try {
                        const formData = new FormData(this);
                        formData.append('face_id', registeredFaceId);

                        const response = await fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.status === 'success') {
                            successModal.classList.remove('hidden');
                            redirectBtn.onclick = () => window.location.href = result.redirect;
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 3000);
                        } else {
                            throw new Error(result.message || 'Registration failed');
                        }
                    } catch (error) {
                        alert('Form submission error: ' + error.message);
                    }
                });
            }

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            });
        });
    </script>
</x-guest-layout>
