<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the logged-in user's ID and receiver's ID from the request
$user_id = $_COOKIE['user_id']; // Assuming user_id is stored in a cookie
$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null; // Get the receiver_id from the query parameter

// Path to the messages data file
$messagesFilePath = 'messages_data.json'; // Make sure the path is correct

// Check if the file exists
if (file_exists($messagesFilePath)) {
    // Get the current data from the file
    $jsonData = file_get_contents($messagesFilePath);
    $conversations = json_decode($jsonData, true);
} else {
    $conversations = [];
}

// Create a unique conversation ID by sorting the user IDs
$conversation_id = $user_id < $receiver_id ? $user_id . '-' . $receiver_id : $receiver_id . '-' . $user_id;

// Find the conversation between the logged-in user and the receiver
$conversation = null;
foreach ($conversations as $conv) {
    if ($conv['conversation_id'] === $conversation_id) {
        $conversation = $conv;
        break;
    }
}

// If conversation is found, return the messages as a JSON response
if ($conversation) {
    echo json_encode([
        'messages' => $conversation['messages']
    ]);
} else {
    // If no conversation found, return an empty array
    echo json_encode([
        'messages' => []
    ]);
}
?>
