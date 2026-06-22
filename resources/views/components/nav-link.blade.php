@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-4 border-sky-500 hc:border-yellow-300 dark:border-sky-400 text-sm font-medium leading-5 text-gray-900 hc:text-white dark:text-gray-100 focus:outline-none focus:border-sky-600 dark:focus:border-sky-300 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-4 border-transparent text-sm font-medium leading-5 text-gray-500 hc:text-white dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-sky-300 dark:hover:border-sky-600 hc:hover:border-yellow-300 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-sky-500 dark:focus:border-sky-400 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
