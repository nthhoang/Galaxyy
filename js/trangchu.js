// === GALAXY - ENHANCED MASTER SCRIPT ===

document.addEventListener('DOMContentLoaded', function() {

    /**
     * Chức năng 1: Xử lý đóng/mở Menu trên di động (Nếu bạn có menu trên header)
     */
    const menuToggle = document.getElementById('menu-toggle');
    const mainMenu = document.getElementById('main-menu');

    if (menuToggle && mainMenu) {
        menuToggle.addEventListener('click', function() {
            mainMenu.classList.toggle('is-open');
            const isOpen = mainMenu.classList.contains('is-open');
            this.innerHTML = isOpen ? '✖' : '☰';
            this.setAttribute('aria-label', isOpen ? 'Đóng menu' : 'Mở menu');
        });
    }

    /**
     * Chức năng 2: Xử lý đóng/mở Submenu trên di động (Nếu bạn có dropdown menu)
     */
    const dropdownItems = document.querySelectorAll('#main-menu .dropdown, #main-menu .dropdown2'); // Đảm bảo selector đúng với HTML của bạn

    dropdownItems.forEach(function(item) {
        const link = item.querySelector('a');
        if (link) { // Kiểm tra link có tồn tại
            link.addEventListener('click', function(event) {
                // Chỉ kích hoạt khi đang ở chế độ di động (nút toggle hiển thị)
                // Cần đảm bảo menuToggle đã được định nghĩa và có display none trên desktop
                if (menuToggle && window.getComputedStyle(menuToggle).display !== 'none') {
                    event.preventDefault();
                    item.classList.toggle('submenu-open');
                }
            });
        }
    });
    
    /**
     * Chức năng 3: Xử lý chuyển đổi ngôn ngữ (nếu có)
     */
    const langToggle = document.getElementById('lang-toggle');
    if (langToggle) {
        langToggle.addEventListener('change', function() {
            window.location.href = this.checked ? '?lang=en' : '?lang=vi';
        });
    }

    /**
     * Chức năng 4: Hiệu ứng xuất hiện khi cuộn trang (Animate On Scroll - AOS)
     */
    const aosObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = parseInt(entry.target.getAttribute('data-delay')) || 0; // Lấy delay từ data-delay
                setTimeout(() => {
                    entry.target.classList.add('is-visible');
                }, delay);
                observer.unobserve(entry.target); // Chạy 1 lần rồi ngừng quan sát
            }
        });
    }, {
        threshold: 0.15 // Kích hoạt khi 15% phần tử hiện ra
    });

    document.querySelectorAll('.animate-on-scroll').forEach(element => {
        aosObserver.observe(element);
    });


    /**
     * Chức năng 5: Hiệu ứng đếm số tự động cho Stats Section
     */
    const animateCounters = (counterElement) => {
        const target = parseFloat(counterElement.getAttribute('data-target'));
        const isDecimal = target % 1 !== 0; // Kiểm tra số thập phân
        const duration = 2500; // 2.5 giây
        let start = 0;
        let increment;

        if (isDecimal) {
            // Xử lý số thập phân (ví dụ 13.8 -> 138 -> đếm lên 138 rồi chia 10)
            const factor = 10;
            const scaledTarget = target * factor;
            increment = scaledTarget / (duration / 10); // Chia nhỏ bước để đếm mượt hơn
            
            let currentScaled = 0;
            const timer = setInterval(() => {
                currentScaled += increment;
                if (currentScaled > scaledTarget) {
                    currentScaled = scaledTarget;
                    clearInterval(timer);
                }
                counterElement.innerText = (currentScaled / factor).toFixed(1); // Làm tròn 1 chữ số thập phân
            }, 10); // Cập nhật mỗi 10ms

        } else {
            increment = target / (duration / 10);
            
            let current = 0;
            const timer = setInterval(() => {
                current += increment;
                if (current > target) {
                    current = target;
                    clearInterval(timer);
                }
                counterElement.innerText = Math.floor(current);
            }, 10);
        }
    };

    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    document.querySelectorAll('.stat-number').forEach(animateCounters);
                    observer.unobserve(entry.target); // Chỉ chạy 1 lần
                }
            });
        }, {
            threshold: 0.4 // Kích hoạt khi 40% phần stats hiện ra
        });
        statsObserver.observe(statsSection);
    }
});