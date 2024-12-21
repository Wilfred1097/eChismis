<?php
// Check if the user_id cookie is set
if (isset($_COOKIE['user_id'])) {
    // Get the logged-in user's ID from the cookie
    $user_id = $_COOKIE['user_id'];

    // Path to the JSON file
    $filePath = 'users_data.json';

    // Check if the file exists
    if (file_exists($filePath)) {
        // Get the current data from the file
        $jsonData = file_get_contents($filePath);
        $users = json_decode($jsonData, true);
    } else {
        echo "User data file not found.";
        exit();
    }
} else {
    // If user_id cookie is not set, redirect to login.php
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eChismis Conversation</title>
    <!-- Link to Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            height: 70vh;
            padding: 10px;
        }
        .sent {
            background-color: green;
            color: white;
            align-self: flex-end; /* Align messages sent by the user to the right */
        }

        /* Styling for received messages */
        .received {
            background-color: #e9e9e9;
            color: black;
            align-self: flex-start; /* Align received messages to the left */
        }

        .message {
            max-width: 70%;
            padding: 10px;
            border-radius: 10px;
            word-wrap: break-word;
            margin-bottom: 10px;
        }

        .sent-at {
            font-size: 0.8em;
            color: #888;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex">
        <!-- Users List -->
        <div class="col-3 bg-white border-end p-3 d-flex flex-column">
            <?php
            // Find the logged-in user and display their username
            $logged_in_user = null;
            foreach ($users as $user) {
                if ($user['id'] == $user_id) {
                    $logged_in_user = $user;
                    break;
                }
            }
            
            if ($logged_in_user) {
                $username = htmlspecialchars($logged_in_user['username']);
                echo "
                <h5>$username</h5>
                ";
            }
            ?>
            <ul class="list-unstyled">
                <?php
                // Loop through users data and display user list
                foreach ($users as $user) {
                    if ($user['id'] != $user_id) {
                        echo "<li class='p-2 border-bottom' style='cursor:pointer;' onclick=\"openChat({$user['id']}, '{$user['username']}')\">
                                <strong>" . htmlspecialchars($user['username']) . "</strong>
                            </li>";
                    }
                }
                ?>
            </ul>
            
            <!-- Logout Button -->
            <?php
            // Find the logged-in user and display their username
            $logged_in_user = null;
            foreach ($users as $user) {
                if ($user['id'] == $user_id) {
                    $logged_in_user = $user;
                    break;
                }
            }
            
            if ($logged_in_user) {
                $username = htmlspecialchars($logged_in_user['username']);
                echo "
                <form action='logout.php' method='POST' class='mt-auto'>
                    <button type='submit' class='btn btn-danger w-100'>
                        Logout
                    </button>
                </form>";
            }
            ?>
        </div>

        <!-- Chat Window -->
        <div class="col-9 d-flex flex-column">
    <div class="p-3 border-bottom">
        <h5 id="chatHeader">Select a user to chat</h5>
    </div>
    <div id="chatMessages" class="chat-messages bg-white flex-grow-1 overflow-auto"></div>
        <div class="p-3 border-top d-flex flex-column-reverse">
            <div class="d-flex">
                <input type="text" id="messageInput" class="form-control me-2" placeholder="Type a message..." disabled>
                <button class="btn btn-primary" id="sendButton" disabled>Send</button>
            </div>
        </div>
    </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedUserId = null;
        const ws = new WebSocket('ws://localhost:3000/chat'); // Replace with your WebSocket server URL

        ws.onopen = () => console.log('WebSocket connected');
        ws.onerror = (error) => console.error('WebSocket error:', error);
        ws.onclose = () => console.log('WebSocket closed');

        ws.onopen = () => {
            // console.log('Connected to WebSocket server');
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            // Check if the message is for the logged-in user or the selected user
            if (data.sender_id === selectedUserId || data.receiver_id === selectedUserId) {
                // Add message with the correct type (sent or received)
                const messageType = data.sender_id === <?php echo $user_id; ?> ? 'sent' : 'received';
                addMessage(data.message, messageType, data.sent_at);
            }
        };

        // Function to load chat history
        function openChat(userId, username) {
            selectedUserId = userId;
            document.getElementById('chatHeader').textContent = `Chat with ${username}`;
            document.getElementById('messageInput').disabled = false;
            document.getElementById('sendButton').disabled = false;
            document.getElementById('chatMessages').innerHTML = '<p>Loading messages...</p>';

            // Fetch chat history from the server
            fetch(`get_messages.php?user_id=<?php echo $user_id; ?>&receiver_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.messages) {
                        document.getElementById('chatMessages').innerHTML = '';  // Clear loading message
                        // console.log('Fetched messages:', data.messages);  // Log the fetched messages for debugging
                        data.messages.forEach(message => {
                            // Check if the sender is the logged-in user
                            const messageType = message.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received';
                            addMessage(message.message, messageType, message.sent_at);
                        });
                    } else {
                        document.getElementById('chatMessages').innerHTML = '<p>No chat history.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching chat history:', error);
                    document.getElementById('chatMessages').innerHTML = '<p>Error loading messages.</p>';
                });
        }


        // Function to add a message to the chat window
        function addMessage(message, type, sentAt) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', type); // Add 'sent' or 'received' class

            const messageText = document.createElement('div');
            messageText.textContent = message;

            const sentAtText = document.createElement('div');
            sentAtText.textContent = new Date(sentAt).toLocaleString();  // Formatting the sent time
            sentAtText.classList.add('sent-at');

            messageDiv.appendChild(messageText);
            messageDiv.appendChild(sentAtText);
            document.getElementById('chatMessages').appendChild(messageDiv);
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
        }

        document.getElementById('sendButton').addEventListener('click', sendMessage);
        document.getElementById('messageInput').addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission if the input is inside a form
                sendMessage(); // Call the sendMessage function when Enter is pressed
            }
        });

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            if (message && selectedUserId) {
                const payload = {
                    sender_id: <?php echo $user_id; ?>, // Use the logged-in user ID
                    receiver_id: selectedUserId, // Use the selected user ID
                    message: message,
                    sent_at: new Date().toISOString() // Send the current timestamp
                };

                // Send the message to the WebSocket server first
                ws.send(JSON.stringify(payload));

                // Once sent via WebSocket, then save the message in the JSON file
                fetch('save_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addMessage(message, 'sent', new Date().toLocaleString());
                        messageInput.value = ''; // Clear the input after sending the message
                    } else {
                        console.error(data.error);
                    }
                })
                .catch(error => console.error('Error sending message:', error));
            }
        }
    </script>
</body>
</html>
