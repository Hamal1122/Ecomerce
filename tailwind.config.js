import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        'node_modules/preline/dist/*.js',
      ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#3D3BF3', // Ubah ini dengan warna utama yang diinginkan
                    hover: '#4335A7',   // Warna saat tombol di-hover
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('preline/plugin'),
    ],
};
