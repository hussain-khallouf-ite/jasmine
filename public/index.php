<?php
// Initialize logic (in a real app, config would be included here)
// require_once '../config/database.php';
$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang === 'ar' ? 'الياسمين | حجز شقق فاخرة' : 'Al-Yasmin | Luxury Apartment Booking'; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $lang === 'ar' ? 'اكتشف الشقق الفاخرة في مشروع الياسمين في مدينة حماة، سوريا. منصة حجز شفافة وفعالة للعائلات والمستثمرين.' : 'Discover premium apartments at Al-Yasmin project in Hama City, Syria. Transparent and efficient booking platform for families and investors.'; ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <?php if ($dir === 'rtl'): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Al-Yasmin Logo" height="40" class="d-inline-block align-text-top" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'><circle cx=\'20\' cy=\'20\' r=\'20\' fill=\'%232F5D62\'/></svg>'">
                Al-Yasmin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><?php echo $lang === 'ar' ? 'الرئيسية' : 'Home'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#apartments"><?php echo $lang === 'ar' ? 'الشقق' : 'Apartments'; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><?php echo $lang === 'ar' ? 'تسجيل الدخول' : 'Login'; ?></a>
                    </li>
                    <li class="nav-item ms-lg-3 d-flex align-items-center">
                        <a class="btn btn-outline-secondary btn-sm" href="?lang=<?php echo $lang === 'ar' ? 'en' : 'ar'; ?>">
                            <?php echo $lang === 'ar' ? 'English' : 'عربي'; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content animate-fade-in-down">
            <h1 class="hero-title"><?php echo $lang === 'ar' ? 'استمتع بالمعيشة الفاخرة في حماة' : 'Experience Premium Living in Hama'; ?></h1>
            <p class="hero-subtitle"><?php echo $lang === 'ar' ? 'اكتشف الشقق المصممة بشكل جميل في قلب وادي الجوز. احجز منزلك المثالي اليوم مع الياسمين.' : 'Discover beautifully designed apartments in the heart of Wadi al-Jawz. Book your perfect home today with Al-Yasmin.'; ?></p>
            
            <!-- Quick Search Form -->
            <div class="search-card mt-4">
                <form class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" aria-label="Property Type">
                            <option selected><?php echo $lang === 'ar' ? 'نوع العقار' : 'Property Type'; ?></option>
                            <option value="1"><?php echo $lang === 'ar' ? 'شقة سكنية' : 'Residential Apartment'; ?></option>
                            <option value="2"><?php echo $lang === 'ar' ? 'مساحة تجارية' : 'Commercial Space'; ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" aria-label="Bedrooms">
                            <option selected><?php echo $lang === 'ar' ? 'غرف النوم' : 'Bedrooms'; ?></option>
                            <option value="1"><?php echo $lang === 'ar' ? 'غرفة نوم واحدة' : '1 Bedroom'; ?></option>
                            <option value="2"><?php echo $lang === 'ar' ? 'غرفتي نوم' : '2 Bedrooms'; ?></option>
                            <option value="3"><?php echo $lang === 'ar' ? '3 غرف نوم' : '3 Bedrooms'; ?></option>
                            <option value="4"><?php echo $lang === 'ar' ? '4+ غرف نوم' : '4+ Bedrooms'; ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary-custom w-100"><?php echo $lang === 'ar' ? 'البحث الآن' : 'Search Now'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <!-- Featured Properties -->
    <section id="apartments" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title"><?php echo $lang === 'ar' ? 'الشقق المميزة' : 'Featured Apartments'; ?></h2>
                <p class="text-muted"><?php echo $lang === 'ar' ? 'استكشف مجموعتنا المختارة من مساحات المعيشة الفاخرة' : 'Explore our curated selection of premium living spaces'; ?></p>
            </div>
            
            <div class="row g-4 d-flex justify-content-center">
                <!-- Fallback Static Cards for Frontend Preview -->
                <div class="col-md-6 col-lg-4">
                    <div class="card property-card h-100">
                        <div class="property-img w-100" style="background-image: url('https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')"></div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-success"><?php echo $lang === 'ar' ? 'متاح' : 'Available'; ?></span>
                                <span class="fw-bold text-primary-custom fs-5">$350/mo</span>
                            </div>
                            <h5 class="card-title fw-bold mt-2"><?php echo $lang === 'ar' ? 'شقة فاخرة 3 غرف نوم مع إطلالة على الوادي' : 'Premium 3BR Wadi View'; ?></h5>
                            <p class="card-text text-muted small"><?php echo $lang === 'ar' ? 'شقة واسعة مع إطلالات خلابة. تشطيب عصري.' : 'Spacious apartment with stunning views. Modern finishing.'; ?></p>
                            <div class="d-flex gap-3 text-muted small my-3">
                                <span><i class="bi bi-door-closed"></i> <?php echo $lang === 'ar' ? '3 غرف' : '3 Rooms'; ?></span>
                                <span><i class="bi bi-rulers"></i> 140 m²</span>
                                <span><i class="bi bi-building"></i> <?php echo $lang === 'ar' ? 'الطابق 4' : '4th Fl'; ?></span>
                            </div>
                            <a href="#" class="btn btn-outline-primary w-100 mt-2"><?php echo $lang === 'ar' ? 'عرض التفاصيل' : 'View Details'; ?></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card property-card h-100">
                        <div class="property-img w-100" style="background-image: url('https://images.unsplash.com/photo-1502672260266-1c1de2d93688?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80')"></div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-success"><?php echo $lang === 'ar' ? 'متاح' : 'Available'; ?></span>
                                <span class="fw-bold text-primary-custom fs-5">$600/mo</span>
                            </div>
                            <h5 class="card-title fw-bold mt-2"><?php echo $lang === 'ar' ? 'بنتهاوس فاخر' : 'Luxury Penthouse'; ?></h5>
                            <p class="card-text text-muted small"><?php echo $lang === 'ar' ? 'معيشة في الطابق العلوي مع تراس خاص وتخزين ماء.' : 'Top floor living with private terrace and water storage.'; ?></p>
                            <div class="d-flex gap-3 text-muted small my-3">
                                <span><i class="bi bi-door-closed"></i> <?php echo $lang === 'ar' ? '4 غرف' : '4 Rooms'; ?></span>
                                <span><i class="bi bi-rulers"></i> 210 m²</span>
                                <span><i class="bi bi-building"></i> <?php echo $lang === 'ar' ? 'الطابق 8' : '8th Fl'; ?></span>
                            </div>
                            <a href="#" class="btn btn-outline-primary w-100 mt-2"><?php echo $lang === 'ar' ? 'عرض التفاصيل' : 'View Details'; ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $lang === 'ar' ? 'نظام حجز شقق الياسمين. جميع الحقوق محفوظة.' : 'Al-Yasmin Apartment Booking System. All rights reserved.'; ?></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
