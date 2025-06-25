<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Payment Failed</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="flex items-center justify-center min-h-screen">
            <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
                <div class="text-center">
                    <!-- Error Icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Payment Failed
                    </h2>

                    <p class="text-gray-600 mb-6">
                        We're sorry, but your payment could not be processed. Please try again or contact support if the problem persists.
                    </p>

                    <!-- Error Details -->
                    @if(isset($error))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <div class="text-sm text-red-800">
                            <strong>Error:</strong> {{ $error }}
                        </div>
                    </div>
                    @endif

                    @if(isset($payment))
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Amount:</span>
                                <span class="text-gray-900">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Transaction ID:</span>
                                <span class="text-gray-900">{{ $payment->transaction_id }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Payment Method:</span>
                                <span class="text-gray-900">{{ $payment->brand }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Status:</span>
                                <span class="text-red-600 font-medium">{{ ucfirst($payment->status) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <a href="{{ route('payment.retry') }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Try Again
                        </a>

                        <a href="{{ route('home') }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Return to Home
                        </a>

                        <a href="{{ route('support.contact') }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
