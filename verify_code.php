<?php
session_start();
date_default_timezone_set('Asia/Manila');

require_once 'php/db_config.php';

// Check if code is already verified - redirect to new password
if (isset($_SESSION['code_verified']) && $_SESSION['code_verified'] === true) {
    header('Location: new_password.php');
    exit();
}

// Check if there's an active reset attempt
// Check if user came from forgot_password.php
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['code_requested_at'])) {
    // Clear all reset-related sessions
    unset($_SESSION['reset_email']);
    unset($_SESSION['code_verified']);
    unset($_SESSION['code_verified_at']);
    unset($_SESSION['code_requested_at']);
    header('Location: forgot_password.php');
    exit();
}

// Check if the code request is expired (2 minutes)
// if (time() - $_SESSION['code_requested_at'] > 120) {
//     echo "<script>
//         $(document).ready(function() {
//             $('#resendBtn').prop('disabled', false);
//             Swal.fire({
//                 icon: 'warning',
//                 title: 'Code Expired',
//                 text: 'Click \"Resend code\" to get a new verification code',
//                 confirmButtonColor: '#d32f2f'
//             });
//         });
//     </script>";
// }

// Handle AJAX verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['verify_code'])) {
        $code = $_POST['verify_code'];
        $email = $_SESSION['reset_email'];
        
        $stmt = $conn->prepare("SELECT id FROM otp_requests WHERE email = ? AND code = ? AND expires_at > NOW()");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 1) {
            // Mark code as verified
            $stmt = $conn->prepare("UPDATE otp_requests SET verified = 1 WHERE email = ? AND code = ?");
            $stmt->bind_param("ss", $email, $code);
            $stmt->execute();
    
            $_SESSION['code_verified'] = true;
            $_SESSION['code_verified_at'] = time();
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired code']);
        }
        exit;
    }
    
    // Handle expiration time request
    if (isset($_POST['get_expiration'])) {
        $email = $_SESSION['reset_email'];
        $stmt = $conn->prepare("SELECT expires_at FROM otp_requests WHERE email = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $expires_in = max(0, strtotime($row['expires_at']) - time());
            echo json_encode(['status' => 'success', 'expires_in' => $expires_in]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No active code found']);
        }
        exit;
    }
}

// Regular page load checks
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit();
}

// Check if code has expired
$email = $_SESSION['reset_email'];
// $stmt = $conn->prepare("SELECT expires_at FROM otp_requests WHERE email = ? ORDER BY created_at DESC LIMIT 1");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($result->num_rows > 0) {
//     $row = $result->fetch_assoc();
//     if (strtotime($row['expires_at']) < time()) {
//         header('Location: forgot_password.php');
//         exit();
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
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
                    <div class="flex items-center justify-between">
                        <img class="h-16 w-auto" src="images/logo.png" alt="ResponSys">
                        <img class="h-16 w-auto" src="images/pg_logo.png" alt="Padre Garcia">
                    </div>
                    <h2 class="mt-10 text-center font-bold tracking-tight text-gray-900 mb-6">We sent a code to 
                        <?php echo $_SESSION['reset_email']; ?>
                    </h2>
                </div>
    
                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <form id="verifyForm" class="space-y-6">
                        <div class="flex justify-center gap-2">
                            <input type="text" maxlength="1" class="verification-code w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-md" required>
                            <input type="text" maxlength="1" class="verification-code w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-md" required>
                            <input type="text" maxlength="1" class="verification-code w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-md" required>
                            <input type="text" maxlength="1" class="verification-code w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-md" required>
                            <input type="text" maxlength="1" class="verification-code w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-md" required>
                            <input type="text" maxlength="1" class="verification-code w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-md" required>
                        </div>
                        <button type="submit" id="verifyBtn" disabled 
                            class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Continue
                        </button>
                    </form>
    
                    <div class="mt-6 text-center">
                        <button id="resendBtn" disabled class="text-indigo-600 hover:text-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            Resend code
                        </button>
                        <p class="text-sm text-gray-500 mt-2">Code expires in <span id="timer">02:00</span></p>
                    </div>

                    <div class="mt-4 text-center space-y-2">
                        <a href="forgot_password.php" class="text-sm text-indigo-600 hover:text-indigo-500 block">
                            Back to forgot password
                        </a>
                        <form action="login.php" method="POST" class="inline">
                            <input type="hidden" name="clear_reset" value="1">
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-600">
                                Cancel password reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


