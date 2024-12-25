<x-guest-layout>
    <div class="flex flex-col items-center">
        <!-- Face Login Section -->
        <div class="w-full max-w-md">
            <h2 class="text-center text-xl font-semibold mb-4">{{ __('Login with Face Recognition') }}</h2>
            
            <!-- Camera Preview -->
            <div class="relative">
                <video id="video" class="w-full rounded-lg shadow-lg" autoplay></video>
                <canvas id="canvas" class="hidden"></canvas>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden mt-4 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-t-blue-500"></div>
                <p class="mt-2 text-gray-600">Recognizing face...</p>
            </div>

            <!-- Face Login Button -->
            <div class="mt-4">
                <button type="button" 
                        id="faceLoginBtn" 
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Login with Face') }}
                </button>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-6 w-full max-w-md">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">{{ __('Or continue with') }}</span>
                </div>
            </div>
        </div>

        <!-- Traditional Login Form -->
        <div class="w-full max-w-md">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

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
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-primary-button class="ms-3">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
