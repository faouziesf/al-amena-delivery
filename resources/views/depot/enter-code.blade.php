<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Saisir Code Session - Scanner Dépôt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
        }

        .code-digit {
            width: 2.5rem;
            height: 3.5rem;
            font-size: 1.5rem;
            text-align: center;
            border: 3px solid #667eea;
            border-radius: 0.75rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        @media (min-width: 375px) {
            .code-digit {
                width: 2.75rem;
                height: 3.75rem;
                font-size: 1.75rem;
            }
        }

        @media (min-width: 400px) {
            .code-digit {
                width: 3rem;
                height: 4rem;
                font-size: 2rem;
            }
        }

        .code-digit:focus {
            border-color: #10B981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            outline: none;
        }

        .code-digit.error {
            border-color: #EF4444;
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .numpad-button {
            width: 4rem;
            height: 4rem;
            font-size: 1.5rem;
            font-weight: bold;
            border-radius: 1rem;
            transition: all 0.2s ease;
            touch-action: manipulation;
        }

        @media (min-width: 375px) {
            .numpad-button {
                width: 4.5rem;
                height: 4.5rem;
                font-size: 1.65rem;
            }
        }

        @media (min-width: 400px) {
            .numpad-button {
                width: 5rem;
                height: 5rem;
                font-size: 1.75rem;
            }
        }

        .numpad-button:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-black text-white mb-2">Scanner</h1>
            <p class="text-white text-opacity-90">Saisissez le code de 8 chiffres</p>
            <p class="text-white text-opacity-75 text-xs mt-2">Dépôt ou Retours</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6">

            <!-- Error Message -->
            @if($errors->any())
            <div class="mb-4 bg-red-50 border-2 border-red-300 rounded-xl p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-red-600">{{ $errors->first('code') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <form id="code-form" method="GET" action="#">
                <!-- Pas de CSRF pour GET -->

                <!-- Code Input Display -->
                <div class="mb-6 sm:mb-8">
                    <p class="text-center text-sm text-gray-600 mb-3 sm:mb-4 font-semibold">CODE DE SESSION</p>
                    <div class="flex justify-center space-x-1 sm:space-x-2 mb-4 sm:mb-6">
                        <input type="text" maxlength="1" class="code-digit" id="digit-1" data-index="0" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-2" data-index="1" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-3" data-index="2" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-4" data-index="3" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-5" data-index="4" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-6" data-index="5" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-7" data-index="6" readonly>
                        <input type="text" maxlength="1" class="code-digit" id="digit-8" data-index="7" readonly>
                    </div>
                    <input type="hidden" name="code" id="code-input">
                </div>

                <!-- Numpad -->
                <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-6">
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('1')">1</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('2')">2</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('3')">3</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('4')">4</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('5')">5</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('6')">6</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('7')">7</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('8')">8</button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('9')">9</button>
                    <button type="button" class="numpad-button bg-red-100 hover:bg-red-200 text-red-600" onclick="clearCode()">
                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <button type="button" class="numpad-button bg-gray-100 hover:bg-gray-200" onclick="addDigit('0')">0</button>
                    <button type="button" class="numpad-button bg-orange-100 hover:bg-orange-200 text-orange-600" onclick="removeLastDigit()">
                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/>
                        </svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        id="submit-btn"
                        disabled
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-bold py-4 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:from-green-600 hover:to-green-700 transition-all shadow-lg">
                    <span id="submit-text">Saisissez 8 chiffres</span>
                    <span id="submit-loading" class="hidden">
                        <svg class="inline w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Connexion...
                    </span>
                </button>
            </form>

        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center">
            <p class="text-white text-opacity-75 text-sm">
                Le code de session est affiché sur l'écran PC<br>
                sous le QR code (8 chiffres)
            </p>
        </div>
    </div>

    <script>
        let currentCode = '';

        function addDigit(digit) {
            if (currentCode.length < 8) {
                currentCode += digit;
                updateDisplay();

                // Vibration feedback
                if (navigator.vibrate) {
                    navigator.vibrate(30);
                }

                // Auto-submit when 8 digits
                if (currentCode.length === 8) {
                    document.getElementById('submit-btn').disabled = false;
                    document.getElementById('submit-text').textContent = '✅ Valider le Code';
                }
            }
        }

        function removeLastDigit() {
            if (currentCode.length > 0) {
                currentCode = currentCode.slice(0, -1);
                updateDisplay();

                if (navigator.vibrate) {
                    navigator.vibrate(20);
                }

                if (currentCode.length < 8) {
                    document.getElementById('submit-btn').disabled = true;
                    document.getElementById('submit-text').textContent = 'Saisissez 8 chiffres';
                }
            }
        }

        function clearCode() {
            currentCode = '';
            updateDisplay();

            if (navigator.vibrate) {
                navigator.vibrate([30, 20, 30]);
            }

            document.getElementById('submit-btn').disabled = true;
            document.getElementById('submit-text').textContent = 'Saisissez 8 chiffres';
        }

        function updateDisplay() {
            // Update all digit displays
            for (let i = 1; i <= 8; i++) {
                const input = document.getElementById(`digit-${i}`);
                const digit = currentCode[i - 1] || '';
                input.value = digit;

                // Remove error class
                input.classList.remove('error');
            }

            // Update hidden input
            document.getElementById('code-input').value = currentCode;
        }

        // Form submission - Redirect via GET
        document.getElementById('code-form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (currentCode.length === 8) {
                document.getElementById('submit-text').classList.add('hidden');
                document.getElementById('submit-loading').classList.remove('hidden');
                document.getElementById('submit-btn').disabled = true;

                // Redirect via GET pour éviter CSRF avec ngrok
                window.location.href = '/depot/validate-code/' + currentCode;
            }
        });

        // Show error animation if errors exist
        @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            const digits = document.querySelectorAll('.code-digit');
            digits.forEach(input => input.classList.add('error'));

            if (navigator.vibrate) {
                navigator.vibrate([100, 50, 100, 50, 100]);
            }

            setTimeout(() => {
                digits.forEach(input => input.classList.remove('error'));
            }, 500);
        });
        @endif

        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>
</html>
