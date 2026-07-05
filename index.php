<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FedAyede - Smart Course Allocation System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="landing-page">
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>FedAyede</span>
            </div>
            <div class="nav-links">
                <a href="#features" class="nav-link">Features</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#stats" class="nav-link">Statistics</a>
                <a href="#contact" class="nav-link">Contact</a>
                <a href="login.php" class="nav-cta">Sign In</a>
                <!-- <a href="lec_and_ad_login.php" class="nav-cta">Sign In</a> -->
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-star"></i>
                    <span>Trusted by 500+ Educational Institutions</span>
                </div>
                <h1 class="hero-title">
                    Revolutionize Your
                    Course Allocation
                    Process
                </h1>
                <p class="hero-description">
                    Transform your educational institution with our intelligent course allocation system. 
                    Streamline scheduling, optimize resources, and enhance student satisfaction with 
                    cutting-edge automation and analytics.
                </p>
                <div class="hero-actions">
                    <a href="login.php" class="btn-hero primary">
                        <i class="fas fa-rocket"></i>
                        Get Started Free
                    </a>
                    <a href="#demo" class="btn-hero secondary">
                        <i class="fas fa-play"></i>
                        Watch Demo
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">99.9%</span>
                        <span class="stat-label">Uptime</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">50K+</span>
                        <span class="stat-label">Students</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">2.5K+</span>
                        <span class="stat-label">Courses</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="dashboard-preview">
                    <div class="preview-header">
                        <div class="preview-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <div class="preview-title">Course Dashboard</div>
                    </div>
                    <div class="preview-content">
                        <div class="preview-stats">
                            <div class="mini-stat">
                                <div class="mini-stat-icon">📚</div>
                                <div class="mini-stat-info">
                                    <span class="mini-stat-number">1,247</span>
                                    <span class="mini-stat-label">Active Courses</span>
                                </div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-icon">👨‍🏫</div>
                                <div class="mini-stat-info">
                                    <span class="mini-stat-number">89</span>
                                    <span class="mini-stat-label">Lecturers</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-chart">
                            <div class="chart-bars">
                                <div class="chart-bar" style="height: 60%"></div>
                                <div class="chart-bar" style="height: 80%"></div>
                                <div class="chart-bar" style="height: 45%"></div>
                                <div class="chart-bar" style="height: 90%"></div>
                                <div class="chart-bar" style="height: 70%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="floating-cards">
                    <div class="floating-card card-1">
                        <i class="fas fa-calendar-check"></i>
                        <span>Smart Scheduling</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-chart-line"></i>
                        <span>Real-time Analytics</span>
                    </div>
                    <div class="floating-card card-3">
                        <i class="fas fa-users"></i>
                        <span>Student Management</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-background">
            <div class="bg-gradient"></div>
            <div class="bg-pattern"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Powerful Features for Modern Education</h2>
                <p class="section-description">
                    Everything you need to manage courses, students, and resources efficiently
                </p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="feature-title">AI-Powered Allocation</h3>
                    <p class="feature-description">
                        Intelligent algorithms automatically optimize course assignments based on 
                        lecturer expertise, student preferences, and resource availability.
                    </p>
                    <div class="feature-benefits">
                        <span class="benefit-tag">95% Efficiency</span>
                        <span class="benefit-tag">Zero Conflicts</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="feature-title">Advanced Analytics</h3>
                    <p class="feature-description">
                        Comprehensive dashboards and reports provide deep insights into 
                        course performance, student engagement, and resource utilization.
                    </p>
                    <div class="feature-benefits">
                        <span class="benefit-tag">Real-time Data</span>
                        <span class="benefit-tag">Custom Reports</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Mobile-First Design</h3>
                    <p class="feature-description">
                        Access your dashboard anywhere, anytime. Fully responsive design 
                        ensures seamless experience across all devices.
                    </p>
                    <div class="feature-benefits">
                        <span class="benefit-tag">100% Responsive</span>
                        <span class="benefit-tag">Offline Ready</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Enterprise Security</h3>
                    <p class="feature-description">
                        Bank-level security with role-based access control, data encryption, 
                        and compliance with educational data protection standards.
                    </p>
                    <div class="feature-benefits">
                        <span class="benefit-tag">GDPR Compliant</span>
                        <span class="benefit-tag">256-bit Encryption</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="feature-title">Seamless Integration</h3>
                    <p class="feature-description">
                        Connect with existing student information systems, learning management 
                        platforms, and third-party educational tools effortlessly.
                    </p>
                    <div class="feature-benefits">
                        <span class="benefit-tag">API Ready</span>
                        <span class="benefit-tag">50+ Integrations</span>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">Time-Saving Automation</h3>
                    <p class="feature-description">
                        Automate repetitive tasks like schedule generation, conflict resolution, 
                        and notification sending to save hours of manual work.
                    </p>
                    <div class="feature-benefits">
                        <span class="benefit-tag">80% Time Saved</span>
                        <span class="benefit-tag">Auto Notifications</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="stats-section">
        <div class="container">
            <div class="stats-content">
                <div class="stats-text">
                    <h2 class="stats-title">Trusted by Educational Leaders Worldwide</h2>
                    <p class="stats-description">
                        Join thousands of institutions that have transformed their course 
                        allocation process with FedAyede's innovative platform.
                    </p>
                    <div class="stats-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Reduce scheduling conflicts by 95%</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Save 20+ hours per semester</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Increase student satisfaction by 40%</span>
                        </div>
                    </div>
                </div>
                <div class="stats-numbers">
                    <div class="stat-box">
                        <div class="stat-icon">🏫</div>
                        <div class="stat-value" data-target="500">0</div>
                        <div class="stat-label">Institutions</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon">👨‍🎓</div>
                        <div class="stat-value" data-target="50000">0</div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon">📚</div>
                        <div class="stat-value" data-target="25000">0</div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-value" data-target="98">0</div>
                        <div class="stat-label">Satisfaction %</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Users Say</h2>
                <p class="section-description">
                    Real feedback from real institutions using FedAyede
                </p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">
                            "FedAyede has completely transformed how we manage course allocations. 
                            What used to take weeks now takes just hours, and the accuracy is incredible."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="/placeholder.svg?height=50&width=50" alt="Dr. Sarah Johnson">
                        </div>
                        <div class="author-info">
                            <div class="author-name">Dr. Sarah Johnson</div>
                            <div class="author-title">Academic Director, Stanford University</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">
                            "The analytics dashboard gives us insights we never had before. 
                            We can now make data-driven decisions about our course offerings."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="/placeholder.svg?height=50&width=50" alt="Prof. Michael Chen">
                        </div>
                        <div class="author-info">
                            <div class="author-name">Prof. Michael Chen</div>
                            <div class="author-title">Dean of Engineering, MIT</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">
                            "Student satisfaction has increased dramatically since we started using FedAyede. 
                            The automated scheduling eliminates conflicts and optimizes learning paths."
                        </p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="/placeholder.svg?height=50&width=50" alt="Dr. Emily Rodriguez">
                        </div>
                        <div class="author-info">
                            <div class="author-name">Dr. Emily Rodriguez</div>
                            <div class="author-title">Registrar, Harvard University</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Transform Your Institution?</h2>
                <p class="cta-description">
                    Join thousands of educational institutions that have revolutionized their 
                    course allocation process with FedAyede.
                </p>
                <div class="cta-actions">
                    <a href="login.php" class="btn-cta primary">
                        <i class="fas fa-rocket"></i>
                        Start For Free
                    </a>
                    <a href="#contact" class="btn-cta secondary">
                        <i class="fas fa-calendar"></i>
                        Schedule Demo
                    </a>
                </div>
                <div class="cta-features">
                    <div class="cta-feature">
                        <i class="fas fa-check"></i>
                        <span>No More 30-day free trial</span>
                    </div>
                    <div class="cta-feature">
                        <i class="fas fa-check"></i>
                        <span>No credit card required</span>
                    </div>
                    <div class="cta-feature">
                        <i class="fas fa-check"></i>
                        <span>24/7 support included</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="brand-logo">
                        <i class="fas fa-graduation-cap"></i>
                        <span>FedAyede</span>
                    </div>
                    <p class="brand-description">
                        Revolutionizing education through intelligent course allocation 
                        and resource management.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h4 class="link-title">Product</h4>
                        <a href="#features" class="footer-link">Features</a>
                        <a href="#" class="footer-link">Pricing</a>
                        <a href="#" class="footer-link">Integrations</a>
                        <a href="#" class="footer-link">API</a>
                    </div>
                    <div class="link-group">
                        <h4 class="link-title">Company</h4>
                        <a href="#about" class="footer-link">About</a>
                        <a href="#" class="footer-link">Careers</a>
                        <a href="#" class="footer-link">Press</a>
                        <a href="#" class="footer-link">Blog</a>
                    </div>
                    <div class="link-group">
                        <h4 class="link-title">Support</h4>
                        <a href="#" class="footer-link">Help Center</a>
                        <a href="#contact" class="footer-link">Contact</a>
                        <a href="#" class="footer-link">Documentation</a>
                        <a href="#" class="footer-link">Status</a>
                    </div>
                    <div class="link-group">
                        <h4 class="link-title">Legal</h4>
                        <a href="#" class="footer-link">Privacy</a>
                        <a href="#" class="footer-link">Terms</a>
                        <a href="#" class="footer-link">Security</a>
                        <a href="#" class="footer-link">Compliance</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="copyright">
                    © 2025 FedAyede. All rights reserved. Built with ❤️ for education.
                </p>
            </div>
        </div>
    </footer>

    <script src="assets/js/landing.js"></script>
</body>
</html>
