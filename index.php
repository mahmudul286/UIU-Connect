<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Connect - The Smart Campus OS</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <!-- FontAwesome Added for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body class="landing-body">

    <nav class="navbar">
        <div class="logo"><a href="#hero">UIU<span>Connect</span></a></div>
        <div class="logo"></div>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#reviews">Reviews</a>
            <a href="login.php" id="nav-login">Sign In</a>
            <a href="register.php" class="btn-primary" style="color: white;">Get Started</a>
        </div>
    </nav>

    <header id="hero" class="hero">
        <div class="hero-text">
            <h1>The Operating System for Your Campus.</h1>
            <p>Unify your academic life. Access course materials, resolve queries in the Q&A forum, find emergency blood
                donors, and buy/sell campus essentials—all in one secure platform designed exclusively for UIU.</p>
            <div style="display: flex; gap: 15px;">
                <a href="register.php" class="btn-primary" style="padding: 14px 30px; font-size: 16px;">Create Free
                    Account</a>
                <a href="login.php" class="btn-outline" style="padding: 14px 30px; font-size: 16px;">Log In</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero_bg.jpg" alt="Students collaborating">
        </div>

    </header>

    <section class="stats-section">
        <div class="stat-box">
            <h2>8,500+</h2>
            <p>Active Students</p>
        </div>
        <div class="stat-box">
            <h2>300+</h2>
            <p>Faculty Members</p>
        </div>
        <div class="stat-box">
            <h2>1,200+</h2>
            <p>Study Materials</p>
        </div>
        <div class="stat-box">
            <h2>24/7</h2>
            <p>Campus Support</p>
        </div>
    </section>

    <section id="features" class="features">
        <h2 class="section-title">Everything you need to succeed</h2>
        <p class="section-subtitle">We've replaced scattered Facebook groups and outdated notice boards with a
            streamlined, purpose-built academic ecosystem.</p>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-regular fa-newspaper" style="font-size: 24px;"></i>
                </div>
                <h3>Centralized Feed</h3>
                <p>Stay updated with the latest campus news, faculty announcements, and department events in a clean,
                    professional feed.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-regular fa-comments" style="font-size: 24px;"></i>
                </div>
                <h3>Academic Q&A Forum</h3>
                <p>Stuck on an algorithm or circuit diagram? Ask questions, share code snippets, and get answers
                    directly from peers and faculty.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-regular fa-heart" style="font-size: 24px;"></i>
                </div>
                <h3>Smart Blood Bank</h3>
                <p>A life-saving real-time directory. Search for available blood donors across all departments and
                    contact them instantly during emergencies.</p>
            </div>
        </div>
    </section>

    <section id="reviews" class="reviews">
        <h2 class="section-title" style="text-align: center;">Loved by UIU Students & Faculty</h2>
        <p class="section-subtitle" style="text-align: center;">See how UIU Connect is changing the campus experience.
        </p>

        <div class="review-grid">
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p style="color: var(--text-main); line-height: 1.6; font-size: 15px;">"Finally, an organized place for course materials! Before UIU Connect, finding previous trimesters' slides was a nightmare. The Q&A forum is a lifesaver before midterms."</p>
                <div class="reviewer-info">
                    <img src="https://ui-avatars.com/api/?name=Farhan+Islam&background=F26522&color=fff" alt="Farhan">
                    <div>
                        <strong style="display: block; font-size: 15px;">Farhan Islam</strong>
                        <small style="color: var(--text-muted);">8th trimester, CSE</small>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p style="color: var(--text-main); line-height: 1.6; font-size: 15px;">"The Blood Bank module is
                    incredibly well-thought-out. Last week, we urgently needed O-negative blood and found a donor from
                    the BBA Department within 5 minutes using the directory."</p>
                <div class="reviewer-info">
                    <img src="https://ui-avatars.com/api/?name=Nadia+Rahman&background=0F172A&color=fff" alt="Nadia">
                    <div>
                        <strong style="display: block; font-size: 15px;">Nadia Rahman</strong>
                        <small style="color: var(--text-muted);">7th trimester, CSE</small>
                    </div>
                </div>
            </div>

            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p style="color: var(--text-main); line-height: 1.6; font-size: 15px;">"As a faculty member, this
                    platform makes announcements so much easier. I can directly upload notes and answer student queries asynchronously without relying on chaotic Messenger groups."</p>
                <div class="reviewer-info">
                    <img src="https://ui-avatars.com/api/?name=Dr+Shafiq&background=cbd5e1&color=0f172a" alt="Faculty">
                    <div>
                        <strong style="display: block; font-size: 15px;">Dr. Shafiq Mahmud</strong>
                        <small style="color: var(--text-muted);">Professor, CSE Dept.</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <h2>Ready to upgrade your campus life?</h2>
        <p>Join thousands of students and faculty members already using UIU Connect.</p>
        <a href="register.php" class="cta-btn">Join UIU Connect Today</a>
    </section>

    <footer class="footer">
        <div class="footer-grid">
            <div>
                <div class="footer-logo">UIU<span>Connect</span></div>
                <p style="font-size: 14px; line-height: 1.6; max-width: 300px;">A connected campus community, built by students, for students of United International University</p>
            </div>
            <div>
                <h4>Platform</h4>
                <ul>
                    <li><a href="#">News Feed</a></li>
                    <li><a href="#">Q&A Forum</a></li>
                    <li><a href="#">Blood Bank</a></li>
                    <li><a href="#">Marketplace</a></li>
                </ul>
            </div>
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Report an Issue</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        <div style="text-align: center; font-size: 14px; color: #64748b;">
            &copy; <?php echo date("Y"); ?> UIU Connect Team. All rights reserved.
        </div>
    </footer>

</body>

</html>