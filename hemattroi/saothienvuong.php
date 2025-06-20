<?php
session_start();
$loggedIn = isset($_SESSION['username']);
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sao Thiên Vương - Hành Tinh Lăn Bên</title>
      <link rel="stylesheet" href="/galaxy/css/header.css">
    
    <script src="/galaxy/js/index.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap&subset=vietnamese" rel="stylesheet">

    <style>
        /* CSS CHO NỘI DUNG TRANG SAO THIÊN VƯƠNG */
        body {
            background-color: #05101a; /* Nền xanh lục đậm rất tối */
            color: #cccccc;
            font-family: 'RobotoRoboto', sans-serif; margin: 0;
             padding: 0;
             width: 100%;
             cursor: url('/galaxy/cursor.cur'), auto !important;
        }

        /* --- CSS CHO MÔ HÌNH 3D NASA (ĐƯA LÊN ĐẦU) --- */
        .nasa-iframe-container {
            margin: 30px auto;
            padding: 25px;
            max-width: 1100px;
            background-color: rgba(5, 20, 30, 0.6); /* Nền xanh lục đậm pha chút trong suốt */
            border-radius: 15px;
            text-align: center;
            border: 1px solid #004050; /* Viền xanh lục thẫm */
        }

        .nasa-iframe-container h3 {
            font-family: 'Exo 2', sans-serif;
            color: #AFEEEE; /* PaleTurquoise cho tiêu đề iframe */
            margin-bottom: 20px;
            font-size: 2.2em;
            text-shadow: 0 0 10px rgba(175, 238, 238, 0.6);
        }
        
        .nasa-iframe-container iframe {
            width: 100%; 
            max-width: 900px; 
            min-height: 500px; 
            border-radius: 10px;
            border: 2px solid #AFEEEE; /* Viền PaleTurquoise */
        }
        /* --- KẾT THÚC CSS MÔ HÌNH 3D NASA --- */

        .planet-content-section { 
            padding: 30px 50px;
            max-width: 1100px;
            margin: 40px auto;
            background: linear-gradient(145deg, rgba(10, 30, 45, 0.9), rgba(15, 40, 60, 0.95)); /* Gradient nền xanh lục/lam */
            border-radius: 15px;
            border: 1px solid #40E0D0; /* Viền màu Turquoise */
            box-shadow: 0 4px 25px rgba(64, 224, 208, 0.25); /* Bóng đổ màu Turquoise */
        }

        .planet-content-section h2, .planet-content-section h3, .planet-content-section h4 {
            font-family: 'Exo 2', sans-serif;
            color: #7FFFD4; /* Aquamarine cho tiêu đề */
            text-shadow: 0 0 10px rgba(127, 255, 212, 0.7);
        }

        .planet-content-section h2 {
            text-align: center;
            margin-bottom: 35px;
            font-size: 3.2em;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .planet-content-section h3 {
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 2.4em;
            font-weight: 600;
            border-bottom: 2px solid #20B2AA; /* LightSeaGreen cho gạch chân */
            padding-bottom: 10px;
            color: #AFEEEE; /* PaleTurquoise */
        }
        
        .planet-content-section h4 {
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 1.8em;
            font-weight: 600;
            color: #B0E0E6; /* PowderBlue */
        }

        .planet-content-section p, .planet-content-section li {
            line-height: 1.8;
            margin-bottom: 18px;
            font-size: 1.1em;
            color: #e0e0e0;
            font-weight: 300;
        }

        .planet-content-section p {
             text-align: justify;
        }

        .planet-content-section strong {
            font-weight: 700;
            color: #E0FFFF; /* LightCyan - màu sáng hơn cho chữ đậm */
        }
        
        .planet-content-section ul {
            list-style-type: none;
            padding-left: 10px;
        }

        .planet-content-section li::before {
            content: "♅"; /* Biểu tượng Sao Thiên Vương */
            color: #7FFFD4; 
            display: inline-block;
            width: 1.5em;
            margin-left: -1.5em;
            font-size: 1.2em;
        }
        
        .planet-image-container {
            text-align: center;
            margin: 30px 0;
            background-color: rgba(5,20,25,0.2); /* Nền xanh lục tối cho ảnh */
            padding: 20px;
            border-radius: 10px;
        }

        .planet-image-container img {
            max-width: 70%;
            height: auto;
            border-radius: 10px;
            border: 4px solid #40E0D0; /* Viền Turquoise */
            box-shadow: 0 0 20px rgba(64, 224, 208, 0.6);
        }
        
        .info-box {
            background-color: rgba(0, 70, 80, 0.5); /* Xanh teal đậm cho info box */
            border-left: 5px solid #7FFFD4; /* Aquamarine */
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .info-box p {
            margin-bottom: 10px;
            font-size: 1.05em;
        }
        
        .info-box strong {
            color: #AFEEEE; /* PaleTurquoise */
        }

        /* --- CSS CHO THƯ VIỆN HÌNH ẢNH Ở CUỐI --- */
        .planet-gallery-section { 
            max-width: 1100px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(10, 30, 40, 0.7); /* Nền xanh lục sẫm cho gallery */
            border-radius: 15px;
            border: 1px solid #008080; /* Teal */
            box-shadow: 0 4px 20px rgba(0, 128, 128, 0.2);
        }

        .planet-gallery-section h3 {
            font-family: 'Exo 2', sans-serif;
            color: #AFEEEE; /* PaleTurquoise gallery title */
            text-align: center;
            font-size: 2.4em;
            margin-bottom: 30px;
            text-shadow: 0 0 8px rgba(175, 238, 238, 0.5);
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .image-grid .gallery-item {
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            background-color: rgba(0,0,0,0.3);
            border: 2px solid #20B2AA; /* LightSeaGreen */
        }
        
        .image-grid img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            border-radius: 8px 8px 0 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .image-grid img:hover {
            transform: scale(1.05);
            opacity: 0.85;
        }
        
        .image-grid .caption {
            padding: 10px;
            text-align: center;
            font-size: 0.9em;
            color: #B0E0E6; /* PowderBlue */
            background-color: rgba(5, 25, 35, 0.8); /* Nền xanh lục tối cho caption */
            border-radius: 0 0 8px 8px;
        }
        /* --- KẾT THÚC CSS THƯ VIỆN HÌNH ẢNH --- */


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .nasa-iframe-container {
                margin: 20px auto;
                padding: 15px;
            }
            .nasa-iframe-container iframe {
                min-height: 350px;
            }
            .planet-content-section {
                padding: 20px 25px;
            }
            .planet-content-section h2 {
                font-size: 2.5em;
            }
            .planet-content-section h3 {
                font-size: 2em;
            }
            .planet-content-section h4 {
                font-size: 1.6em;
            }
            .planet-image-container img {
                max-width: 90%;
            }
            .image-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
    </style>
</head>

<body>
   <header id="head"> <div class="logo-container">
    <img src="/galaxy/images-icon/logo3.png" alt="logonhom" class="logo-overlay">
</div>
       <div id="menuhead">
        
        <nav>
           <button id="menu-toggle" aria-label="Mở menu">☰</button>

        <ul id="main-menu">
                <li><a href="/galaxy/trangchu.php"><img src="/galaxy/images-icon/home.png" alt=""><?= t('1') ?></a></li>

                <li class="dropdown">
                    <a href="#" class="active"><img src="/galaxy/images-icon/hemattroi.png"   alt=""><?= t('2') ?></a>
                    <div class="dropdown-content">
                        <a class="item" href="mattroi.php"><img src="/galaxy/images-icon/sun.png" alt=""><?= t('2,1') ?></a>
                        <a class="item" href="saothuy.php"><img src="/galaxy/images-icon/mercury.png" alt=""><?= t('2,2') ?></a>
                        <a class="item" href="saokim.php"><img src="/galaxy/images-icon/venus.png" alt=""><?= t('2,3') ?></a>
                        <a class="item" href="traidat.php"><img src="/galaxy/images-icon/earth.png" alt=""><?= t('2,4') ?></a>
                        <a class="item" href="mattrang.php"><img src="/galaxy/images-icon/full-moon.png" alt=""><?= t('2,5') ?> </a>
                        <a class="item" href="saohoa.php"><img src="/galaxy/images-icon/mars.png" alt=""><?= t('2,6') ?></a>
                        <a class="item" href="saomoc.php"><img src="/galaxy/images-icon/jupiter.png" alt=""><?= t('2,7') ?></a>
                        <a class="item" href="saotho.php"><img src="/galaxy/images-icon/saturn.png" alt=""><?= t('2,8') ?></a>
                        <a class="item" href="saothienvuong.php"><img src="/galaxy/images-icon/uranus.png" alt=""><?= t('2,9') ?></a>
                        <a class="item" href="saohaivuong.php"><img src="/galaxy/images-icon/neptune.png" alt=""><?= t('2,10') ?></a>
                    </div>
                </li>
                <li class="dropdown"><a href="#"><img src="/galaxy/images-icon/black-hole.png" alt=""><?= t('3') ?></a>
                    <div class="dropdown-content">
                        <a class="item" href="/galaxy/vutru.php"><img src="/galaxy/images-icon/vutru.png" alt=""><?= t('3,3') ?> </a>
                        <a class="item" href="/galaxy/sukien.php"><img src="/galaxy/images-icon/sukien.png" alt=""><?= t('3,1') ?> </a>
                        <a class="item" href="/galaxy/tintuc.php"><img src="/galaxy/images-icon/news.png" alt=""><?= t('3,2') ?>
                 <li ><a href="/galaxy/congdong.php"><img src="/galaxy/images-icon/group (1).png" alt=""><?= t('4') ?></a>  </li>
               <li>
  <a href="<?php echo $loggedIn ? '/galaxy/taikhoan.php' : '/galaxy/TAIKHOAN/login-register.html'; ?>">
    <img src="/galaxy/images-icon/dangnhap.png" alt=""><?= t('5') ?>
  </a>
</li>
                <li class="dropdown"><a href="#"><img src="/galaxy/images-icon/more.png" alt=""><?= t('6') ?></a>
                     <div class="dropdown-content" style="left: -170%">
                        <a class="item" href="/galaxy/vechungtoi.php"><img src="/galaxy/images-icon/group.png" alt=""><?= t('6,1') ?></a>
                        <a class="language-switcher-container">
        <input type="checkbox" id="lang-toggle" class="lang-toggle-checkbox"
               <?php if(isset($current_lang)) echo ($current_lang == 'en') ? 'checked' : ''; ?>
        >
        <label for="lang-toggle" class="lang-toggle-label">
            <span class="lang-toggle-inner"></span>
            <span class="lang-toggle-switch"></span>
        </label>
             </a>
                     </div>
                </li> 
            </ul>
        </nav></div>
    </header>
<main class="sun-content-section">
        <?php
            // 1. Xác định đường dẫn đến tệp nội dung dựa trên ngôn ngữ hiện tại
            $content_file = $_SERVER['DOCUMENT_ROOT'] . "/galaxy/content/uranus_{$current_lang}.html";

            // 2. Kiểm tra tệp có tồn tại không và đọc toàn bộ nội dung của nó
            if (file_exists($content_file)) {
                echo file_get_contents($content_file);
            } else {
                echo "Nội dung không có sẵn cho ngôn ngữ này.";
            }
        ?>
    </main>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- <script src="script.js"></script> -->
<script src="/galaxy/js/allhemattroi.js"></script>
    </body>
</html>