<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/admin-login.php';
require_once __DIR__ . '/../function/helper.php';

logoutAdmin();

session_destroy();

redirect(BASE_URL . 'admin/login.php');