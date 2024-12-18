<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; // Set user session
        header('Location: home.php'); // Redirect to home page
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
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
            border: 1px solid #ccc; /* Added border sa form */
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Form title styling */
        h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Input fields styling */
        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            margin-bottom: 15px;
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
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
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

<form action="sign_in.php" method="POST">
    <h2>Sign In</h2>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    
    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Sign In</button>

    <p class="create-account">Don't have an account? <a href="create_account.php">Create Account</a></p> <!-- Create Account link inside the form -->
</form>

</body>
</html>
