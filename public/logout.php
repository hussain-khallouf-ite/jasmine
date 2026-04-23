<?php
session_start();
session_unset();
session_destroy();
$lang = isset($_GET['lang']) && $_GET['lang'] === 'ar' ? 'ar' : 'en';
header('Location: login.php?lang=' . $lang);
exit;
