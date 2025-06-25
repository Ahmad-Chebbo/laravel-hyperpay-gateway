<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Payment Pending</title>

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
                    <!-- Pending Icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                        <svg class="h-6 w-6 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Payment Pending
                    </h2>

                    <p class="text-gray-600 mb-6">
                        Your payment is being processed. This may take a few moments. You will receive a confirmation once the payment is completed.
                    </p>

                    <!-- Payment Details -->
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
                                <span class="text-yellow-600 font-medium">{{ ucfirst($payment->status) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Progress Bar -->
                    <div class="mb-6">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Processing payment...</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button onclick="checkPaymentStatus()"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Check Status
                        </button>

                        <a href="{{ route('home') }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkPaymentStatus() {
            // This would typically make an AJAX call to check payment status
            // For now, we'll just reload the page
            window.location.reload();
        }

        // Auto-check status every 5 seconds
        setInterval(checkPaymentStatus, 5000);
    </script>
</body>
</html>
