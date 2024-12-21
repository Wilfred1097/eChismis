<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the data from WebSocket message (sent via AJAX)
$data = json_decode(file_get_contents("php://input"), true);

// Get the logged-in user's ID (from session or cookie)
$user_id = $_COOKIE['user_id']; // Assuming user_id is stored in a cookie
$receiver_id = $data['receiver_id']; // The user being chatted with
$message = $data['message']; // The message content
$sent_at = $data['sent_at']; // The timestamp when the message was sent

// Path to the messages data file
$messagesFilePath = 'messages_data.json';

// Check if the file exists
if (file_exists($messagesFilePath)) {
    // Get the current data from the file
    $jsonData = file_get_contents($messagesFilePath);
    $conversations = json_decode($jsonData, true);
} else {
    $conversations = [];
}

// Create a unique conversation ID by sorting the user IDs
// This ensures the conversation between two users is always identified in the same way
$conversation_id = $user_id < $receiver_id ? $user_id . '-' . $receiver_id : $receiver_id . '-' . $user_id;

// Check if the conversation already exists
$conversationFound = false;

foreach ($conversations as &$conversation) {
    if ($conversation['conversation_id'] === $conversation_id) {
        // Add the new message to the conversation
        $conversation['messages'][] = [
            'sender_id' => $user_id,
            'message' => $message,
            'sent_at' => $sent_at
        ];
        $conversationFound = true;
        break;
    }
}

// If the conversation doesn't exist, create a new conversation
if (!$conversationFound) {
    $conversations[] = [
        'conversation_id' => $conversation_id,
        'users' => [$user_id, $receiver_id],
        'messages' => [
            [
                'sender_id' => $user_id,
                'message' => $message,
                'sent_at' => $sent_at
            ]
        ]
    ];
}

// Save the updated conversations back to the file
if (file_put_contents($messagesFilePath, json_encode($conversations, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => "Message sent successfully!"]);
} else {
    echo json_encode(["error" => "Error saving message!"]);
}
?>
