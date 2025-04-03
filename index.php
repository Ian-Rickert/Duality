<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duality - Connecting Generations</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            line-height: 1.6;
            color: #333;
        }

        .navbar {
            background: white;
            padding: 1rem 5%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
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

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 5%;
            color: white;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .button {
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .primary-button {
            background: white;
            color: #667eea;
        }

        .secondary-button {
            border: 2px solid white;
            color: white;
        }

        .features {
            padding: 5rem 5%;
            background: #f8f9fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .feature-card h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }

        .testimonials {
            padding: 5rem 5%;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            font-style: italic;
        }

        .testimonial-author {
            margin-top: 1rem;
            font-style: normal;
            font-weight: bold;
        }

        footer {
            background: #333;
            color: white;
            padding: 2rem 5%;
            text-align: center;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .nav-links {
                display: none;
            }
        }
        
        html {
            scroll-behavior: smooth;
        }

    </style>
</head>
<body>
    <nav class="navbar">
    <a href="index.php" class="logo"><img src="logo.png" alt="Duality Logo" style="width: 70px; height: 70px;;"></a>
        <div class="nav-links">
            <a href="#features">How It Works</a>
            <a href="#testimonials">Stories</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="browse.php">Browse</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signUp.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Bridging Generations, Sharing Knowledge</h1>
            <p>Connect with wisdom and innovation. A platform where seniors share life experience while teens teach technology skills. Together, we create meaningful connections that enrich both lives.</p>
            <div class="cta-buttons">
                <a href="signUp.php" class="button primary-button">Join Our Community</a>
                <a href="#features" class="button secondary-button">Learn More</a>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="features-grid">
            <div class="feature-card">
                <h3>Life Skills Mentoring</h3>
                <p>Seniors share valuable life experiences, career advice, and practical skills developed over decades.</p>
            </div>
            <div class="feature-card">
                <h3>Tech Training</h3>
                <p>Teens help seniors master modern technology, from smartphones to social media and beyond.</p>
            </div>
            <div class="feature-card">
                <h3>Meaningful Connections</h3>
                <p>Build lasting relationships while breaking age barriers and fostering mutual understanding.</p>
            </div>
        </div>
    </section>

    <section class="testimonials" id="testimonials">
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>"As a retiree, teaching young people about financial planning and career development gives me purpose. In return, they've helped me master video calls to stay connected with my grandchildren."</p>
                <div class="testimonial-author">- Margaret, 68</div>
            </div>
            <div class="testimonial-card">
                <p>"My mentor taught me invaluable life lessons about perseverance and work ethic. Meanwhile, I helped him set up his first Instagram account to share his woodworking hobby!"</p>
                <div class="testimonial-author">- Jason, 16</div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Duality. Connecting generations through shared learning.</p>
    </footer>
</body>
</html>
