<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $heading }}
        </x-slot>

        @if (empty($rows))
            <p class="text-sm text-gray-500">No data</p>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($rows as $index => $row)
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500">#{{ $index + 1 }}</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['flavor'] }}</span>
                        <span class="text-sm text-gray-500">{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
