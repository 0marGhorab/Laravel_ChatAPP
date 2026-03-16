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
            colors: {
                primary: {
                    DEFAULT: '#AE75DA',
                    light: '#C49AE8',
                    dark: '#9B5DC4',
                },
                secondary: {
                    DEFAULT: '#9112BC',
                    light: '#A83DD4',
                    dark: '#730E96',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-in-right': {
                    '0%': { opacity: '0', transform: 'translateX(12px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                'scale-in': {
                    '0%': { opacity: '0', transform: 'scale(0.96)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                'pulse-soft': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.85' },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.3s ease-out forwards',
                'fade-in-up': 'fade-in-up 0.4s ease-out forwards',
                'slide-in-right': 'slide-in-right 0.35s ease-out forwards',
                'scale-in': 'scale-in 0.25s ease-out forwards',
                'pulse-soft': 'pulse-soft 2s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
