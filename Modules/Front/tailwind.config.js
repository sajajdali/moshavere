/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["Resources/views/**/*.blade.php", "Resources/aseets/src/**/*.js"],
  theme: {
    container: {
      center: true,
    },
    extend: {
      screens: {
        xs: "420px",
      },
      colors: {
        'primary': {
          'main': '#1766FF',
          'tint': {
            100: '#E8F0FF',
            200: '#BAD2FF',
            300: '#8BB3FF',
            400: '#5D94FF',
            500: '#2F76FF',
          },
          'fade': {
            100: '#1766FF1A',
            200: '#1766FF4D',
            300: '#1766FF80',
            400: '#1766FFB2',
            500: '#1766FFE5',
          }
        },
        'light': {
          'pink': '#FFCDCD',
          'purple': '#E2D5FF',
        },
        'green': '#0CCD88',
        'orange': '#FF7817',
        'blue-sky': '#74A3FF',
        'pink': '#FF22B0',
        'yellow': '#FFC63B',
        'red': '#FE5050',
        'secondary': {
          100: '#EEF2F6',
          200: '#D5DDE5',
          300: '#ADB9C7',
          400: '#66737F',
          500: '#272D37',
        }
      },
    },
  },
  plugins: [],
};
