<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eChismis Register</title>
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
        <h2>eChismis Register</h2>
        <div id="error" class="error"></div>
        <form id="registerForm">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById("registerForm").addEventListener("submit", function(e) {
                e.preventDefault(); // Prevent the form from submitting normally

                // Get form data
                var username = document.getElementById("username").value.trim();
                var email = document.getElementById("email").value.trim();
                var password = document.getElementById("password").value.trim();

                // Validate input
                if (!username || !email || !password) {
                    document.getElementById("error").textContent = "All fields are required!";
                    return;
                }

                // Prepare user data
                var userData = {
                    username: username,
                    email: email,
                    password: password // Save the raw password without encryption
                };

                // Send the data to save_user.php using fetch
                fetch('save_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                })
                .then(response => response.json()) // Parse the JSON response
                .then(responseData => {
                    // Check the response from the PHP script
                    if (responseData.success) {
                        // Show success message using SweetAlert
                        Swal.fire({
                            title: 'Registration Successful!',
                            text: 'You will be redirected to the login page.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Redirect to login page after closing the alert
                            window.location.href = "login.php";
                        });
                    } else if (responseData.error) {
                        // Handle errors returned from the PHP script
                        Swal.fire({
                            title: 'Registration Unsuccessful!',
                            text: 'Entered Username or Email already exists!',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById("error").textContent = "There was an error processing your request!";
                });
            });
        });
    </script>
</body>
</html>
