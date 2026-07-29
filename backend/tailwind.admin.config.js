/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./resources/views/admin/**/*.blade.php', './resources/views/seller/**/*.blade.php'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
        mono: ['IBM Plex Mono', 'ui-monospace', 'monospace'],
      },
      colors: {
        brand: {
          50: '#eef4ff', 100: '#dbe7ff', 200: '#bdd4ff', 300: '#92b8ff', 400: '#5f8fff',
          500: '#3868f6', 600: '#2748ea', 700: '#1f37c4', 800: '#1f309b', 900: '#202c79', 950: '#161b4d',
        },
        ink: {
          50: '#f7f8fa', 100: '#eef0f4', 200: '#dde1e8', 300: '#c1c8d3', 400: '#98a2b3',
          500: '#717a8c', 600: '#535b6e', 700: '#3f4558', 800: '#262b3b', 900: '#171a24', 950: '#0d0f16',
        },
      },
      boxShadow: {
        card: '0 1px 2px 0 rgba(23,26,36,0.04), 0 1px 6px -1px rgba(23,26,36,0.06)',
        popover: '0 10px 38px -10px rgba(23,26,36,0.25), 0 10px 20px -15px rgba(23,26,36,0.15)',
      },
      animation: {
        'fade-in': 'fadeIn .35s ease forwards',
        'slide-up': 'slideUp .35s ease forwards',
        'scale-in': 'scaleIn .18s ease forwards',
        shimmer: 'shimmer 1.6s infinite',
      },
      keyframes: {
        fadeIn: { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
        slideUp: { '0%': { opacity: 0, transform: 'translateY(8px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
        scaleIn: { '0%': { opacity: 0, transform: 'scale(.96)' }, '100%': { opacity: 1, transform: 'scale(1)' } },
        shimmer: { '0%': { backgroundPosition: '-400px 0' }, '100%': { backgroundPosition: '400px 0' } },
      },
    },
  },
  safelist: [
    { pattern: /^(bg|text|border|ring)-(brand|ink|emerald|amber|rose|violet|sky|indigo|orange)-(50|100|200|400|500|600|700)$/, variants: ['dark'] },
  ],
  plugins: [],
};
