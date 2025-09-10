<main>
    <section class="hero-section" id="hero">
        <video autoplay muted playsinline loop id="hero-video-background">
            <source src="/galaxy/images-icon/bgrgalaxy.mp4" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ thẻ video.
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content container">
            <h1 class="animate-on-scroll">KHÁM PHÁ BÍ ẨN CỦA VŨ TRỤ VÔ TẬN</h1>
            <p class="lead text-light mb-4 animate-on-scroll" style="transition-delay: 0.2s;">Cánh cửa dẫn đến những kiến thức thiên văn, tin tức mới nhất và một cộng đồng đam mê không gian.</p>
            <a href="#explore-section" class="cta-button animate-on-scroll" style="transition-delay: 0.4s;">Bắt đầu hành trình</a>
        </div>
        <a href="#explore-section" class="scroll-down-indicator" aria-label="Scroll down"></a>
    </section>

    <section id="explore-section" class="py-5">
        <div class="container py-5">
            <h2 class="section-title animate-on-scroll">Cổng Thông Tin Vũ Trụ Của Bạn</h2>
            <p class="section-subtitle animate-on-scroll">Mọi thứ bạn cần để thỏa mãn niềm đam mê khám phá không gian, tất cả ở cùng một nơi.</p>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-satellite-dish"></i></div>
                        <h5 class="card-title text-white">Hệ Mặt Trời</h5>
                        <p class="card-text">Du hành qua các hành tinh, mặt trăng và khám phá những điều kỳ thú trong sân nhà vũ trụ.</p>
                        <a href="/galaxy/hemattroi/mattroi.php" class="btn btn-outline-primary mt-3">Tìm hiểu</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay: 0.1s;">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-book-open"></i></div>
                        <h5 class="card-title text-white">Thư Viện Vũ Trụ</h5>
                        <p class="card-text">Khám phá kho ảnh và video 4K tuyệt đẹp về các thiên hà, tinh vân do cộng đồng đóng góp.</p>
                        <a href="/galaxy/galaxy_lib.php" class="btn btn-outline-primary mt-3">Xem ngay</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay: 0.2s;">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-newspaper"></i></div>
                        <h5 class="card-title text-white">Tin Tức & Sự Kiện</h5>
                        <p class="card-text">Cập nhật tin tức nóng hổi và không bỏ lỡ các sự kiện thiên văn đáng chú ý sắp diễn ra.</p>
                        <a href="/galaxy/tintuc.php" class="btn btn-outline-primary mt-3">Đọc tin tức</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay: 0.3s;">
                    <div class="feature-card">
                        <div class="feature-card-icon"><i class="fas fa-users"></i></div>
                        <h5 class="card-title text-white">Cộng Đồng</h5>
                        <p class="card-text">Kết nối, chia sẻ kiến thức và hình ảnh của bạn với những người có cùng đam mê bất tận.</p>
                        <a href="/galaxy/congdong.php" class="btn btn-outline-primary mt-3">Tham gia</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="library-showcase-section text-white text-center">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Thư Viện Hình Ảnh & Video 4K</h2>
            <p class="section-subtitle animate-on-scroll">Đắm chìm trong vẻ đẹp ngoạn mục của vũ trụ qua những thước phim và hình ảnh chất lượng cao, được tuyển chọn kỹ lưỡng từ cộng đồng.</p>
            <a href="/galaxy/galaxy_lib.php" class="cta-button animate-on-scroll" style="transition-delay: 0.2s;">Khám Phá Thư Viện</a>
        </div>
    </section>

    <section id="news-section" class="py-5">
    <div class="container py-5">
        <h2 class="section-title animate-on-scroll">Tin tức nổi bật</h2>
        <p class="section-subtitle animate-on-scroll">Những khám phá và sự kiện mới nhất từ khắp nơi trong vũ trụ.</p>
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
                                    Đọc thêm 
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                    $delay += 0.1; 
                endforeach; 
                ?>
            <?php else: ?>
                <p class="text-center text-muted">Không có tin tức</p>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5">
            <a href="/galaxy/tintuc.php" class="cta-button animate-on-scroll">Xem tất cả tin tức</a>
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
                    <h2 class="section-title text-start">Trải Nghiệm Hệ Mặt Trời 3D</h2>
                    <p class="text-white">Không chỉ đọc, hãy tương tác! Khám phá mô hình 3D chân thực của Hệ Mặt Trời, xoay, thu phóng và tìm hiểu thông tin chi tiết về từng hành tinh ngay trên trình duyệt của bạn.</p>
                    <a href="/galaxy/modelhemattroi/model3d.php" class="cta-button mt-3">Khám phá ngay</a>
                </div>
            </div>
        </div>
    </section>

    <section class="community-cta-section">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Trở Thành Một Phần Của Cộng Đồng</h2>
            <p class="section-subtitle animate-on-scroll">Tham gia cùng hàng ngàn nhà thiên văn nghiệp dư và những người yêu vũ trụ. Chia sẻ hình ảnh, đặt câu hỏi và cùng nhau khám phá.</p>
            <a href="/galaxy/congdong.php" class="cta-button animate-on-scroll" style="transition-delay: 0.2s;">Tham Gia Ngay</a>
        </div>
    </section>

    <section class="stats-section py-5">
    <div class="container">
        <h2 class="section-title animate-on-scroll">Vũ Trụ Qua Những Con Số</h2>
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                <div class="stat-item">
                    <i class="fas fa-planet stat-icon"></i>
                    <h3 class="stat-number" data-target="8">0</h3>
                    <p class="stat-label">Hành Tinh Chính</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                <div class="stat-item">
                    <i class="fas fa-moon stat-icon"></i>
                    <h3 class="stat-number" data-target="290">0</h3>
                    <p class="stat-label">Mặt Trăng Được Xác Nhận</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                <div class="stat-item">
                    <i class="fas fa-star-of-david stat-icon"></i>
                    <h3 class="stat-number" data-target="2">0</h3>
                    <p class="stat-label">Nghìn Tỷ Thiên Hà (ước tính)</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4 animate-on-scroll">
                <div class="stat-item">
                    <i class="fas fa-infinity stat-icon"></i>
                    <h3 class="stat-number" data-target="13.8">0</h3>
                    <p class="stat-label">Tỷ Năm Tuổi</p>
                </div>
            </div>
        </div>
    </div>
