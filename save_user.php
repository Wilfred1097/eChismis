<?php
// Set the correct content type
header('Content-Type: application/json');

// Save the incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    // Path to the JSON file
    $filePath = 'users_data.json';

    // Check if the JSON file exists
    if (file_exists($filePath)) {
        // Get the current data from the file
        $jsonData = file_get_contents($filePath);
        $users = json_decode($jsonData, true);
    } else {
        $users = [];
    }

    // Check if the username or email already exists
    foreach ($users as $user) {
        if ($user['username'] === $data['username'] || $user['email'] === $data['email']) {
            echo json_encode(["error" => "Username or email already exists!"]);
            exit();
        }
    }

    // Generate the new user ID
    $newId = 1; // Default to 1 if there are no users
    if (count($users) > 0) {
        // Get the highest ID and increment it
        $newId = max(array_column($users, 'id')) + 1;
    }

    // Add the new user data with the generated ID
    $users[] = [
        'id' => $newId, // Assign the new ID
        'username' => $data['username'],
        'email' => $data['email'],
        'password' => $data['password'] // Store the raw password
    ];

    // Save the updated data back to the JSON file
    if (file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT))) {
        echo json_encode(["success" => "User registered successfully!"]);
    } else {
        echo json_encode(["error" => "Error saving user data!"]);
    }
} else {
    echo json_encode(["error" => "Invalid data!"]);
}
?>
