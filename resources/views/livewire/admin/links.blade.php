<flux:main container>
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Link Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage all shortened links across the platform
            </p>
        </div>

        {{-- Search and Filters --}}
        <flux:card class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by hash, URL, user name or email..."
                        icon="magnifying-glass"
                    />
                </div>
                <div class="sm:w-48">
                    <flux:select wire:model.live="statusFilter">
                        <option value="all">All Links</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="reported">Reported</option>
                    </flux:select>
                </div>
            </div>
        </flux:card>

        {{-- Bulk Actions --}}
        @if(!empty($selectedLinks))
            <flux:card class="mb-6 bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-indigo-900 dark:text-indigo-100">
                        {{ count($selectedLinks) }} link(s) selected
                    </span>
                    <div class="flex gap-2">
                        <flux:button
                            wire:click="bulkToggleActive"
                            variant="ghost"
                            size="sm"
                            icon="arrow-path"
                        >
                            Toggle Status
                        </flux:button>
                        <flux:button
                            wire:click="confirmBulkDelete"
                            variant="danger"
                            size="sm"
                            icon="trash"
                        >
                            Delete Selected
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @endif

        {{-- Links Table --}}
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left">
                                <flux:checkbox
                                    wire:model.live="selectAll"
                                    label=""
                                />
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Hash
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                URL
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Visits
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($links as $link)
                            <tr wire:key="link-{{ $link->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:checkbox
                                        wire:model.live="selectedLinks"
                                        value="{{ $link->id }}"
                                        label=""
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $link->hash }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100 truncate max-w-xs" title="{{ $link->full_url }}">
                                        {{ $link->full_url }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($link->user)
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $link->user->name }}</div>
                                            <div class="text-gray-500 dark:text-gray-400">{{ $link->user->email }}</div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Anonymous</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($link->visits) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-2">
                                        <flux:badge variant="{{ $link->is_active ? 'success' : 'danger' }}">
                                            {{ $link->is_active ? 'Active' : 'Inactive' }}
                                        </flux:badge>
                                        @if($link->is_reported)
                                            <flux:badge variant="warning">
                                                Reported
                                            </flux:badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $link->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <flux:button
                                            wire:click="viewLink({{ $link->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                            wire:loading.attr="disabled"
                                            wire:target="viewLink({{ $link->id }})"
                                        >
                                            View
                                        </flux:button>
                                        <flux:button
                                            wire:click="toggleActive({{ $link->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="arrow-path"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleActive({{ $link->id }})"
                                        >
                                            {{ $link->is_active ? 'Deactivate' : 'Activate' }}
                                        </flux:button>
                                        <flux:button
                                            wire:click="confirmDelete({{ $link->id }})"
                                            variant="danger"
                                            size="sm"
                                            icon="trash"
                                        >
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <flux:icon.link class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm">No links found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($links->hasPages())
                <div class="mt-4">
                    {{ $links->links() }}
                </div>
            @endif
        </flux:card>

    {{-- View Link Details Modal --}}
    @if($viewingLinkId && $this->viewingLink)
        <flux:modal wire:model="viewingLinkId" class="max-w-3xl">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Link Details</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Hash</label>
                        <code class="block mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $this->viewingLink->hash }}</code>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full URL</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 break-all">{{ $this->viewingLink->full_url }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Visits</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->viewingLink->visits) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <div class="mt-1">
                                <flux:badge variant="{{ $this->viewingLink->is_active ? 'success' : 'danger' }}">
                                    {{ $this->viewingLink->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>

                    @if($this->viewingLink->user)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Created By</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $this->viewingLink->user->name }} ({{ $this->viewingLink->user->email }})
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Created At</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingLink->created_at->format('M d, Y H:i:s') }}</p>
                        </div>

                        @if($this->viewingLink->last_visited_at)
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Visited</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingLink->last_visited_at->format('M d, Y H:i:s') }}</p>
                            </div>
                        @endif
                    </div>

                    @if($this->viewingLink->reports->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reports ({{ $this->viewingLink->reports->count() }})</label>
                            <div class="mt-2 space-y-2">
                                @foreach($this->viewingLink->reports as $report)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <flux:badge variant="warning">{{ ucfirst($report->category) }}</flux:badge>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $report->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @if($report->comment)
                                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $report->comment }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="closeView" variant="ghost">
                        Close
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <flux:modal wire:model="showDeleteConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Link</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to delete this link? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelDelete" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="deleteLink"
                        variant="danger"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="deleteLink"
                    >
                        <span wire:loading.remove wire:target="deleteLink">Delete Link</span>
                        <span wire:loading wire:target="deleteLink">Deleting...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if($showBulkDeleteConfirm)
        <flux:modal wire:model="showBulkDeleteConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Multiple Links</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to delete {{ count($selectedLinks) }} link(s)? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelBulkDelete" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="bulkDelete"
                        variant="danger"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="bulkDelete"
                    >
                        <span wire:loading.remove wire:target="bulkDelete">Delete {{ count($selectedLinks) }} Link(s)</span>
                        <span wire:loading wire:target="bulkDelete">Deleting...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</flux:main>
