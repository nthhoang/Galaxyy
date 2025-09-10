<main>
    <section class="hero-section" id="hero">
        <video autoplay muted playsinline loop id="hero-video-background">
            <source src="/galaxy/images-icon/bgrgalaxy.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content container">
            <h1 class="animate-on-scroll">EXPLORE THE MYSTERIES OF THE INFINITE UNIVERSE</h1>
            <p class="lead text-light mb-4 animate-on-scroll" style="transition-delay: 0.2s;">
                A gateway to astronomical knowledge, the latest news, and a passionate space community.
            </p>
            <a href="#explore-section" class="cta-button animate-on-scroll" style="transition-delay: 0.4s;">Start the Journey</a>
        </div>
        <a href="#explore-section" class="scroll-down-indicator" aria-label="Scroll down"></a>
    </section>

    <section id="explore-section" class="py-5">
        <div class="container py-5">
            <h2 class="section-title animate-on-scroll">Your Cosmic Information Hub</h2>
            <p class="section-subtitle animate-on-scroll">Everything you need to fuel your passion for space exploration, all in one place.</p>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-satellite-dish"></i></div>
                        <h5 class="card-title text-white">Solar System</h5>
                        <p class="card-text">Journey through planets, moons, and uncover wonders within our cosmic neighborhood.</p>
                        <a href="/galaxy/hemattroi/mattroi.php" class="btn btn-outline-primary mt-3">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay: 0.1s;">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-book-open"></i></div>
                        <h5 class="card-title text-white">Cosmic Library</h5>
                        <p class="card-text">Explore a stunning collection of 4K images and videos of galaxies and nebulae shared by the community.</p>
                        <a href="/galaxy/galaxy_lib.php" class="btn btn-outline-primary mt-3">View Now</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay: 0.2s;">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-newspaper"></i></div>
                        <h5 class="card-title text-white">News & Events</h5>
                        <p class="card-text">Stay updated with the latest space news and never miss an upcoming astronomical event.</p>
                        <a href="/galaxy/tintuc.php" class="btn btn-outline-primary mt-3">Read News</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay: 0.3s;">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-users"></i></div>
                        <h5 class="card-title text-white">Community</h5>
                        <p class="card-text">Connect, share knowledge, and exchange breathtaking space images with fellow enthusiasts.</p>
                        <a href="/galaxy/congdong.php" class="btn btn-outline-primary mt-3">Join Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="library-showcase-section text-white text-center">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Image & 4K Video Library</h2>
            <p class="section-subtitle animate-on-scroll">Immerse yourself in the breathtaking beauty of the cosmos through high-quality, community-curated visuals.</p>
            <a href="/galaxy/galaxy_lib.php" class="cta-button animate-on-scroll" style="transition-delay: 0.2s;">Explore the Library</a>
        </div>
    </section>

    <section id="news-section" class="py-5">
        <div class="container py-5">
            <h2 class="section-title animate-on-scroll">Featured News</h2>
            <p class="section-subtitle animate-on-scroll">The latest discoveries and events from across the universe.</p>
            <div class="row g-4">
                <?php if (!empty($featured_news)): ?>
                    <?php 
                    $delay = 0;
                    foreach ($featured_news as $news): 
                    ?>
                        <div class="col-lg-4 col-md-6 animate-on-scroll" style="transition-delay: <?php echo $delay; ?>s;">
                            <div class="news-card">
                                <img src="<?php echo htmlspecialchars($news['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>">
                                <div class="card-body">
                                    <p class="news-meta">
                                        <span><i class="fas fa-calendar-alt"></i> 
                                            <?php echo date("d/m/Y", strtotime($news['created_at'])); ?>
                                        </span>
                                    </p>
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </h5>
                                    <?php if (!empty($news['excerpt'])): ?>
                                        <p class="card-text text-muted">
                                            <?php echo htmlspecialchars($news['excerpt']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <a href="/galaxy/view_news.php?id=<?php echo $news['id']; ?>" class="btn btn-sm btn-outline-light">
                                        Read More
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $delay += 0.1; 
                    endforeach; 
                    ?>
                <?php else: ?>
                    <p class="text-center text-muted">No news available</p>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5">
                <a href="/galaxy/tintuc.php" class="cta-button animate-on-scroll">View All News</a>
            </div>
        </div>
    </section>

    <section id="model3d-section" class="py-5" style="background-color: #111128;">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 animate-on-scroll">
                    <img src="/galaxy/images-icon/hemattroi.avif" alt="3D Solar System Model" class="img-fluid">
                </div>
                <div class="col-lg-6 animate-on-scroll" style="transition-delay: 0.1s;">
                    <h2 class="section-title text-start">Experience the 3D Solar System</h2>
                    <p class="text-white">Don’t just read—interact! Explore a realistic 3D model of the Solar System: rotate, zoom, and discover detailed information about each planet right in your browser.</p>
                    <a href="/galaxy/modelhemattroi/model3d.php" class="cta-button mt-3">Explore Now</a>
                </div>
            </div>
        </div>
    </section>

    <section class="community-cta-section">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Become Part of the Community</h2>
            <p class="section-subtitle animate-on-scroll">Join thousands of amateur astronomers and space lovers. Share photos, ask questions, and explore together.</p>
            <a href="/galaxy/congdong.php" class="cta-button animate-on-scroll" style="transition-delay: 0.2s;">Join Today</a>
        </div>
    </section>

    <section class="stats-section py-5">
        <div class="container">
            <h2 class="section-title animate-on-scroll">The Universe in Numbers</h2>
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                    <div class="stat-item">
                        <i class="fas fa-planet stat-icon"></i>
                        <h3 class="stat-number" data-target="8">0</h3>
                        <p class="stat-label">Major Planets</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                    <div class="stat-item">
                        <i class="fas fa-moon stat-icon"></i>
                        <h3 class="stat-number" data-target="290">0</h3>
                        <p class="stat-label">Confirmed Moons</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                    <div class="stat-item">
                        <i class="fas fa-star-of-david stat-icon"></i>
                        <h3 class="stat-number" data-target="2">0</h3>
                        <p class="stat-label">Trillion Galaxies (estimated)</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                    <div class="stat-item">
                        <i class="fas fa-infinity stat-icon"></i>
                        <h3 class="stat-number" data-target="13.8">0</h3>
                        <p class="stat-label">Billion Years Old</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="quote-section">
        <div class="container">
            <blockquote class="inspirational-quote animate-on-scroll">
                <p>"Somewhere, something incredible is waiting to be known."</p>
                <footer>— Carl Sagan</footer>
            </blockquote>
        </div>
    </section>

    <section class="video-showcase-section py-5">
        <div class="container">
            <h2 class="section-title animate-on-scroll">A Cinematic Space Experience</h2>
            <p class="text-center animate-on-scroll mb-5">
                Immerse yourself in stunning 4K films that showcase the majestic beauty of space.
            </p>

            <div class="main-video-player animate-on-scroll">
                <div class="video-wrapper">
                    <video controls autoplay muted loop>
                        <source src="/galaxy/images-icon/bgrgalaxy.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </section>
    
    <section id="about-section" class="py-5">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 animate-on-scroll">
                    <h2 class="section-title text-start">About Us</h2>
                    <p>Galaxy was born out of an endless passion for the universe. Our mission is to build a comprehensive knowledge platform, a vibrant community, and a source of inspiration for the next generation of Vietnamese space explorers. We believe that knowledge of the cosmos belongs to everyone.</p>
                </div>
                <div class="col-lg-5 text-center animate-on-scroll" style="transition-delay: 0.1s;">
                    <img src="/galaxy/images-icon/astronaut.jpg" alt="Galaxy Team" class="img-fluid rounded-circle w-75">
                </div>
            </div>
        </div>
    </section>
</main>
