<?php
// Start the session
session_start();

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require 'conn.php';

// WebSocket configuration
$ws_server_url = 'ws://localhost:8080'; // Replace with your WebSocket server URL

// Get the logged-in user's ID and the recipient's ID
$user_id = $_SESSION['user_id'];
$recipient_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch the recipient's details
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$stmt->bind_result($recipient_name);
if (!$stmt->fetch()) {
    // If recipient not found, return an error response
    echo "Error: Recipient not found.";
    exit();
}
$stmt->close();

// Fetch or create a conversation
$stmt = $conn->prepare("SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
$stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
$stmt->execute();
$stmt->bind_result($conversation_id);

if (!$stmt->fetch()) {
    // Create a new conversation if none exists
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $recipient_id);
    $stmt->execute();
    $conversation_id = $stmt->insert_id;
}
$stmt->close();

// Fetch messages in the conversation for GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $last_timestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : '1970-01-01 00:00:00';
    $stmt = $conn->prepare("SELECT sender_id, message, sent_at FROM messages WHERE conversation_id = ? AND sent_at > ? ORDER BY sent_at ASC");
    $stmt->bind_param("is", $conversation_id, $last_timestamp);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $isSender = $row['sender_id'] == $user_id;
        $messageClass = $isSender ? 'sent' : 'received';
        $formattedDate = (new DateTime($row['sent_at']))->format('M. d, Y h:iA');
        echo "<div class='message $messageClass'>" . htmlspecialchars($row['message']) . "<br><small>$formattedDate</small></div>";
    }
    $stmt->close();
    exit();
}

// Handle POST request for sending messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Check if the message with the same conversation_id and sender_id already exists
        $stmt = $conn->prepare("SELECT id FROM messages WHERE conversation_id = ? AND sender_id = ?");
        $stmt->bind_param("ii", $conversation_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            // If no message exists with the same conversation_id and sender_id, insert the new message
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message, sent_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $conversation_id, $user_id, $message);
            
            if ($stmt->execute()) {
                // Message successfully saved to the database
                
                // Prepare data to send to the WebSocket server
                $message_data = [
                    'conversation_id' => $conversation_id,
                    'sender_id' => $user_id,
                    'recipient_id' => $recipient_id,
                    'message' => $message,
                    'sent_at' => date('Y-m-d H:i:s') // Use the same time as the database
                ];

                // Push the message to the WebSocket server
                $ws_client = stream_socket_client("tcp://localhost:3000", $errno, $errstr);
                if ($ws_client) {
                    fwrite($ws_client, json_encode($message_data));
                    fclose($ws_client);
                } else {
                    error_log("WebSocket error: $errstr ($errno)");
                }
            } else {
                // Log error if the database insertion fails
                error_log("Database error: " . $stmt->error);
            }
        } else {
            // If a message already exists with the same conversation_id and sender_id
            error_log("Message already exists for this conversation and sender.");
        }
        
        $stmt->close();
    }
    exit();
}

?>
