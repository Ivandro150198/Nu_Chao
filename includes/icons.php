<?php
declare(strict_types=1);

function icon(string $name, string $class = 'icon'): string
{
    $icons = [
        'home' => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z"/>',
        'shirt' => '<path d="M9 4 7 7l-3 1 2 3v9h12V11l2-3-3-1-2-3H9zm3 6v8"/><path d="M9 4h6"/>',
        'bag' => '<path d="M7 8h10l1 12H6L7 8z"/><path d="M9 8V7a3 3 0 0 1 6 0v1"/>',
        'cart' => '<circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/><path d="M3 4h2l2.4 11h9.8L20 8H7"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 19.5c1.8-3.2 4.2-4.5 7-4.5s5.2 1.3 7 4.5"/>',
        'phone' => '<path d="M7 3h3l1.5 4.5-2 1.5a11 11 0 0 0 5.5 5.5l1.5-2L21 14v3a2 2 0 0 1-2.2 2A16 16 0 0 1 5 7.2 2 2 0 0 1 7 5"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/>',
        'map' => '<path d="M9 4 4 6v14l5-2 6 2 5-2V4l-5 2-6-2z"/><circle cx="12" cy="11" r="2"/>',
        'whatsapp' => '<path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.7-1.2A9 9 0 1 0 12 3zm0 2a7 7 0 0 1 5.9 10.8l-.3.4.5 1.9-1.9-.5-.4.3A7 7 0 1 1 12 5zm-2.8 3.3c.2 0 .4 0 .6.4l.8 1.9c.1.2 0 .4-.1.5l-.4.5c-.1.1-.2.3 0 .5.4.7 1.2 1.5 2 1.9.3.1.5.1.6-.1l.6-.8c.1-.2.3-.2.5-.1l2 .9c.2.1.4.2.4.5 0 .8-.5 2.3-2.4 2.3-1.5 0-3.5-.8-4.8-2.5-1.1-1.4-1.5-3-1.5-3.8 0-.4.3-1.5 1.2-1.8.2-.1.4-.1.5-.1z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M19 13.5A7.5 7.5 0 1 1 10.5 5 6 6 0 0 0 19 13.5z"/>',
        'truck' => '<path d="M1 8h11v9H1zM12 11h5l3 3v3h-8v-6z"/><circle cx="5.5" cy="18.5" r="1.5"/><circle cx="16.5" cy="18.5" r="1.5"/>',
        'check' => '<path d="M5 12.5 9.5 17 19 7.5"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="m20 20-4.3-4.3"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'chevron-left' => '<path d="M15 5 8 12l7 7"/>',
        'chevron-right' => '<path d="m9 5 7 7-7 7"/>',
        'spark' => '<path d="M12 3v4M12 17v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M3 12h4M17 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/>',
        'logout' => '<path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4M15 8l4 4-4 4M9 12h10"/>',
        'login' => '<path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M9 8l-4 4 4 4M5 12h10"/>',
        'package' => '<path d="M12 3 4 7v10l8 4 8-4V7l-8-4z"/><path d="M12 13V3M4 7l8 6 8-6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'heart' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
        'send' => '<path d="M4 12 20 4l-5 16-3-6-6-2z"/>',
        'tag' => '<path d="M20.6 13.4 10.9 3.7A2 2 0 0 0 9.5 3H4a1 1 0 0 0-1 1v5.5a2 2 0 0 0 .6 1.4l9.7 9.7a2 2 0 0 0 2.8 0l4.5-4.5a2 2 0 0 0 0-2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9c.3.6.9 1 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'bell' => '<path d="M6 9a6 6 0 0 1 12 0c0 7 3 7 3 7H3s3 0 3-7"/><path d="M10 19a2 2 0 0 0 4 0"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'image' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="m21 15-4.5-4.5L7 20"/>',
    ];

    $path = $icons[$name] ?? $icons['spark'];
    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}
