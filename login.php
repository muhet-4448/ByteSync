<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bytesync"; // Your database name

// Establishing the MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize inputs to prevent XSS and SQL injection
    $inputUsername = htmlspecialchars(trim($_POST['username']));
    $inputPassword = trim($_POST['password']);

    // Check if the username and password are not empty
    if (!empty($inputUsername) && !empty($inputPassword)) {
        
        // Prevent SQL injection using prepared statements
        $stmt = $conn->prepare("SELECT id, username, password FROM iplogin WHERE username = ?");
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Debug: Check the fetched user and password
            error_log("Fetched user: " . print_r($user, true)); // Log the user details

            // Direct password comparison since it's stored in plain text
            if ($inputPassword === $user['password']) {
                // Password is correct, start the session
                $_SESSION['user_id'] = $user['id']; // Store user_id in session
                header("Location: profile.html"); // Redirect to profile page
                exit();
            } else {
                // Invalid password
                echo "<script>alert('Invalid Username or Password!'); window.location.href = 'ip.html';</script>";
            }
        } else {
            // Invalid username
            echo "<script>alert('Invalid Username or Password!'); window.location.href = 'ip.html';</script>";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Empty username or password
        echo "<script>alert('Please enter both username and password!'); window.location.href = 'ip.html';</script>";
    }
}

// Close the database connection
$conn->close();
?>
