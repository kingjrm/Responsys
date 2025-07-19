/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./*.html",          // For any root-level HTML files
      "./*.php",           // Root-level PHP files like index.php, dashboard.php
      "./**/*.php",        // All PHP files inside subfolders
      "./js/**/*.js",      // JS files, especially if you're using dynamic class manipulation
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Poppins", "sans-serif"],
      },
    }
  },
  darkMode: "false", // or 'media' or 'class'
  plugins: [],
};