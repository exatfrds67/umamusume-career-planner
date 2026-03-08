@extends('layouts.guest')

@section('title', 'Create Account - Umamusume Career Planner')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="glass-card rounded-2xl p-8">
                <div class="text-center">
                    <a href="{{ route('welcome') }}">
                        <img src="/images/app_logo/uma_musume_race_planner_logo_256.png" alt="{{ config('app.name') }} logo"
                            loading="eager" decoding="async" class="h-20 w-20 mx-auto mb-4" width="80" height="80">
                    </a>
                    <h2 class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                        Create your account
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Or
                        <a href="{{ route('login') }}"
                            class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                            sign in to existing account
                        </a>
                    </p>
                </div>

                <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                        There were errors with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Name
                            </label>
                            <input id="name" name="name" type="text" autocomplete="name" required
                                value="{{ old('name') }}"
                                class="appearance-none relative block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 placeholder-neutral-500 dark:placeholder-neutral-400 text-neutral-900 dark:text-white rounded-md focus:outline-hidden focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm bg-white dark:bg-neutral-800"
                                placeholder="Your name">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Email address
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                value="{{ old('email') }}"
                                class="appearance-none relative block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 placeholder-neutral-500 dark:placeholder-neutral-400 text-neutral-900 dark:text-white rounded-md focus:outline-hidden focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm bg-white dark:bg-neutral-800"
                                placeholder="you@example.com">
                        </div>

                        <div>
                            <x-password-input id="password" name="password" label="Password" autocomplete="new-password"
                                placeholder="••••••••" required />
                        </div>

                        <div>
                            <x-password-input id="password_confirmation" name="password_confirmation"
                                label="Confirm Password" autocomplete="new-password" placeholder="••••••••" required />
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-primary-500 group-hover:text-primary-400" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                                </svg>
                            </span>
                            Create account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
