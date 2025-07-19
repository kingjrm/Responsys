<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHA-256 Password Hashing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .result {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .result p {
            margin-bottom: 10px;
        }
        .result strong {
            display: inline-block;
            min-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>SHA-256 Password Hashing</h2>
        <form method="POST" action="">
            <div>
                <label for="password">Enter Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Hash Password</button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["password"])) {
                $password = $_POST["password"];
                $hashed_password = hash('sha256', $password);
                echo '<div class="result">';
                echo '<p><strong>Original Password:</strong> ' . htmlspecialchars($password) . '</p>';
                echo '<p><strong>SHA-256 Hash:</strong> ' . $hashed_password . '</p>';
                echo '</div>';
            } else {
                echo '<div class="result"><p>No password entered.</p></div>';
            }
        }
        ?>
    </div>
</body>
</html>