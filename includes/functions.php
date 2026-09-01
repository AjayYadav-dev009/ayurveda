<?php

function redirect($url)
{
    header("Location: " . $url);
    exit;
}

function clean($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['admin_id']);
}
