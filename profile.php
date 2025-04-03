<?php
require_once 'config.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$user = [];
$interests = [];
$expertise = [];
$all_interests = ['Programming', 'Design', 'Marketing', 'Business', 'Art', 'Music', 'Photography', 'Writing', 'Teaching', 'Cooking', 'Sports', 'Travel'];
$all_expertise = ['PHP', 'JavaScript', 'Python', 'UI/UX', 'Digital Marketing', 'Project Management', 'Data Analysis', 'Machine Learning', 'Content Writing', 'Graphic Design', 'Video Editing', 'Social Media'];

try {

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: logout.php');
        exit();
    }


    $stmt = $pdo->prepare("SELECT interest FROM interests WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $interests = $stmt->fetchAll(PDO::FETCH_COLUMN);


    $stmt = $pdo->prepare("SELECT expertise FROM expertise WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $expertise = $stmt->fetchAll(PDO::FETCH_COLUMN);


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['interest'])) {
            $stmt = $pdo->prepare("INSERT INTO interests (user_id, first_name, last_name, interest) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $user['first_name'],
                $user['last_name'],
                $_POST['interest']
            ]);
            header('Location: profile.php');
            exit();
        } elseif (isset($_POST['expertise'])) {
            $stmt = $pdo->prepare("INSERT INTO expertise (user_id, first_name, last_name, expertise) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $user['first_name'],
                $user['last_name'],
                $_POST['expertise']
            ]);
            header('Location: profile.php');
            exit();
        }
    }


    $pdo->exec("CREATE TABLE IF NOT EXISTS user_interests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        interest VARCHAR(50) NOT NULL,
        UNIQUE KEY unique_user_interest (user_id, interest),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");


    $pdo->exec("CREATE TABLE IF NOT EXISTS user_expertise (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        expertise VARCHAR(50) NOT NULL,
        UNIQUE KEY unique_user_expertise (user_id, expertise),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    echo "An error occurred. Please try again later.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png" type="image/x-icon">
    <title>Profile - Duality</title>
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
            color: #333;
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

        .profile-container {
            max-width: 800px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        .profile-header {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-picture {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .profile-info {
            text-align: center;
        }

        .profile-info h1 {
            color: #2d3748;
            margin-bottom: 12px;
            font-size: 2.4em;
            font-weight: 600;
        }

        .profile-info p {
            font-size: 1.2em;
            color: #718096;
            margin-bottom: 0;
        }

        .section {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, #667eea, #764ba2);
            opacity: 0.7;
        }

        .section h2 {
            color: #2d3748;
            margin-bottom: 25px;
            font-size: 1.6em;
            font-weight: 600;
            padding-bottom: 15px;
            border-bottom: 1px solid #edf2f7;
        }

        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 25px;
        }

        .tag {
            background: #f7fafc;
            color: #4a5568;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.95rem;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #cbd5e0;
        }

        .add-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            opacity: 0.9;
        }

        .add-button:hover {
            transform: translateY(-2px);
            opacity: 1;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 1001;
            backdrop-filter: blur(5px);
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

        .modal h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.8em;
            text-align: center;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .close:hover {
            color: #667eea;
        }

        .search-input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .search-input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .options-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .option-item {
            padding: 14px 20px;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #edf2f7;
        }

        .option-item:hover {
            background-color: #f7fafc;
        }

        .option-item.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        @media (max-width: 768px) {
            .profile-header {
                padding: 30px 20px;
            }

            .profile-picture {
                width: 180px;
                height: 180px;
                margin-bottom: 25px;
            }

            .section {
                padding: 25px;
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
        <a href="index.php" class="logo"><img src="logo.png" alt="Duality Logo" style="width: 70px; height: 70px;;"></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="browse.php">Browse</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <img src="stockPfP.png" alt="Profile Picture" class="profile-picture">
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p>Age: <?php echo htmlspecialchars($user['age']); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Interests</h2>
            <div class="tag-container">
                <?php foreach($interests as $interest): ?>
                    <span class="tag"><?php echo htmlspecialchars($interest); ?></span>
                <?php endforeach; ?>
            </div>
            <button onclick="openModal('interestModal')" class="add-button">Add Interest</button>
        </div>

        <div class="section">
            <h2>Expertise</h2>
            <div class="tag-container">
                <?php foreach($expertise as $exp): ?>
                    <span class="tag"><?php echo htmlspecialchars($exp); ?></span>
                <?php endforeach; ?>
            </div>
            <button onclick="openModal('expertiseModal')" class="add-button">Add Expertise</button>
        </div>
    </div>

    <div id="interestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('interestModal')">&times;</span>
            <h2>Add Interest</h2>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search interests..." onkeyup="filterOptions('interest')">
                <div class="options-container" id="interestOptions">
                    <?php foreach($all_interests as $interest): ?>
                        <div class="option-item" onclick="selectOption(this, 'interest')"><?php echo htmlspecialchars($interest); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <form action="" method="post" id="interestForm">
                <input type="hidden" name="interest" id="selectedInterest">
                <button type="submit" name="add_interest" class="add-button">Add Interest</button>
            </form>
        </div>
    </div>

    <div id="expertiseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('expertiseModal')">&times;</span>
            <h2>Add Expertise</h2>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search expertise..." onkeyup="filterOptions('expertise')">
                <div class="options-container" id="expertiseOptions">
                    <?php foreach($all_expertise as $exp): ?>
                        <div class="option-item" onclick="selectOption(this, 'expertise')"><?php echo htmlspecialchars($exp); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <form action="" method="post" id="expertiseForm">
                <input type="hidden" name="expertise" id="selectedExpertise">
                <button type="submit" name="add_expertise" class="add-button">Add Expertise</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target.className === "modal") {
                event.target.style.display = "none";
            }
        }

        function filterOptions(type) {
            const searchInput = document.querySelector(`#${type}Modal .search-input`).value.toLowerCase();
            const options = document.querySelectorAll(`#${type}Options .option-item`);
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchInput) ? 'block' : 'none';
            });
        }

        function selectOption(element, type) {

            const options = document.querySelectorAll(`#${type}Options .option-item`);
            options.forEach(opt => opt.classList.remove('selected'));
            

            element.classList.add('selected');
            

            document.getElementById(`selected${type.charAt(0).toUpperCase() + type.slice(1)}`).value = element.textContent;
        }
    </script>
</body>
</html>
