/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./public/**/*.{html,js,php}', './partials/**/*.{html,js,php}', './forms/**/*.{html,js,php}', ],
  safelist: [
    {
      pattern: /grid-cols-\d+/,
      variants: ['md'],
    },
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

