<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowStock - Gestion de Stock</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(45deg, #ffd700, #ffaa00);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Custom Navbar Styles */
        .navbar-custom {
            background: var(--primary-gradient);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white !important;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
            color: white !important;
        }

        .logo-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .logo-k, .logo-s {
            position: absolute;
            font-weight: 900;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .logo-k {
            color: #667eea;
            transform: translateX(-3px);
            z-index: 2;
        }

        .logo-s {
            color: #764ba2;
            transform: translateX(3px);
            z-index: 1;
        }

        .navbar-brand:hover .logo-k {
            transform: translateX(-5px) scale(1.1);
        }

        .navbar-brand:hover .logo-s {
            transform: translateX(5px) scale(1.1);
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 10px 15px !important;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .dropdown-menu {
            border: none;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            background: white;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 4px;
        }

        .dropdown-item:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(5px);
        }

        .navbar-toggler {
            border: 2px solid white;
            padding: 4px 8px;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Hero Section */
        .hero {
            background: var(--primary-gradient);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-top: 76px; /* Account for fixed navbar */
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="1" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateX(0px) translateY(0px); }
            50% { transform: translateX(10px) translateY(-10px); }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            animation: slideInUp 1s ease-out;
        }

        .highlight {
            background: var(--secondary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 2s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.3); }
        }

        .hero-subtitle {
            font-size: 1.3rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            animation: slideInUp 1s ease-out 0.2s both;
        }

        .btn-hero-primary {
            background: var(--secondary-gradient);
            color: #333;
            border: none;
            font-weight: 600;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
            color: #333;
        }

        .btn-hero-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
            font-weight: 600;
            padding: 13px 30px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-hero-secondary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        /* Floating Cards */
        .floating-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            width: 180px;
            animation: floatCard 3s ease-in-out infinite;
            position: absolute;
        }

        .floating-card:nth-child(1) {
            top: 20px;
            right: 50px;
            animation-delay: 0s;
        }

        .floating-card:nth-child(2) {
            top: 150px;
            left: 30px;
            animation-delay: 1s;
        }

        .floating-card:nth-child(3) {
            bottom: 50px;
            right: 20px;
            animation-delay: 2s;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(2deg); }
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .floating-card h3 {
            color: #333;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .floating-card p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background: #f8fafc;
        }

        .section-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 4rem;
            animation: fadeInUp 1s ease-out;
        }

        .feature-card {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease-out;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .feature-card h3 {
            color: #333;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer h3 {
            color: #ffd700;
            margin-bottom: 1rem;
        }

        .footer a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #ffd700;
        }

        .social-links a {
            font-size: 1.5rem;
            margin: 0 10px;
        }

        .footer-bottom {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero {
                padding: 80px 0 60px;
                text-align: center;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .floating-card {
                position: relative;
                margin: 20px auto;
                top: auto !important;
                left: auto !important;
                right: auto !important;
                bottom: auto !important;
            }

            .hero-visual {
                margin-top: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#accueil">
                <div class="logo-icon">
                    <span class="logo-k">K</span>
                    <span class="logo-s">S</span>
                </div>
                <span class="logo-text">KnowStock</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            Connexion
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                            </a></li>
                            <li><a class="dropdown-item" href="register">
                                <i class="bi bi-person-plus me-2"></i>Créer un compte
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                           
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="propos">
                            <i class="bi bi-info-circle me-1"></i>
                            À propos
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="accueil">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">
                        Gérez votre stock avec
                        <span class="highlight">KnowStock</span>
                    </h1>
                    <p class="hero-subtitle">
                        La solution intelligente pour optimiser votre inventaire, 
                        suivre vos produits et booster vos performances commerciales.
                    </p>
                    
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual position-relative" style="height: 400px;">
                        <div class="floating-card">
                            <div class="card-icon">📊</div>
                            <h3>Analytics</h3>
                            <p>Suivez vos performances</p>
                        </div>
                        <div class="floating-card">
                            <div class="card-icon">📦</div>
                            <h3>Inventaire</h3>
                            <p>Gestion en temps réel</p>
                        </div>
                        <div class="floating-card">
                            <div class="card-icon">🚀</div>
                            <h3>Optimisation</h3>
                            <p>Automatisation intelligente</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title text-center">Pourquoi choisir KnowStock ?</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Rapide & Efficace</h3>
                        <p>Interface intuitive pour une gestion de stock simplifiée et rapide.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">🌐</div>
                        <h3>Multi-plateforme</h3>
                        <p>Accessible depuis n'importe quel appareil, partout dans le monde.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Suivi Détaillé</h3>
                        <p>Visualisez l'état de votre stock en temps réel avec des rapports clairs.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">🔍</div>
                        <h3>Gestion Intelligente</h3>
                        <p>Catégorisez vos produits et retrouvez-les facilement en un clin d'œil.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Sécurité des Données</h3>
                        <p>Vos informations sont protégées avec des protocoles de sécurité avancés.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">🔔</div>
                        <h3>Alertes Personnalisées</h3>
                        <p>Recevez des notifications pour les faibles niveaux de stock ou les ruptures.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h3>KnowStock</h3>
                    <p>La solution intelligente pour optimiser votre inventaire.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <h3>Liens Rapides</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#accueil">Accueil</a></li>
                        <li class="mb-2"><a href="#login">Connexion</a></li>
                        <li class="mb-2"><a href="#about">À propos</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-12 mb-4">
                    <h3>Nous Contacter</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            Adresse: CAJY services sarl, Cotonou, Bénin
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            Téléphone: <a href="tel:+22901407501221">+229 01 40 75 01 21</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            Email: <a href="mailto:info@cajyservicesarl.com">info@cajyservicesarl.com</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock me-2"></i>
                            Disponibilité: Lun-Ven, 8h-18h
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom text-center">
                &copy; 2025 KnowStock. Tous droits réservés.
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 76; // Account for fixed navbar
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.2}px)`; 
            }
        });

        // Animation observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const featureCards = entry.target.querySelectorAll('.feature-card');
                    featureCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, index * 200);
                    });
                }
            });
        }, observerOptions);

        // Initialize animations
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('.features');
            sections.forEach(section => observer.observe(section));
            
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';
            });
        });

        // Handle dropdown links (you can customize these actions)
        document.querySelector('a[href="#login"]').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Redirection vers la page de connexion');
            // window.location.href = '/login'; // Uncomment for actual navigation
        });

        document.querySelector('a[href="#register"]').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Redirection vers la page d\'inscription');
            // window.location.href = '/register'; // Uncomment for actual navigation
        });

        document.querySelector('a[href="#espace-client"]').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Redirection vers l\'espace client');
            // window.location.href = '/dashboard'; // Uncomment for actual navigation
        });
    </script>
</body>
</html>