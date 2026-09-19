<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

logoutUser();

redirect(BASE_URL . 'account/login.php?loggedout=1');