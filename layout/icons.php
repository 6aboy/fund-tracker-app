<?php
/** Small local SVG icons, independent of external fonts or icon services. */
function card_icon(string $name, string $tone = 'brand'): string
{
    $paths = [
        'wallet' => '<path d="M20 8V5a2 2 0 0 0-2-2H5a3 3 0 0 0 0 6h15v12H5a3 3 0 0 1-3-3V6"/><path d="M20 12h-5v5h5"/><path d="M17 14.5h.01"/>',
        'deposit' => '<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>',
        'expense' => '<path d="M12 16V4m-5 5 5-5 5 5M4 16v5h16v-5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-13 5h.01m4 0h.01m4 0h.01"/>',
        'net' => '<path d="M3 17 9 11l4 4L21 5m-6 0h6v6"/>',
        'percent' => '<path d="m5 19 14-14"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/>',
        'report' => '<path d="M14 2H5v20h14V7l-5-5v5h5M8 17v-3m4 3v-6m4 6v-4"/>',
        'chart' => '<path d="M3 3v18h18M7 16l4-6 4 3 6-8"/>',
        'pie' => '<path d="M10 3a9 9 0 1 0 11 11H10V3Z"/><path d="M14 2v8h8a8 8 0 0 0-8-8Z"/>',
    ];
    $path = $paths[$name] ?? $paths['report'];
    return '<span class="card-icon icon-' . htmlspecialchars($tone) . '" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg></span>';
}
