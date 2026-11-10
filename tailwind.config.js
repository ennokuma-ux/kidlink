/** @type {import('tailwindcss').Config} */
export default {
    // Only the app's own templates and scripts are scanned. The minified
    // vendor bundles in public/js are deliberately left out: scanning them
    // would generate thousands of classes the app never uses.
    content: [
        './app/**/*.php',
        './resources/views/**/*.blade.php',
        './public/js/ajax-filters.js',
        './public/js/confirm-swal.js',
        './public/js/dash-views.js',
        './public/js/effects.js',
        './public/js/game-audio.js',
        './public/js/kidlink-game.js',
        './public/js/live-filters.js',
        './public/js/mascot.js',
    ],
    theme: {
        extend: {
            // Same card shadows the old runtime config applied from
            // components/stronger-shadows.blade.php. The page background is a
            // light cream, so Tailwind's default 5% black shadows disappear.
            boxShadow: {
                sm: '0 1px 3px rgba(24,36,56,.18), 0 4px 12px rgba(24,36,56,.14)',
                DEFAULT: '0 2px 5px rgba(24,36,56,.18), 0 6px 16px rgba(24,36,56,.16)',
                md: '0 3px 8px rgba(24,36,56,.20), 0 10px 22px rgba(24,36,56,.16)',
                lg: '0 6px 14px rgba(24,36,56,.22), 0 16px 32px rgba(24,36,56,.18)',
                xl: '0 10px 20px rgba(24,36,56,.24), 0 24px 44px rgba(24,36,56,.20)',
            },
        },
    },
    plugins: [],
};
