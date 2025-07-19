<?php
require_once __DIR__ . '/php/load_env.php';
loadEnv(__DIR__ . '/.env');
session_start();

// Clear reset sessions if coming from reset flow
if (isset($_POST['clear_reset'])) {
    unset($_SESSION['reset_email']);
    unset($_SESSION['code_verified']);
    unset($_SESSION['code_verified_at']);
    unset($_SESSION['code_requested_at']);
}

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user']) && $_SESSION['user'] === true) {
    header('Location: manage/dashboard.php');
    exit();
}

$siteKey = $_ENV['RECAPTCHA_SITE_KEY'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to your account</title>
    <link rel="stylesheet" href="css/tailwind/output.css">
    <link rel="stylesheet" href="css/tailwind/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Poppins font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- jQuery Validation -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Google Captcha -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <section class="bg-zinc-50 h-full flex items-center justify-center">
        <div class="flex min-h-full flex-col justify-center items-center lg:px-8">
            <div class="w-full sm:max-w-sm mx-auto bg-[#fdfdfd] border border-gray-200 rounded-lg p-8">
                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <div class="flex items-center justify-between">
                        <img class="h-16 w-auto" src="images/logo.png" alt="ResponSys">
                        <img class="h-16 w-auto" src="images/pg_logo.png" alt="Padre Garcia">
                    </div>
                    <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900 mb-6">Sign in to your account</h2>
                </div>

                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <form id="loginForm" class="space-y-6" action="php/sign-in/validate_login.php" method="POST">
                        <div>
                            <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                            <div class="mt-2">
                                <input type="email" name="email" id="email" autocomplete="email" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                                focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                                <div class="text-sm">
                                    <a href="forgot_password.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                                </div>
                            </div>                            
                            <div class="mt-2 relative">
                                <input type="password" name="password" id="password" autocomplete="current-password" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 pr-10">
                                <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <div class="text-red-500 text-xs mt-1"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-center w-full">
                                <div class="g-recaptcha" 
                                    data-sitekey="<?php echo htmlspecialchars($siteKey); ?>"
                                    data-callback="enableSubmitButton">
                                </div>
                            </div>
                            <button type="submit" id="submitBtn" disabled 
                                class="mt-2 flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                Sign in
                            </button>
                        </div>
                    </form>

                    <p class="mt-10 text-center text-sm/6 text-gray-500">
                        By logging in, you confirm that you have read and agree to be bound by our
                        <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Terms and Conditions</a>
                        and
                        <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Privacy Policy</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

<script src="js/formValidation.js"></script>

<script>
    // Function to enable submit button after CAPTCHA completion
    function enableSubmitButton() {
        document.getElementById('submitBtn').disabled = false;
    }

    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);
        button.innerHTML = type === 'password' ? 
            '<i class="fa-solid fa-eye"></i>' : 
            '<i class="fa-solid fa-eye-slash"></i>';
    }

    // Form handling
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form values
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        // Validate email and password
        if (!email || !password) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please fill in both email and password fields',
                confirmButtonColor: "#d32f2f",
                timer: 1000
            });
            return;
        }

        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please complete the CAPTCHA verification',
                confirmButtonColor: "#d32f2f",
                timer: 1000
            });
            return;
        }

        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = `
            <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <circle class="opacity-75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="30" stroke-dashoffset="20" fill="none"></circle>
                </svg>
                <span class="ml-2">Signing in...</span>
            </div>`;
        submitButton.disabled = true;

        // Get the password and encrypt it
        const encoder = new TextEncoder();
        const cryptoSubtle = window.crypto.subtle;

        // Create SHA-256 hash of password
        cryptoSubtle.digest('SHA-256', encoder.encode(password))
            .then(function(hashedBuffer) {
                // Convert hash bytes to hex string
                const hashedPassword = Array.from(new Uint8Array(hashedBuffer))
                    .map(b => b.toString(16).padStart(2, '0'))
                    .join('');

                // Create FormData with hashed password
                const formData = new FormData();
                formData.append('email', document.getElementById('email').value);
                formData.append('password', hashedPassword);
                formData.append('g-recaptcha-response', recaptchaResponse);

                return fetch('php/sign-in/validate_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = 'manage/dashboard.php';
                    } else {
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = true;
                        grecaptcha.reset();

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Login failed: " + data.message,
                            // text: "Login failed", 
                            confirmButtonColor: "#d32f2f",
                            timer: 1000
                        });
                    }
                })
                .catch(error => {
                    console.log('Error:', error);
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                    grecaptcha.reset();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        // text: "An error occurred: " + error.message,
                        text: "An error occurred",
                        confirmButtonColor: "#d32f2f",
                        timer: 1000
                    });
                });
        });
    });
</script>

</body>
</html>