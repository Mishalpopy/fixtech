import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */

// const { preset } = require('primevue/preset/tailwind');
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}"
    ],

    // presets: [preset],
    plugins: [require('tailwindcss-primeui')],
    darkMode: ['selector', '[class*="app-dark"]'],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#6366F1', // Match PrimeVue's theme
                secondary: '#4F46E5',
              },
        },
        theme: {
            screens: {
                sm: '576px',
                md: '768px',
                lg: '992px',
                xl: '1200px',
                '2xl': '1920px'
            }
        }
    },

    plugins: [forms],
};
