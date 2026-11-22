<?php
// includes/svg_icons.php - Crisp Vector SVG Graphics Generator for Products

function get_product_svg($icon, $color = '#2563eb', $width = 140, $height = 140) {
    $icon = strtolower($icon);
    
    $svgs = [
        'headphones' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <path d="M50 110V90a50 50 0 0 1 100 0v20" stroke="' . $color . '" stroke-width="8" stroke-linecap="round" fill="none"/>
            <rect x="42" y="100" width="18" height="34" rx="9" fill="' . $color . '"/>
            <rect x="140" y="100" width="18" height="34" rx="9" fill="' . $color . '"/>
            <path d="M60 115v10M140 115v10" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
        ',
        'laptop' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <rect x="55" y="65" width="90" height="58" rx="6" fill="#0f172a"/>
            <rect x="61" y="71" width="78" height="46" rx="2" fill="' . $color . '"/>
            <polygon points="40 130 160 130 150 123 50 123" fill="#64748b"/>
            <rect x="90" y="123" width="20" height="3" rx="1.5" fill="#cbd5e1"/>
        ',
        'mouse' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <rect x="75" y="55" width="50" height="90" rx="25" fill="' . $color . '"/>
            <line x1="100" y1="55" x2="100" y2="85" stroke="#ffffff" stroke-width="4"/>
            <circle cx="100" cy="75" r="4" fill="#0f172a"/>
        ',
        'keyboard' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <rect x="45" y="70" width="110" height="60" rx="8" fill="#1e293b"/>
            <rect x="52" y="78" width="16" height="12" rx="3" fill="' . $color . '"/>
            <rect x="74" y="78" width="16" height="12" rx="3" fill="#475569"/>
            <rect x="96" y="78" width="16" height="12" rx="3" fill="#475569"/>
            <rect x="118" y="78" width="16" height="12" rx="3" fill="' . $color . '"/>
            <rect x="52" y="96" width="20" height="12" rx="3" fill="#475569"/>
            <rect x="78" y="96" width="44" height="12" rx="3" fill="' . $color . '"/>
            <rect x="128" y="96" width="20" height="12" rx="3" fill="#475569"/>
            <rect x="66" y="114" width="68" height="10" rx="3" fill="#94a3b8"/>
        ',
        'bag' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <path d="M80 65v-10a20 20 0 0 1 40 0v10" stroke="' . $color . '" stroke-width="6" fill="none" stroke-linecap="round"/>
            <rect x="58" y="65" width="84" height="85" rx="14" fill="' . $color . '"/>
            <rect x="70" y="95" width="60" height="42" rx="6" fill="#78350f" fill-opacity="0.3"/>
            <circle cx="100" cy="104" r="4" fill="#fbbf24"/>
        ',
        'monitor' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <rect x="45" y="55" width="110" height="68" rx="8" fill="#0f172a"/>
            <rect x="50" y="60" width="100" height="58" rx="4" fill="' . $color . '" fill-opacity="0.8"/>
            <rect x="95" y="123" width="10" height="18" fill="#475569"/>
            <ellipse cx="100" cy="142" rx="28" ry="6" fill="#334155"/>
        ',
        'watch' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <rect x="82" y="40" width="36" height="120" rx="8" fill="#334155"/>
            <rect x="72" y="68" width="56" height="64" rx="14" fill="#0f172a"/>
            <rect x="78" y="74" width="44" height="52" rx="10" fill="' . $color . '"/>
            <circle cx="100" cy="100" r="16" stroke="#ffffff" stroke-width="3" fill="none"/>
            <line x1="100" y1="100" x2="100" y2="92" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
            <line x1="100" y1="100" x2="107" y2="100" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
        ',
        'coffee' => '
            <circle cx="100" cy="100" r="80" fill="' . $color . '" fill-opacity="0.12"/>
            <rect x="75" y="50" width="50" height="70" rx="4" fill="#0f172a" fill-opacity="0.8"/>
            <rect x="80" y="55" width="40" height="60" rx="2" fill="' . $color . '" fill-opacity="0.5"/>
            <rect x="68" y="42" width="64" height="10" rx="3" fill="#334155"/>
            <line x1="100" y1="35" x2="100" y2="45" stroke="#334155" stroke-width="4"/>
            <rect x="85" y="28" width="30" height="8" rx="4" fill="#0f172a"/>
            <rect x="70" y="120" width="60" height="8" rx="2" fill="#d97706"/>
            <rect x="62" y="128" width="76" height="16" rx="4" fill="#0f172a"/>
        '
    ];

    $content = $svgs[$icon] ?? $svgs['laptop'];

    return '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">' . $content . '</svg>';
}
