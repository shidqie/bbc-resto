import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Google Sans"', '"Outfit"', 'Inter', ...defaultTheme.fontFamily.sans],
                serif: ['"Playfair Display"', '"Libre Caslon Text"', ...defaultTheme.fontFamily.serif],
                mono: ['"Anonymous Pro"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                primary: {
                    DEFAULT: '#0D3024',
                    container: '#0a2219',
                },
                secondary: {
                    DEFAULT: '#B8860B',
                    container: '#d4a843',
                },
                accent: '#D4A843',
                canvas: '#FAFAF7',
                surface: '#FFFFFF',
                body: '#3D3D3D',
                success: '#16A34A',
                warning: '#D97706',
                danger: '#DC2626',
            },
            borderRadius: {
                'sm': '4px',
                'md': '8px',
            },
        },
    },

    plugins: [forms],
};
