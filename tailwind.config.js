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
                sans: ['"Google Sans"', 'Inter', 'Outfit', ...defaultTheme.fontFamily.sans],
                mono: ['"Anonymous Pro"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                primary: '#EA580C', // Orange (Warna Resto)
                secondary: '#16A34A', // Green
                success: '#10B981',
                warning: '#F59E0B',
                danger: '#EF4444',
                surface: '#FFFFFF',
                text: '#111827',
                neutral: '#FFFFFF',
            },
            borderRadius: {
                'sm': '4px',
                'md': '8px',
            },
            spacing: {
                'sm': '4px',
                'md': '8px',
            }
        },
    },

    plugins: [forms],
};
