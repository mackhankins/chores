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

            <label class="flex items-end">
                <span class="flex items-center gap-2 pb-2 text-sm font-medium text-gray-950 dark:text-white">
                    <input type="checkbox" wire:model.live="missedOnly" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-white/5" />
                    Missed only
                </span>
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
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Rate</th>
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 dark:text-gray-400">Earned</th>
                        <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 sm:pe-6 dark:text-gray-400">Balance</th>
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
                            <td class="px-4 py-3.5 text-end">
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
                            <td class="px-4 py-3.5 text-end text-sm tabular-nums text-green-600 dark:text-green-400">
                                @if ($stat['earned'] > 0)
                                    ${{ number_format($stat['earned'], 2) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-end text-sm tabular-nums sm:pe-6">
                                @if ($stat['expenses'] !== null)
                                    <span class="{{ $stat['balance'] > 0 ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} font-medium">
                                        ${{ number_format($stat['balance'], 2) }}
                                    </span>
                                    <span class="block text-xs text-gray-400">
                                        ${{ number_format($stat['expenses'], 2) }} expenses
                                        @if ($stat['paid'] > 0)
                                            · ${{ number_format($stat['paid'], 2) }} paid
                                        @endif
                                    </span>
                                @elseif ($stat['earned'] > 0)
                                    <span class="text-green-600 dark:text-green-400 font-medium">${{ number_format($stat['earned'], 2) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Missed chores (instance-level) --}}
    @if ($missedOnly)
        <x-filament::section>
            <x-slot name="heading">Missed Chores</x-slot>
            <x-slot name="description">Past, unchecked chores &mdash; click to credit the child retroactively</x-slot>

            @if ($data['missedInstances']->isEmpty())
                <p class="py-4 text-sm text-gray-500 dark:text-gray-400">No missed chores in this range.</p>
            @else
                <div class="-mx-6 -mb-6 overflow-x-auto rounded-b-xl">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 sm:ps-6 dark:text-gray-400">Date</th>
                                @if (! $childFilter)
                                    <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 dark:text-gray-400">Child</th>
                                @endif
                                <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 dark:text-gray-400">Chore</th>
                                <th class="px-4 py-3 text-start text-sm font-medium text-gray-500 dark:text-gray-400">Room</th>
                                <th class="px-4 py-3 text-end text-sm font-medium text-gray-500 sm:pe-6 dark:text-gray-400"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @foreach ($data['missedInstances'] as $row)
                                <tr>
                                    <td class="px-4 py-3.5 text-sm tabular-nums text-gray-600 sm:ps-6 dark:text-gray-400">{{ $row['date']->format('D, M j') }}</td>
                                    @if (! $childFilter)
                                        <td class="px-4 py-3.5 text-sm">
                                            <div class="flex items-center gap-2.5">
                                                <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $row['child']->avatar_color }}"></span>
                                                <span class="font-medium text-gray-950 dark:text-white">{{ $row['child']->name }}</span>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3.5 text-sm font-medium text-gray-950 dark:text-white">{{ $row['chore']->name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400">{{ $row['room_name'] }}</td>
                                    <td class="px-4 py-3.5 text-end sm:pe-6">
                                        <x-filament::button
                                            size="xs"
                                            color="success"
                                            icon="heroicon-m-check"
                                            wire:click="markMissedComplete('{{ $row['child']->id }}', '{{ $row['chore']->id }}', '{{ $row['date']->toDateString() }}')"
                                        >
                                            Mark complete
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif

    {{-- Per-chore breakdown --}}
    @if (! $missedOnly && $data['perChoreStats'])
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
