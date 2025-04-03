<?php

require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($tableCheck->rowCount() == 0) {
            $pdo->exec("CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                age INT NOT NULL,
                password VARCHAR(255) NOT NULL
            )");
        }

        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
        $password = $_POST['password'] ?? '';

        if (empty($firstName)) {
            $errors[] = "First name is required";
        }
        if (empty($lastName)) {
            $errors[] = "Last name is required";
        }
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        if ($age < 14 || $age > 100) {
            $errors[] = "Age must be between 14 and 100";
        }
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already exists";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, age, password) VALUES (:firstName, :lastName, :email, :age, :passwordHash)");
                
                $result = $stmt->execute([
                    ':firstName' => $firstName,
                    ':lastName' => $lastName,
                    ':email' => $email,
                    ':age' => $age,
                    ':passwordHash' => $passwordHash
                ]);

                if ($result) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['email'] = $email;
                    $_SESSION['age'] = $age;
                    header('Location: profile.php');
                    exit();
                } else {
                    $errors[] = "Failed to create account";
                }
            }
        }
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $errors[] = "Email already exists";
        } else {
            $errors[] = "Registration failed: " . $e->getMessage();
            error_log("Database Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duality</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 480px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #667eea;
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .error {
            color: #ff4444;
            font-size: 14px;
            margin-top: 5px;
        }

        .success {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background-color: #ff4444;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Account</h1>
        
        <?php if ($success): ?>
            <div class="success">
                Registration successful! You can now log in.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" required minlength="2" 
                       value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" required minlength="2"
                       value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required min="14" max="100"
                       value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
