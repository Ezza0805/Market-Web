<?php
// includes/tracker.php - Traffic Monitor & Analytics Tracker

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Track current request in traffic.json
 */
function track_traffic() {
    // Skip static assets or CLI executions (unless forced in tests)
    if (php_sapi_name() === 'cli' && !defined('FORCE_TRACK_CLI')) return;

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);

    // Ignore asset files
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf'])) {
        return;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
    $role = get_role();
    $user = current_user();

    $entry = [
        'ip' => $ip,
        'user_agent' => substr($user_agent, 0, 250),
        'path' => $uri,
        'method' => $method,
        'role' => $role,
        'user_id' => $user ? $user['id'] : null,
        'username' => $user ? $user['username'] : 'Guest',
        'referrer' => $referrer,
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s')
    ];

    // Read, append, and cap at latest 2,000 entries using the JSON DB engine
    $traffic = json_db_read('traffic');
    $max_id = 0;
    foreach ($traffic as $t) {
        if (isset($t['id']) && $t['id'] > $max_id) {
            $max_id = (int)$t['id'];
        }
    }
    $entry['id'] = $max_id + 1;
    $traffic[] = $entry;

    // Cap at latest 2,000 records
    if (count($traffic) > 2000) {
        $traffic = array_slice($traffic, -2000);
    }

    json_db_write('traffic', $traffic);
}

/**
 * Compute summary statistics from traffic logs
 */
function get_traffic_analytics() {
    $traffic = json_db_read('traffic');
    $total_visits = count($traffic);

    $today_str = date('Y-m-d');
    $today_visits = 0;
    $unique_ips_today = [];
    $all_unique_ips = [];
    $pages_count = [];
    $role_breakdown = ['guest' => 0, 'user' => 0, 'admin' => 0];
    
    // Hourly buckets for today (00 to 23)
    $hourly_today = array_fill(0, 24, 0);

    // Past 7 days counts
    $past_7_days = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $past_7_days[$d] = 0;
    }

    foreach ($traffic as $log) {
        $ip = $log['ip'] ?? 'unknown';
        $all_unique_ips[$ip] = true;
        $role = $log['role'] ?? 'guest';
        if (isset($role_breakdown[$role])) {
            $role_breakdown[$role]++;
        } else {
            $role_breakdown['guest']++;
        }

        $path = parse_url($log['path'] ?? '/', PHP_URL_PATH);
        if (!isset($pages_count[$path])) {
            $pages_count[$path] = 0;
        }
        $pages_count[$path]++;

        $log_date = substr($log['datetime'] ?? '', 0, 10);
        if ($log_date === $today_str) {
            $today_visits++;
            $unique_ips_today[$ip] = true;
            $hour = (int)date('G', $log['timestamp'] ?? time());
            if ($hour >= 0 && $hour < 24) {
                $hourly_today[$hour]++;
            }
        }

        if (isset($past_7_days[$log_date])) {
            $past_7_days[$log_date]++;
        }
    }

    arsort($pages_count);
    $top_pages = array_slice($pages_count, 0, 6, true);

    // Latest 15 logs
    $recent_logs = array_slice(array_reverse($traffic), 0, 15);

    return [
        'total_visits' => $total_visits,
        'today_visits' => $today_visits,
        'unique_today' => count($unique_ips_today),
        'unique_total' => count($all_unique_ips),
        'top_pages' => $top_pages,
        'role_breakdown' => $role_breakdown,
        'hourly_today' => $hourly_today,
        'past_7_days' => $past_7_days,
        'recent_logs' => $recent_logs
    ];
}