</section>
    
    
    <section class="quote-section">
        <div class="container">
            <blockquote class="inspirational-quote animate-on-scroll">
                <p>"Ở đâu đó, một điều gì đó đáng kinh ngạc đang chờ được khám phá."</p>
                <footer>— Carl Sagan</footer>
            </blockquote>
        </div>
    </section>

    <section class="video-showcase-section py-5">
  <div class="container">
    <h2 class="section-title animate-on-scroll">Trải Nghiệm Điện Ảnh Vũ Trụ</h2>
    <p class="text-center animate-on-scroll mb-5">
      Đắm chìm trong những thước phim 4K sắc nét khám phá vẻ đẹp kỳ vĩ của không gian.
    </p>

    <div class="main-video-player animate-on-scroll">
      <div class="video-wrapper">
        <video controls autoplay muted loop>
          <source src="/galaxy/images-icon/bgrgalaxy.mp4" type="video/mp4">
          Trình duyệt của bạn không hỗ trợ video.
        </video>
      </div>
    </div>
  </div>
</section>

    
    <section id="about-section" class="py-5">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 animate-on-scroll">
                    <h2 class="section-title text-start">Về Chúng Tôi</h2>
                    <p>Galaxy được tạo ra từ niềm đam mê vô tận với vũ trụ. Sứ mệnh của chúng tôi là xây dựng một nền tảng kiến thức toàn diện, một cộng đồng sôi nổi và là nơi truyền cảm hứng cho thế hệ những nhà thám hiểm không gian tiếp theo của Việt Nam. Chúng tôi tin rằng kiến thức về vũ trụ thuộc về tất cả mọi người.</p>
                </div>
                <div class="col-lg-5 text-center animate-on-scroll" style="transition-delay: 0.1s;">
                    <img src="/galaxy/images-icon/astronaut.jpg" alt="Galaxy Team" class="img-fluid rounded-circle w-75">
                </div>
            </div>
        </div>
    </section>
    

</main>