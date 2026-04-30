<?php
/**
 * @var array $proprites
 * @var string|null $errorMessage
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة الشقق | الياسمين</title>
    <meta name="description" content="استعرض أحدث شققنا المتاحة مع صور ومواصفات كاملة وسعر شهري واضح.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" integrity="sha512-yH3mZc3MGbM0Gv7+NeNcoYi6Zq+NWOmwth3eUeFjC/YE9X7eo0FSynysuF+YfA6+QcrN0SxBxNN8tW9Jd0h6KA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="شعار الياسمين" height="40" class="d-inline-block align-text-top" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'><circle cx=\'20\' cy=\'20\' r=\'20\' fill=\'%232F5D62\'/></svg>'">
                الياسمين
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="تبديل التنقل">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="proprites.php">استعراض الشقق</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.html">تسجيل الدخول</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold">شقق مُحدَّثة بأسلوب عصري</h1>
                    <p class="text-muted fs-5">اعثر على شقة تناسب أسلوب حياتك مع عرض واضح للصور والموقع وعدد الغرف والسعر الشهري.</p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a href="register.html" class="btn btn-primary-custom btn-lg">اطلب مشاهدة</a>
                </div>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($proprites) && !$errorMessage): ?>
                <div class="text-center py-5">
                    <h3 class="text-muted mb-3">لا توجد شقق متاحة حالياً</h3>
                    <p class="mb-0">يرجى العودة لاحقاً للاطلاع على أحدث العروض.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($proprites as $proprite): ?>
                        <?php $image = !empty($proprite['image_url']) ? $proprite['image_url'] : getDefaultPropertyImage(); ?>
                        <div class="col-sm-12 col-md-6 col-xl-4">
                            <div class="card property-card h-100">
                                <div class="property-img" style="background-image: url('<?php echo escape($image); ?>');"></div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-success property-badge">متاح</span>
                                        <span class="fw-bold text-primary-custom fs-5"><?php echo escape(formatPrice((float)$proprite['price_per_month'])); ?></span>
                                    </div>
                                    <h5 class="card-title fw-bold"><?php echo escape($proprite['title']); ?></h5>
                                    <p class="text-muted mb-3 small property-description"><?php echo escape($proprite['description']); ?></p>
                                    <div class="d-flex property-meta flex-wrap mb-3">
                                        <span><i class="bi bi-geo-alt"></i><?php echo escape($proprite['location']); ?></span>
                                        <span><i class="bi bi-door-closed"></i><?php echo escape((int)$proprite['rooms']); ?> غرف</span>
                                        <span><i class="bi bi-aspect-ratio"></i><?php echo escape((float)$proprite['size_m2']); ?> م²</span>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="index.php" class="btn btn-outline-primary w-100">عرض التفاصيل</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> الياسمين. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
