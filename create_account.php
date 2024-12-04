<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Hash the password for secure storage
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the email is already registered
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Email is already registered.";
        } else {
            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashed_password]);

            // Redirect to the sign-in page after successful registration
            header('Location: sign_in.php');
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <style>
        /* General body styling */
body {
    background-color: #f4f4f4;
    font-family: 'Arial', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Form container styling */
form {
    background-color: #fff;
    padding: 30px;
    border: 1px solid #ccc; /* Added border around form */
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Shadow to make form stand out */
    width: 100%;
    max-width: 400px;
    text-align: left; /* Align text to the left */
}

/* Form title styling */
h2 {
    font-size: 28px;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center; /* Center the title */
}

/* Label and input container styling */
.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

label {
    font-size: 14px;
    color: #555;
    margin-bottom: 8px;
    display: inline-block;
}

/* Input styling */
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 0 0 15px 0; /* Equal margin only at bottom */
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    outline: none;
    box-sizing: border-box; /* Include padding and border in total width */
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

/* Focused input fields styling */
input[type="email"]:focus,
input[type="password"]:focus {
    border-color: #0056b3;
    box-shadow: 0 0 8px rgba(0, 86, 179, 0.3);
}

/* Submit button styling */
button[type="submit"] {
    width: 100%;
    padding: 14px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #218838;
}

/* Error message styling */
p {
    color: red;
    font-size: 14px;
    margin-bottom: 10px;
}

/* Create Account section styling */
.create-account {
    margin-top: 20px;
    font-size: 14px;
    color: #333;
}

.create-account a {
    text-decoration: none;
    color: #0056b3;
    font-weight: bold;
}

.create-account a:hover {
    text-decoration: underline;
}

/* Media query for responsiveness */
@media only screen and (max-width: 430px) {
    form {
        width: 90%;
        padding: 20px;
        margin-top: 20px;
    }

    h2 {
        font-size: 24px;
    }

    input[type="email"],
    input[type="password"] {
        padding: 10px;
        font-size: 14px;
    }

    button[type="submit"] {
        padding: 12px;
        font-size: 16px;
    }
}

    </style>
</head>
<body>

<form action="create_account.php" method="POST">
    <h2>Create Account</h2>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </div>

    <button type="submit">Create Account</button>

    <div class="create-account">
        <p>Already have an account? <a href="sign_in.php">Sign In</a></p>
    </div>
</form>

</body>
</html>
