{{-- Simple Card Component for Flux Free --}}
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6']) }}>
    {{ $slot }}
</div>
