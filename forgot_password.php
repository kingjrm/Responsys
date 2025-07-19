<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Clear any existing reset sessions when returning to this page
unset($_SESSION['reset_email']);
unset($_SESSION['code_verified']);
unset($_SESSION['code_verified_at']);
unset($_SESSION['code_requested_at']);

if (isset($_SESSION['user']) && $_SESSION['user'] === true) {
    header('Location: manage/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        <div class="flex w-[600px] min-h-full flex-col justify-center items-center lg:px-8">
            <div class="w-full sm:max-w-sm mx-auto bg-[#fdfdfd] border border-gray-200 rounded-lg p-8">
                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <div class="flex items-center justify-between">
                        <img class="h-16 w-auto" src="images/logo.png" alt="ResponSys">
                        <img class="h-16 w-auto" src="images/pg_logo.png" alt="Padre Garcia">
                    </div>
                    <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900 mb-6">Forgot Password</h2>
                </div>

                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <form id="emailForm" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                            <div class="mt-2">
                                <input type="email" name="email" id="email" autocomplete="email" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                                focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            </div>
                        </div>
                        <button type="submit" id="sendCodeBtn" disabled
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            Send Code
                        </button>
                        <div class="text-center">
                            <a href="login.php" class="text-sm text-indigo-600 hover:text-indigo-500">Back to login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

<script>
    $(document).ready(function() {
        // Email validation function
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Handle email input changes
        $('#email').on('input', function() {
            const email = $(this).val().trim();
            const isValid = isValidEmail(email);
            $('#sendCodeBtn').prop('disabled', !isValid);
        });

        $('#emailForm').on('submit', function(e) {
            e.preventDefault();
            const email = $('#email').val();

            // Get button reference and store original text
            const submitBtn = $('#sendCodeBtn');
            const originalText = submitBtn.html();

            // Update button to loading state
            submitBtn.html(`
                <div class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <circle class="opacity-75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="30" stroke-dashoffset="20" fill="none"></circle>
                    </svg>
                    Sending code...
                </div>
            `).prop('disabled', true);
            
            $.ajax({
                url: 'php/sign-in/send_verification.php',
                method: 'POST',
                data: { email: email },
                success: function(response) {
                    if (response.status === 'success') {
                        // Store user_id in session and redirect
                        sessionStorage.setItem('reset_email', email);
                        window.location.href = 'verify_code.php';
                    } else {
                        // Reset button state
                        submitBtn.html(originalText).prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: "#d32f2f"
                        });
                    }
                },
                error: function() {
                    // Reset button state
                    submitBtn.html(originalText).prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: "#d32f2f"
                    });
                }
            });
        });

        // Add jQuery validation
        $('#emailForm').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },
            messages: {
                email: {
                    required: "Please enter your email address",
                    email: "Please enter a valid email address"
                }
            },
            errorElement: 'span',
            errorClass: 'error',
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
            }
        });

    });
</script>
</body>
</html>