<?php
require_once 'config.php';
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$interest_filter = isset($_GET['interest']) ? $_GET['interest'] : '';
$min_age = isset($_GET['min_age']) ? (int)$_GET['min_age'] : '';
$max_age = isset($_GET['max_age']) ? (int)$_GET['max_age'] : '';
$expertise_filter = isset($_GET['expertise']) ? $_GET['expertise'] : '';

try {

    $query = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.age, u.email, 
              GROUP_CONCAT(DISTINCT e.expertise) as expertise_list 
              FROM users u 
              LEFT JOIN expertise e ON u.id = e.user_id";
    

    if (!empty($interest_filter)) {
        $query .= " LEFT JOIN interests i ON u.id = i.user_id";
    }
    

    $query .= " WHERE u.id != :user_id";
    
  
    if (!empty($interest_filter)) {
        $query .= " AND i.interest = :interest";
    }
    if (!empty($min_age)) {
        $query .= " AND u.age >= :min_age";
    }
    if (!empty($max_age)) {
        $query .= " AND u.age <= :max_age";
    }
    if (!empty($expertise_filter)) {
        $query .= " AND EXISTS (SELECT 1 FROM expertise WHERE user_id = u.id AND expertise = :expertise)";
    }
    
    $query .= " GROUP BY u.id";
    
    $stmt = $pdo->prepare($query);
    

    $params = [':user_id' => $_SESSION['user_id']];
    if (!empty($interest_filter)) {
        $params[':interest'] = $interest_filter;
    }
    if (!empty($min_age)) {
        $params[':min_age'] = $min_age;
    }
    if (!empty($max_age)) {
        $params[':max_age'] = $max_age;
    }
    if (!empty($expertise_filter)) {
        $params[':expertise'] = $expertise_filter;
    }
    
    $stmt->execute($params);
    $users = $stmt->fetchAll();


    $stmt = $pdo->query("SELECT DISTINCT interest FROM interests ORDER BY interest");
    $all_interests = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->query("SELECT DISTINCT expertise FROM expertise ORDER BY expertise");
    $all_expertise = $stmt->fetchAll(PDO::FETCH_COLUMN);


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
        $to = $_POST['recipient_email'];
        $subject = "New message from Duality";
        $message = $_POST['message'];
        
 
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $sender_email = $stmt->fetchColumn();
        

        $html_message = "<html><body>";
        $html_message .= "<p>You have received a new message from a Duality user:</p>";
        $html_message .= "<p>" . htmlspecialchars($message) . "</p>";
        $html_message .= "<p>To reply, please email: " . htmlspecialchars($sender_email) . "</p>";
        $html_message .= "</body></html>";

        try {
            $mail = new PHPMailer(true);
            

            $mail->SMTPDebug = 0; 
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer: $str");
            };
            

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'message.duality@gmail.com';
            $mail->Password = 'xcok ermv rcwd jfmx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
  
            $mail->setFrom('noreply@duality.com', 'Duality');
            $mail->addReplyTo($sender_email);
            $mail->addAddress($to);
            
 
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html_message;
            $mail->AltBody = strip_tags($message) . "\n\nTo reply, please email: " . $sender_email;
            

            error_log("Attempting to send email to: " . $to);
            error_log("From: " . $sender_email);
            
            $mail->send();
            $_SESSION['message'] = "Message sent successfully!";
            error_log("Email sent successfully");
        } catch (Exception $e) {
            error_log("Email Error: " . $mail->ErrorInfo);
            $_SESSION['message'] = "Failed to send message. Error: " . $mail->ErrorInfo;
        }
    }

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $error = "An error occurred while fetching users.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Users - Duality</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f6f8ff 0%, #e9ecef 100%);
            padding: 20px;
        }

        .navbar {
            background: white;
            padding: 1rem 5%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            margin-left: 2rem;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            color: #2d3748;
            font-weight: 500;
        }

        select, input {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        select:focus, input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            opacity: 0.9;
        }

        button:hover {
            transform: translateY(-2px);
            opacity: 1;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .user-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .user-name {
            font-size: 1.4em;
            color: #2d3748;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .user-age {
            color: #718096;
            margin-bottom: 15px;
        }

        .expertise-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .expertise-tag {
            background: #f7fafc;
            color: #4a5568;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            border: 1px solid #e2e8f0;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(5px);
            z-index: 1001;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            margin: 100px auto;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #718096;
            transition: color 0.3s;
        }

        .close:hover {
            color: #2d3748;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin: 15px 0;
            min-height: 150px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        textarea:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .filter-section {
                padding: 20px;
            }

            .user-card {
                padding: 20px;
            }

            .modal-content {
                margin: 60px auto;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Duality</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="browse.php">Browse</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="filter-section">
            <form action="" method="get" class="filter-grid">
                <div class="filter-item">
                    <label for="interest">Interest</label>
                    <select name="interest" id="interest">
                        <option value="">All Interests</option>
                        <?php foreach($all_interests as $interest): ?>
                            <option value="<?php echo htmlspecialchars($interest); ?>" 
                                    <?php echo $interest_filter === $interest ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($interest); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="expertise">Expertise</label>
                    <select name="expertise" id="expertise">
                        <option value="">All Expertise</option>
                        <?php foreach($all_expertise as $expertise): ?>
                            <option value="<?php echo htmlspecialchars($expertise); ?>"
                                    <?php echo $expertise_filter === $expertise ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($expertise); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="min_age">Minimum Age</label>
                    <input type="number" name="min_age" id="min_age" value="<?php echo htmlspecialchars($min_age); ?>" min="14" max="100">
                </div>

                <div class="filter-item">
                    <label for="max_age">Maximum Age</label>
                    <input type="number" name="max_age" id="max_age" value="<?php echo htmlspecialchars($max_age); ?>" min="14" max="100">
                </div>

                <div class="filter-item">
                    <label>&nbsp;</label>
                    <button type="submit">Apply Filters</button>
                </div>
            </form>
        </div>

        <div class="users-grid">
            <?php foreach($users as $user): ?>
                <div class="user-card" onclick="openMessageModal('<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                    <img src="stockPfP.png" alt="Profile Picture" class="profile-pic">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                        <div class="user-age"><?php echo htmlspecialchars($user['age']); ?> years old</div>
                        <div class="expertise-tags">
                            <?php 
                            if ($user['expertise_list']) {
                                $expertise_array = explode(',', $user['expertise_list']);
                                foreach($expertise_array as $expertise): ?>
                                    <span class="expertise-tag"><?php echo htmlspecialchars($expertise); ?></span>
                                <?php endforeach;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMessageModal()">&times;</span>
            <h2>Send Message</h2>
            <form action="" method="post">
                <input type="hidden" name="recipient_email" id="recipient_email">
                <textarea name="message" placeholder="Type your message here..." required></textarea>
                <button type="submit" name="send_message">Send Message</button>
            </form>
        </div>
    </div>

    <script>
        function openMessageModal(email, name) {
            document.getElementById('messageModal').style.display = 'block';
            document.getElementById('recipient_email').value = email;
            document.querySelector('.modal-content h2').textContent = `Send Message to ${name}`;
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>
</body>
</html>
