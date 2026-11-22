<?php
// logout.php - Safe Session & Cookie Cleanup
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

logout();
set_flash('info', 'You have been safely signed out. You are now browsing as a Guest.');
header('Location: /index.php');
exit;
