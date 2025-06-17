<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos - Notre Univers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            z-index: 1000;
            padding: 0 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-link {
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: #6366f1;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 80%;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #374151;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        .hero-content {
            text-align: center;
            color: white;
            z-index: 2;
            max-width: 900px;
            padding: 0 2rem;
        }

        .hero h1 {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.9);
            color: #6366f1;
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border-color: rgba(255, 255, 255, 0.5);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            background: white;
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Sections */
        .section {
            padding: 120px 0;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .section-title {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 1rem;
            color: #1f2937;
            font-weight: 700;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Qui sommes-nous */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 4rem;
        }

        .about-text h3 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }

        .about-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4b5563;
            margin-bottom: 1.5rem;
        }

        .about-visual {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border-radius: 20px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: #6366f1;
            position: relative;
            overflow: hidden;
        }

        .about-visual::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            animation: rotate 4s linear infinite;
        }

        /* Services */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .service-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
        }

        .service-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .service-card p {
            color: #6b7280;
            line-height: 1.7;
        }

        /* Projets */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .project-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .project-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
        }

        .project-content {
            padding: 2rem;
        }

        .project-content h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .project-content p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .project-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tag {
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 2rem 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .about-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="">
                    <img src="{{ asset('assets/img/images (2).jpg') }}" alt="" style="height: 120px;">
                </a>
            </div>

            <ul class="nav-menu" id="nav-menu">
                <li><a href="#qui-sommes-nous" class="nav-link">Qui sommes-nous</a></li>
                <li><a href="#services" class="nav-link">Services</a></li>
                <li><a href="#projets" class="nav-link">Projets</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Nous Créons l'Avenir</h1>
            <p class="hero-subtitle">Innovation, Excellence et Passion au service de vos projets</p>
            <div class="cta-buttons">
                <a href="#qui-sommes-nous" class="btn btn-primary">Découvrir</a>
                <a href="#contact" class="btn btn-secondary">Nous Contacter</a>
            </div>
        </div>
    </section>

    <!-- Qui sommes-nous -->
    <section id="qui-sommes-nous" class="section">
        <h2 class="section-title">Qui Sommes-Nous</h2>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px; margin-top: 40px;">
            <img src="assets/img/images 1.jpg" alt="Membre 1" style="height: 150px; border-radius: 10px;">
            <img src="assets/img/images2.jpg" alt="Membre 2" style="height: 150px; border-radius: 10px;">
            <img src="assets/img/images3.jpg" alt="Membre 3" style="height: 150px; border-radius: 10px;">
            <img src="assets/img/images4.jpg" alt="Membre 4" style="height: 150px; border-radius: 10px;">
            <img src="assets/img/imag.jpg" alt="Membre 5" style="height: 150px; border-radius: 10px;">

        </div><br><br>

        <p class="section-subtitle">Une équipe passionnée dédiée à transformer vos idées en réalité</p>

        <div class="about-grid">
            <div class="about-text">
                <h3>Notre Mission</h3>
                <p> <b>CAJY Services</b> vous accompagne dans la réussite de vos projets, en conformité avec la
                    réglementation. Notre mission va bien au-delà d’une offre experte et complète des produits
                    d’excellence, parce que nous veillons toujours à mieux satisfaire vos exigences, en vous bâtissant
                    des solutions innovantes, adaptées à des services personnalisés..</p>
                <p>Depuis notre création, nous avons accompagné plus de 200 entreprises dans leur transformation
                    digitale, en proposant des solutions qui allient performance, esthétisme et facilité d'utilisation.
                </p>
            </div>
            <div class="about-visual">
                <img src="assets/img/images (3).jpg" alt="Membre 5" style="height:450px; ">
            </div>
        </div>

        <div class="about-grid">
            <div class="about-visual">
                💡
            </div>
            <div class="about-text">
                <h3>Notre Vision</h3>
                <p>Nous croyons que la technologie doit servir l'humain et améliorer son quotidien. C'est pourquoi nous
                    mettons l'utilisateur au centre de chaque projet, en créant des expériences intuitives et
                    engageantes.</p>
                <p>Notre approche collaborative nous permet de comprendre parfaitement vos besoins et de proposer des
                    solutions personnalisées qui dépassent vos attentes.</p>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section id="services" class="section" style="background: #f8fafc;">
        <h2 class="section-title">Nos Services</h2>
        <p class="section-subtitle">Des solutions complètes pour tous vos besoins digitaux</p>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">🎨</div>
                <h3></h3>
                <p>Distribution de caisse enregistreuses et gab
                    Nous assurons la distribution , l'installation et la maintenance de caisses enregistreuses et GAB au
                    Bénin et dans la sous région.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">⚡</div>
                
                <p>Géolocalisation des véhicules
                    En utilisant la technologie GPS et GPRS par le biais de liaisons satellitaires nous offrons le
                    suivie de vos véhicules partout dans le monde..</p>
            </div>
            <div class="service-card">
                <div class="service-icon">📱</div>
              
                <p>Marketing et communication
                    Cajy est la bonne adresse pour la promotion de votre marque, votre entreprise et de vos produits et
                    ceci travers de branding, affiches, directory, signalétiques, enseignes etc ...</p>
            </div>
            <div class="service-card">
                <div class="service-icon">🔧</div>
                
                <p>Intermédiation de Services
                    Ce service consiste à se poser en intermédiaire entre acheteurs et offreurs de produits et services,
                    en se rémunérant avec une commission sur transaction et/ou sur services additionnels..</p>
            </div>
            <div class="service-card">
                <div class="service-icon">🛡️</div>
                
                <p>Support technique continu, mises à jour de sécurité et maintenance préventive pour assurer la
                    pérennité de vos solutions.</p>
            </div>
            <div class="service-card">
                
               
                <p>Vidéo-surveillance et contrôle d'accès
                    Selon les besoins de nos clients, ou bien les caractéristiques d’un lieu que nous avons visité, nous
                    choisissons un dispositif de vidéosurveillance adéquat au contexte et aux besoins..</p>
            </div>
        </div>
    </section>

    <!-- Projets -->
    <section id="projets" class="section">
        <h2 class="section-title">Nos Projets</h2>
        <p class="section-subtitle">Découvrez quelques-unes de nos réalisations récentes</p>

        <div class="projects-grid">
            <div class="project-card">
                <div class="project-image">E</div>
                <div class="project-content">
                    <h3>Plateforme E-commerce</h3>
                    <p>Développement d'une plateforme e-commerce complète avec système de paiement intégré, gestion des
                        stocks et interface d'administration avancée.</p>
                    <div class="project-tags">
                        <span class="tag">React</span>
                        <span class="tag">Node.js</span>
                        <span class="tag">MongoDB</span>
                    </div>
                </div>
            </div>
            <div class="project-card">
                <div class="project-image">A</div>
                <div class="project-content">
                    <h3>Application Mobile Santé</h3>
                    <p>Conception et développement d'une application mobile de suivi médical avec notifications
                        intelligentes et synchronisation cloud.</p>
                    <div class="project-tags">
                        <span class="tag">React Native</span>
                        <span class="tag">Firebase</span>
                        <span class="tag">AI/ML</span>
                    </div>
                </div>
            </div>
            <div class="project-card">
                <div class="project-image">D</div>
                <div class="project-content">
                    <h3>Dashboard Analytics</h3>
                    <p>Création d'un tableau de bord analytique en temps réel pour le suivi des performances
                        commerciales avec visualisations interactives.</p>
                    <div class="project-tags">
                        <span class="tag">Vue.js</span>
                        <span class="tag">D3.js</span>
                        <span class="tag">Python</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Navigation active
        const navLinks = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('section[id]');

        function setActiveLink() {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        }

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            setActiveLink();
        });

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('nav-menu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Close mobile menu on link click
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });

        // Smooth scroll
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.service-card, .project-card, .about-text, .about-visual').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>

</html>