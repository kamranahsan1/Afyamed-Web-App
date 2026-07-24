<x-filament-panels::page>
    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Pending doctor and pharmacy accounts from Firestore. Approving a doctor syncs their public directory card.
        </p>

        @if (empty($pending))
            <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
                No pending verifications (or Firestore is unavailable).
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Docs</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($pending as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $row['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $row['email'] ?? $row['uid'] }}</div>
                                </td>
                                <td class="px-4 py-3 capitalize">{{ $row['role'] }}</td>
                                <td class="px-4 py-3">{{ $row['verification_status'] }}</td>
                                <td class="px-4 py-3">{{ count($row['documents'] ?? []) }}</td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <x-filament::button size="sm" color="success" wire:click="approve('{{ $row['uid'] }}')">
                                        Approve
                                    </x-filament::button>
                                    <x-filament::button size="sm" color="danger" wire:click="reject('{{ $row['uid'] }}')">
                                        Reject
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <x-filament::button wire:click="refreshPending" color="gray">
            Refresh
        </x-filament::button>
    </div>
</x-filament-panels::page>
