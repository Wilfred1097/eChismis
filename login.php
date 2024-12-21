<?php
// Initialize error message
$error = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Both fields are required!";
    } else {
        // Path to the JSON file
        $filePath = 'users_data.json';

        // Check if the file exists
        if (file_exists($filePath)) {
            // Get the current data from the file
            $jsonData = file_get_contents($filePath);
            $users = json_decode($jsonData, true);

            // Search for the user by email
            $userFound = false;
            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    $userFound = true;

                    // Verify the password
                    if ($user['password'] === $password) {
                        // Successful login
                        // Set a cookie for user ID (expires in 1 hour)
                        setcookie("user_id", $user['id'], time() + 3600, "/"); // 3600 seconds = 1 hour

                        // Redirect to dashboard or any protected page
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Incorrect password!";
                    }
                }
            }

            if (!$userFound) {
                $error = "No account found with this email!";
            }
        } else {
            $error = "User data file not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eChismis Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #4e73df, #1d2d8f);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        .container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .container input:focus {
            border-color: #4e73df;
            outline: none;
        }
        .container button {
            width: 100%;
            padding: 12px;
            border: 1px solid;
            background: #4e73df;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .container button:hover {
            background-color: white;
            color: #4e73df;
        }
        .container a {
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
            color: #4e73df;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .container a:hover {
            color: #3a58b2;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>eChismis Login</h2>
        <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="index.php">Register</a></p>
        </form>
    </div>
</body>
</html>
