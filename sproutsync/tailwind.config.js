// tailwind.config.js
module.exports = {
  content: [
    "./*.php",
    "./**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        'emerald-400': '#34D399',
        'emerald-500': '#10B981',
        'emerald-600': '#059669',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      }
    },
  },
  plugins: [],
}