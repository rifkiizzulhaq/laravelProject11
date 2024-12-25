<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
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
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Face Registration Section -->
        <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Face Registration') }}</h3>
            
            <!-- Camera Preview (Hidden by default) -->
            <div id="cameraSection" class="mt-2 hidden">
                <div class="relative">
                    <video id="video" class="w-full rounded-lg shadow-lg" autoplay></video>
                    <canvas id="canvas" class="hidden"></canvas>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="hidden mt-2 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-t-blue-500"></div>
                    <p class="mt-2 text-gray-600">Registering face...</p>
                </div>
            </div>

            <!-- Camera Button -->
            <button type="button" 
                    id="toggleCameraBtn"
                    class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Open Camera') }}
            </button>

            <!-- Face Registration Status -->
            <div id="faceRegStatus" class="mt-2 text-sm text-gray-600 hidden">
                Face registered successfully!
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        // Tunggu hingga DOM sepenuhnya dimuat
        window.addEventListener('load', function() {
            // Inisialisasi variabel
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const toggleCameraBtn = document.getElementById('toggleCameraBtn');
            const cameraSection = document.getElementById('cameraSection');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const faceRegStatus = document.getElementById('faceRegStatus');
            const registerForm = document.querySelector('form');
            let stream = null;
            let registeredFaceId = null;

            // Fungsi untuk menghentikan kamera
            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
            }

            // Fungsi untuk registrasi wajah
            async function registerFace(imageBlob) {
                const formData = new FormData();
                formData.append('image', imageBlob);

                const response = await fetch('/register/face', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                return await response.json();
            }

            // Event handler untuk tombol kamera
            if (toggleCameraBtn) {
                toggleCameraBtn.onclick = async function() {
                    if (cameraSection.classList.contains('hidden')) {
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({ video: true });
                            video.srcObject = stream;
                            cameraSection.classList.remove('hidden');
                            this.textContent = 'Capture Face';
                        } catch (err) {
                            alert('Camera error: ' + err.message);
                        }
                    } else {
                        try {
                            loadingIndicator.classList.remove('hidden');
                            this.disabled = true;

                            const context = canvas.getContext('2d');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);

                            const blob = await new Promise(resolve => canvas.toBlob(resolve));
                            const result = await registerFace(blob);

                            if (result.status === 'success') {
                                registeredFaceId = result.face_id;
                                faceRegStatus.textContent = 'Face registered successfully!';
                                faceRegStatus.classList.remove('hidden');
                                stopCamera();
                                cameraSection.classList.add('hidden');
                                this.textContent = 'Face Registered ✓';
                                this.disabled = true;
                            } else {
                                throw new Error(result.message);
                            }
                        } catch (error) {
                            alert('Registration error: ' + error.message);
                        } finally {
                            loadingIndicator.classList.add('hidden');
                            this.disabled = false;
                        }
                    }
                };
            }

            // Event handler untuk form submission
            if (registerForm) {
                registerForm.onsubmit = async function(e) {
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

                        if (result.status === 'success' || response.ok) {
                            window.location.href = result.redirect || '/dashboard';
                        } else {
                            throw new Error(result.message || 'Registration failed');
                        }
                    } catch (error) {
                        alert('Form submission error: ' + error.message);
                    }
                };
            }

            // Cleanup pada unload
            window.addEventListener('unload', stopCamera);
        });
    </script>
</x-guest-layout>
