<?php 
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection settings
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

// Check if the user is logged in, and fetch the user data
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

    // Fetch user profile data from the database
    $stmt = $conn->prepare("SELECT * FROM profile WHERE id = ?");
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error); // Check for errors in prepare
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch the user data
        // Ensure the values are assigned
        if ($user) {
            $first_name = $user['first_name'];
            $last_name = $user['last_name'];
            $email = $user['email'];
            $phone_number = $user['phone_number'];
            $bio = $user['bio'];
            $profile_picture = $user['profile_picture'];
        } else {
            echo "User data not found!";
            exit();
        }
    } else {
        echo "No user found with user_id: " . $user_id;
        exit();
    }

    $stmt->close();
} else {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}



// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Sanitize inputs
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $bio = $_POST['bio'];

    // Update profile data in the database
    $stmt = $conn->prepare("UPDATE profile SET first_name = ?, last_name = ?, email = ?, phone_number = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone_number, $bio, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Reload the updated profile data
        header("Location: profile.php");
        exit();
    } else {
        echo "<script>alert('No changes made or error updating the profile.');</script>";
    }

    $stmt->close();
}

// Handle profile deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_profile'])) {
    // Delete the user profile
    $stmt = $conn->prepare("DELETE FROM profile WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        session_destroy(); // Destroy session after deletion
        header("Location: login.php"); // Redirect to login page after deletion
        exit();
    } else {
        echo "<script>alert('Error deleting profile.');</script>";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
