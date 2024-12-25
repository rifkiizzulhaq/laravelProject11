<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <!-- Face Recognition Scripts -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                const loadingIndicator = document.getElementById('loadingIndicator');
                const faceLoginBtn = document.getElementById('faceLoginBtn');

                if (faceLoginBtn) {
                    // Initialize camera when button exists (login page)
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            console.log('Camera initialized');
                            video.srcObject = stream;
                        })
                        .catch(err => {
                            console.error('Camera error:', err);
                            alert('Camera access error: ' + err.message);
                        });

                    // Add click event listener
                    faceLoginBtn.addEventListener('click', async function() {
                        try {
                            console.log('Starting face login...');
                            loadingIndicator.classList.remove('hidden');
                            faceLoginBtn.disabled = true;

                            // Capture image
                            const context = canvas.getContext('2d');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            context.drawImage(video, 0, 0, canvas.width, canvas.height);

                            // Convert to blob
                            const blob = await new Promise(resolve => canvas.toBlob(resolve));
                            const formData = new FormData();
                            formData.append('image', blob);

                            // Send request
                            const response = await fetch('/login/face', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const data = await response.json();
                            console.log('Response:', data);

                            if (data.message === 'Login successful') {
                                window.location.href = data.redirect;
                            } else {
                                alert(data.error || 'Face login failed. Please try again.');
                            }

                        } catch (error) {
                            console.error('Error:', error);
                            alert('Error during face login: ' + error.message);
                        } finally {
                            loadingIndicator.classList.add('hidden');
                            faceLoginBtn.disabled = false;
                        }
                    });
                }
            });
        </script>
    </body>
</html>
