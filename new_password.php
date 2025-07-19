<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Check if user is in the proper flow
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit();
}

// Check if code was verified 
if (!isset($_SESSION['code_verified']) || $_SESSION['code_verified'] !== true) {
    header('Location: verify_code.php'); // Send back to verify code if not verified
    exit();
}

// Check if verification is recent (within 5 minutes)
if (!isset($_SESSION['code_verified_at']) || (time() - $_SESSION['code_verified_at'] > 300)) {
    unset($_SESSION['code_verified']);
    unset($_SESSION['code_verified_at']);
    header('Location: verify_code.php'); // Send back to verify code if expired
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
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
</head>
<body>
    <section class="bg-zinc-50 h-screen flex items-center justify-center">
        <div class="flex min-h-full flex-col justify-center items-center lg:px-8">
            <div class="w-full sm:max-w-sm mx-auto bg-[#fdfdfd] border border-gray-200 rounded-lg p-8">
                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <div class="flex items-center justify-between mb-4">
                        <img class="h-16 w-auto" src="images/logo.png" alt="ResponSys">
                        <img class="h-16 w-auto" src="images/pg_logo.png" alt="Padre Garcia">
                    </div>
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Set new password</h2>
                        <p class="mt-2 text-sm text-gray-600">Create a strong password for your account</p>
                    </div>
                </div>

                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <form id="passwordForm" class="space-y-6">
                        <div>
                            <label for="newPassword" class="block text-sm font-medium text-gray-700">New Password</label>
                            <div class="mt-1 relative">
                                <input type="password" id="newPassword" name="newPassword" required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                                    focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                <button type="button" onclick="togglePassword('newPassword')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <!-- Password requirements will be inserted here by jQuery -->
                        </div>

                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <div class="mt-1 relative">
                                <input type="password" id="confirmPassword" name="confirmPassword" required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                                    focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                <button type="button" onclick="togglePassword('confirmPassword')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="resetBtn" disabled 
                            class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Reset Password
                        </button>

                        <div class="mt-4 text-center">
                            <a href="login.php" onclick="clearSessions(event)" class="text-sm text-indigo-600 hover:text-indigo-500">
                                Cancel Password Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

<script>

    function clearSessions(e) {
        e.preventDefault();
        $.post('login.php', { clear_reset: 1 }, function() {
            window.location.href = 'login.php';
        });
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

    function validatePassword(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        return Object.values(requirements).every(Boolean);
    }

    // Add password requirements UI after the new password input
    const passwordRequirements = `
        <div class="mt-2 text-xs space-y-1">
            <p class="text-gray-500">Password must contain:</p>
            <p id="length" class="text-gray-500"><i class="fa-solid fa-circle text-[8px] mr-2"></i>At least 8 characters</p>
            <p id="uppercase" class="text-gray-500"><i class="fa-solid fa-circle text-[8px] mr-2"></i>One uppercase letter</p>
            <p id="lowercase" class="text-gray-500"><i class="fa-solid fa-circle text-[8px] mr-2"></i>One lowercase letter</p>
            <p id="number" class="text-gray-500"><i class="fa-solid fa-circle text-[8px] mr-2"></i>One number</p>
            <p id="special" class="text-gray-500"><i class="fa-solid fa-circle text-[8px] mr-2"></i>One special character</p>
        </div>`;


    $(document).ready(function() {
        // Add custom validation method for password requirements
        $.validator.addMethod("passwordRequirements", function(value, element) {
            return validatePassword(value);
        }, "Password must meet all requirements");

        function checkPasswordInputs() {
            const newPass = $('#newPassword').val();
            const confirmPass = $('#confirmPassword').val();
            const isValid = newPass && confirmPass && validatePassword(newPass) && newPass === confirmPass;
            $('#resetBtn').prop('disabled', !isValid);
        }

        $('#newPassword, #confirmPassword').on('input', checkPasswordInputs);
        
        // Insert password requirements after new password input
        $('#newPassword').parent().after(passwordRequirements);

        // Real-time password validation
        $('#newPassword').on('input', function() {
            const password = $(this).val();
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(req);
                if (requirements[req]) {
                    element.classList.remove('text-gray-500');
                    element.classList.add('text-green-500');
                    element.querySelector('i').classList.add('text-green-500');
                } else {
                    element.classList.remove('text-green-500');
                    element.classList.add('text-gray-500');
                    element.querySelector('i').classList.remove('text-green-500');
                }
            });
        });

        // jQuery Validation
        $('#passwordForm').validate({
            rules: {
                newPassword: {
                required: true,
                minlength: 8,
                passwordRequirements: true
                },
                confirmPassword: {
                    required: true,
                    equalTo: "#newPassword"
                }
            },
            messages: {
                newPassword: {
                    required: "Please enter a new password",
                    minlength: "Password must be at least 8 characters long",
                    pattern: "Password must meet all requirements"
                },
                confirmPassword: {
                    required: "Please confirm your password",
                    equalTo: "Passwords do not match"
                }
            },
            errorElement: 'span',
            errorClass: "error",
            errorPlacement: function(error, element) {
                error.addClass('text-red-500 text-xs mt-1');
                error.insertAfter(element.parent());
            },
            highlight: function(element) {
                $(element)
                    .removeClass('border-gray-200')
                    .removeClass('focus:outline-indigo-600')
                    .addClass('border-red-500')
                    .addClass('ring-2')
                    .addClass('ring-red-200')
                    .addClass('focus:outline-red-600')
                    .addClass('outline-red-300');
            },
            unhighlight: function(element) {
                $(element)
                    .addClass('border-gray-200')
                    .addClass('focus:outline-indigo-600')
                    .removeClass('border-red-500')
                    .removeClass('ring-2')
                    .removeClass('ring-red-200')
                    .removeClass('focus:outline-red-600')
                    .removeClass('outline-red-300');
            },
            submitHandler: function(form, event) {
                event.preventDefault();
                const newPassword = $('#newPassword').val();

                const submitBtn = $('#resetBtn');
                const originalText = submitBtn.html();
                
                submitBtn.html(`
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <circle class="opacity-75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="30" stroke-dashoffset="20" fill="none"></circle>
                        </svg>
                        Resetting...
                    </div>
                `).prop('disabled', true);

                if (!validatePassword(newPassword)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Password',
                        text: 'Please meet all password requirements',
                        confirmButtonColor: "#d32f2f"
                    });
                    return;
                }

                // Hash password on client side
                const encoder = new TextEncoder();
                const cryptoSubtle = window.crypto.subtle;

                cryptoSubtle.digest('SHA-256', encoder.encode(newPassword))
                    .then(function(hashedBuffer) {
                        // Convert hash to hex string
                        const hashedPassword = Array.from(new Uint8Array(hashedBuffer))
                            .map(b => b.toString(16).padStart(2, '0'))
                            .join('');

                        $.ajax({
                            url: 'php/sign-in/reset_password.php',
                            method: 'POST',
                            data: { newPassword: hashedPassword },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Your password has been reset',
                                        confirmButtonColor: "#52b855"
                                    }).then(() => {
                                        window.location.href = 'login.php';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: "#d32f2f"
                                    });
                                }
                            }
                        }).fail(function() {
                            submitBtn.html(originalText).prop('disabled', false);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                                confirmButtonColor: "#d32f2f"
                            });
                        });
                    });
            }
        });
    });
</script>
</body>
</html>