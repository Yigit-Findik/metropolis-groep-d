@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 hc:border-white hc:bg-black hc:text-white hc:placeholder:text-neutral-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 hc:focus:border-yellow-400 dark:focus:border-indigo-600 focus:ring-indigo-500 hc:focus:ring-yellow-400 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}>
