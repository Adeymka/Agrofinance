/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['Space Grotesk', 'system-ui', 'sans-serif'],
                ui: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
};
