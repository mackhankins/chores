<x-filament-panels::page>
    @php
        $children = \App\Models\Child::all();
        $data = $this->getReportData();
    @endphp

    {{-- Filters --}}
    <x-filament::section>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <label>
                <span class="mb-3 block text-sm font-medium text-gray-950 dark:text-white">Period</span>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="periodFilter">
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>

            @if ($periodFilter === 'custom')
                <label>
                    <span class="mb-3 block text-sm font-medium text-gray-950 dark:text-white">From</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.live.debounce.500ms="startDate" />
                    </x-filament::input.wrapper>
                </label>
                <label>
                    <span class="mb-3 block text-sm font-medium text-gray-950 dark:text-white">To</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.live.debounce.500ms="endDate" />
                    </x-filament::input.wrapper>
                </label>
            @endif

            <label>
                <span class="mb-3 block text-sm font-medium text-gray-950 dark:text-white">Child</span>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="childFilter">
                        <option value="">All Children</option>
                        @foreach ($children as $child)
                            <option value="{{ $child->id }}">{{ $child->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>
        </div>

        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Showing {{ $data['startDate'] }} &mdash; {{ $data['endDate'] }}
        </p>
    </x-filament::section>

    {{-- Children summary --}}
    <x-filament::section>
        <x-slot name="heading">Completion by Child</x-slot>

        <div class="-mx-6 -mb-6 overflow-x-auto rounded-b-xl">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 sm:ps-6 dark:text-gray-400">Child</th>
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Assigned</th>
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Completed</th>
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Missed</th>
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 sm:pe-6 dark:text-gray-400">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @foreach ($data['childrenStats'] as $stat)
                        <tr>
                            <td class="px-4 py-3.5 sm:ps-6">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $stat['child']->avatar_color }}"></span>
                                    <span class="text-sm font-medium text-gray-950 dark:text-white">{{ $stat['child']->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-end text-sm tabular-nums text-gray-600 dark:text-gray-400">{{ $stat['total'] }}</td>
                            <td class="px-4 py-3.5 text-end text-sm tabular-nums text-gray-600 dark:text-gray-400">{{ $stat['completed'] }}</td>
                            <td class="px-4 py-3.5 text-end text-sm tabular-nums text-gray-600 dark:text-gray-400">{{ $stat['missed'] }}</td>
                            <td class="px-4 py-3.5 text-end sm:pe-6">
                                @php
                                    $badgeColor = match(true) {
                                        $stat['rate'] >= 80 => 'success',
                                        $stat['rate'] >= 50 => 'warning',
                                        default => 'danger',
                                    };
                                @endphp
                                <div class="flex justify-end">
                                    <x-filament::badge :color="$badgeColor">
                                        {{ $stat['rate'] }}%
                                    </x-filament::badge>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Per-chore breakdown --}}
    @if ($data['perChoreStats'])
        <x-filament::section>
            <x-slot name="heading">Chore Breakdown</x-slot>
            <x-slot name="description">Sorted by completion rate &mdash; worst first</x-slot>

            <div class="-mx-6 -mb-6 overflow-x-auto rounded-b-xl">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 sm:ps-6 dark:text-gray-400">Chore</th>
                            <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 dark:text-gray-400">Room</th>
                            <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Assigned</th>
                            <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Completed</th>
                            <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 sm:pe-6 dark:text-gray-400">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($data['perChoreStats'] as $stat)
                            <tr>
                                <td class="px-4 py-3.5 text-sm font-medium text-gray-950 sm:ps-6 dark:text-white">{{ $stat['chore_name'] }}</td>
                                <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400">{{ $stat['room_name'] }}</td>
                                <td class="px-4 py-3.5 text-end text-sm tabular-nums text-gray-600 dark:text-gray-400">{{ $stat['total'] }}</td>
                                <td class="px-4 py-3.5 text-end text-sm tabular-nums text-gray-600 dark:text-gray-400">{{ $stat['completed'] }}</td>
                                <td class="px-4 py-3.5 text-end sm:pe-6">
                                    @php
                                        $badgeColor = match(true) {
                                            $stat['rate'] >= 80 => 'success',
                                            $stat['rate'] >= 50 => 'warning',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <div class="flex justify-end">
                                        <x-filament::badge :color="$badgeColor">
                                            {{ $stat['rate'] }}%
                                        </x-filament::badge>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
