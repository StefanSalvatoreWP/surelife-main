/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  safelist: [
    'bg-white',
    'rounded-xl',
    'shadow-lg',
    'overflow-hidden',
    'mb-6',
    'bg-gradient-to-r',
    'from-gray-50',
    'to-gray-100',
    'px-6',
    'py-4',
    'py-6',
    'p-6',
    'border-b',
    'border-gray-200',
    'text-lg',
    'font-bold',
    'text-gray-800',
    'flex',
    'items-center',
    'w-5',
    'h-5',
    'mr-2',
    'text-indigo-600',
    'grid',
    'grid-cols-1',
    'md:grid-cols-2',
    'lg:grid-cols-3',
    'lg:grid-cols-4',
    'gap-4',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
      },
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