<script>
    $(document).ready(function() {
        let expirationTimer;
        let allInputsFilled = false;

        function startExpirationTimer() {
            // Get expiration time
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: { get_expiration: true },
                success: function(response) {
                    if (response.expires_in) {
                        let timeLeft = response.expires_in;
                        expirationTimer = setInterval(() => {
                            timeLeft--;
                            const minutes = Math.floor(timeLeft / 60);
                            const seconds = timeLeft % 60;
                            $('#timer').text(
                                minutes.toString().padStart(2, '0') + ':' + 
                                seconds.toString().padStart(2, '0')
                            );
                            
                            if (timeLeft <= 0) {
                                clearInterval(expirationTimer);
                                $('#resendBtn').prop('disabled', false);
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Code Expired',
                                    text: 'Click "Resend code" to get a new verification code',
                                    confirmButtonColor: "#d32f2f"
                                });
                            }
                        }, 1000);
                    }
                }
            });
        }

        startExpirationTimer();

        function checkInputs() {
            allInputsFilled = true;
            $('.verification-code').each(function() {
                if (!$(this).val()) {
                    allInputsFilled = false;
                    return false;
                }
            });
            $('#verifyBtn').prop('disabled', !allInputsFilled);
        }

        // Code input handling
        $('.verification-code').on('input', function() {
            if (this.value.length === 1) {
                const next = $(this).next('.verification-code');
                if (next.length) next.focus();
            }
            checkInputs();
        });

        $('.verification-code').on('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 0) {
                const prev = $(this).prev('.verification-code');
                if (prev.length) prev.focus();
            }
        });

        // Form submission
        $('#verifyForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#verifyBtn');
            const originalText = submitBtn.html();
            
            submitBtn.html(`
                <div class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <circle class="opacity-75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="30" stroke-dashoffset="20" fill="none"></circle>
                    </svg>
                    Verifying...
                </div>
            `).prop('disabled', true);

            let code = '';
            $('.verification-code').each(function() {
                code += $(this).val();
            });

            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: { verify_code: code },
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = 'new_password.php';
                    } else {
                        submitBtn.html(originalText).prop('disabled', !allInputsFilled);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: "#d32f2f"
                        }).then(() => {
                            // Clear all inputs and reset form
                            $('.verification-code').val('');
                            $('.verification-code').first().focus();
                            checkInputs();
                        });
                    }
                },
                error: function() {
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

        // Handle resend timer
        let resendTimer;
        function startResendTimer() {
            let timeLeft = 120; // 2 minutes
            $('#resendBtn').prop('disabled', true);
            
            resendTimer = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(resendTimer);
                    $('#resendBtn').prop('disabled', false);
                }
            }, 1000);
        }

        startResendTimer();

        // Replace the resend button click handler
        $('#resendBtn').click(function() {
            const btn = $(this);
            const originalText = btn.html();
            
            btn.html(`
                <div class="flex items-center justify-center">
                    <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <circle class="opacity-75" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-dasharray="30" stroke-dashoffset="20" fill="none"></circle>
                    </svg>
                    Sending...
                </div>
            `).prop('disabled', true);
        
            // Use the existing session email to resend code
            $.ajax({
                url: 'php/sign-in/send_verification.php',
                method: 'POST',
                data: { email: '<?php echo $_SESSION["reset_email"]; ?>' },
                success: function(response) {
                    if (response.status === 'success') {
                        // Reset timers and form
                        clearInterval(expirationTimer);
                        startExpirationTimer();
                        startResendTimer();
                        $('.verification-code').val('');
                        $('.verification-code').first().focus();
                        
                        btn.html(originalText);
                        Swal.fire({
                            icon: 'success',
                            title: 'Code Sent!',
                            text: 'A new verification code has been sent to your email',
                            confirmButtonColor: "#52b855"
                        });
                    } else {
                        btn.html(originalText).prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: "#d32f2f"
                        });
                    }
                },
                error: function() {
                    btn.html(originalText).prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to send new code. Please try again.',
                        confirmButtonColor: "#d32f2f"
                    });
                }
            });
        });
    });
</script>
</body>
</html>