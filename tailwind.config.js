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
            fontSize: {
                xs: ['13px', '1.45'],
                sm: ['15px', '1.5'],
                base: ['16px', '1.55'],
                lg: ['18px', '1.5'],
                xl: ['20px', '1.4'],
                '2xl': ['24px', '1.3'],
                '3xl': ['30px', '1.25'],
                '4xl': ['36px', '1.2'],
                '5xl': ['48px', '1.15'],
                '6xl': ['60px', '1.1'],
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
