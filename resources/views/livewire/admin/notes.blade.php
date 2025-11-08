<flux:main container>
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Note Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage all notes across the platform
            </p>
        </div>

        {{-- Search and Filters --}}
        <flux:card class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by hash, title, content..."
                        icon="magnifying-glass"
                    />
                </div>
                <div class="sm:w-48">
                    <flux:select wire:model.live="statusFilter">
                        <option value="all">All Notes</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="reported">Reported</option>
                        <option value="expired">Expired</option>
                    </flux:select>
                </div>
            </div>
        </flux:card>

        {{-- Notes Table --}}
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Hash
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Title
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Content Preview
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Views
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
                        @forelse($notes as $note)
                            <tr wire:key="note-{{ $note->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $note->hash }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100 truncate max-w-xs" title="{{ $note->title }}">
                                        {{ $note->title ?: 'Untitled' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs" title="{{ $note->content }}">
                                        {{ $this->getContentPreview($note->content, 50) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($note->user)
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $note->user->name }}</div>
                                            <div class="text-gray-500 dark:text-gray-400">{{ $note->user->email }}</div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Anonymous</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($note->views) }}
                                    @if($note->view_limit)
                                        <span class="text-gray-500 dark:text-gray-400">/ {{ number_format($note->view_limit) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-2">
                                        <flux:badge variant="{{ $note->is_active ? 'success' : 'danger' }}">
                                            {{ $note->is_active ? 'Active' : 'Inactive' }}
                                        </flux:badge>
                                        @if($note->is_reported)
                                            <flux:badge variant="warning">
                                                Reported
                                            </flux:badge>
                                        @endif
                                        @if($note->expires_at && $note->expires_at->isPast())
                                            <flux:badge variant="neutral">
                                                Expired
                                            </flux:badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $note->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <flux:button
                                            wire:click="viewNote({{ $note->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                            wire:loading.attr="disabled"
                                            wire:target="viewNote({{ $note->id }})"
                                        >
                                            View
                                        </flux:button>
                                        <flux:button
                                            wire:click="confirmDelete({{ $note->id }})"
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
                                    <flux:icon.document-text class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm">No notes found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($notes->hasPages())
                <div class="mt-4">
                    {{ $notes->links() }}
                </div>
            @endif
        </flux:card>

    {{-- View Note Details Modal --}}
    @if($viewingNoteId && $this->viewingNote)
        <flux:modal wire:model="viewingNoteId" class="max-w-4xl">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Note Details</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Hash</label>
                        <code class="block mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $this->viewingNote->hash }}</code>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingNote->title ?: 'Untitled' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
                        <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg max-h-96 overflow-y-auto">
                            <pre class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words">{{ $this->viewingNote->content }}</pre>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Views</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ number_format($this->viewingNote->views) }}
                                @if($this->viewingNote->view_limit)
                                    / {{ number_format($this->viewingNote->view_limit) }} limit
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <div class="mt-1">
                                <flux:badge variant="{{ $this->viewingNote->is_active ? 'success' : 'danger' }}">
                                    {{ $this->viewingNote->is_active ? 'Active' : 'Inactive' }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Character Count</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->viewingNote->char_count) }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Line Count</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->viewingNote->line_count) }}</p>
                        </div>
                    </div>

                    @if($this->viewingNote->user)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Created By</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $this->viewingNote->user->name }} ({{ $this->viewingNote->user->email }})
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Created At</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingNote->created_at->format('M d, Y H:i:s') }}</p>
                        </div>

                        @if($this->viewingNote->expires_at)
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Expires At</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $this->viewingNote->expires_at->format('M d, Y H:i:s') }}
                                    @if($this->viewingNote->expires_at->isPast())
                                        <flux:badge variant="danger" class="ml-2">Expired</flux:badge>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($this->viewingNote->reports->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reports ({{ $this->viewingNote->reports->count() }})</label>
                            <div class="mt-2 space-y-2">
                                @foreach($this->viewingNote->reports as $report)
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Note</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to delete this note? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelDelete" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="deleteNote"
                        variant="danger"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="deleteNote"
                    >
                        <span wire:loading.remove wire:target="deleteNote">Delete Note</span>
                        <span wire:loading wire:target="deleteNote">Deleting...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</flux:main>
