<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
    {{-- Header --}}
    <div>
        <flux:heading size="xl" class="mb-2">Dashboard</flux:heading>
        <flux:text>Manage your shortened links and notes</flux:text>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
                wire:click="switchTab('links')"
                class="flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition {{ $activeTab === 'links' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span>Links</span>
                <flux:badge size="sm" color="zinc" variant="outline">{{ $this->links->count() }}</flux:badge>
            </button>

            <button
                wire:click="switchTab('notes')"
                class="flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition {{ $activeTab === 'notes' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Notes</span>
                <flux:badge size="sm" color="zinc" variant="outline">{{ $this->notes->count() }}</flux:badge>
            </button>
        </nav>
    </div>

    {{-- Tab Content --}}
    <div wire:loading.remove wire:target="switchTab">
        @if($activeTab === 'links')
            {{-- Links Tab --}}
            @if($this->links->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 bg-white px-6 py-12 dark:border-zinc-700 dark:bg-zinc-900">
                    <svg class="h-12 w-12 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <flux:heading size="lg" class="mt-4">No links yet</flux:heading>
                    <flux:text class="mt-2 text-center">You haven't created any shortened links</flux:text>
                    <flux:button href="{{ route('home') }}" variant="primary" class="mt-6">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create your first link
                    </flux:button>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Hash</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Destination</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Views</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Created</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                                @foreach($this->links as $link)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <a href="/{{ $link->hash }}" class="font-mono text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300" target="_blank">
                                                {{ $link->hash }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs truncate text-sm text-zinc-900 dark:text-zinc-100">{{ $link->url }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $link->views }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $link->created_at->diffForHumans() }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="/{{ $link->hash }}" target="_blank" class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            {{-- Notes Tab --}}
            @if($this->notes->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 bg-white px-6 py-12 dark:border-zinc-700 dark:bg-zinc-900">
                    <svg class="h-12 w-12 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <flux:heading size="lg" class="mt-4">No notes yet</flux:heading>
                    <flux:text class="mt-2 text-center">You haven't created any notes</flux:text>
                    <flux:button href="{{ route('notes.create') }}" variant="primary" class="mt-6">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create your first note
                    </flux:button>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Hash</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Views</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Expires</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Created</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                                @foreach($this->notes as $note)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <a href="/n/{{ $note->hash }}" class="font-mono text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300" target="_blank">
                                                {{ $note->hash }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs truncate text-sm text-zinc-900 dark:text-zinc-100">
                                                {{ $note->title ?: '(Untitled)' }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $note->views }}
                                            @if($note->view_limit)
                                                <span class="text-xs {{ $note->views >= $note->view_limit * 0.8 ? 'text-orange-600 dark:text-orange-400' : '' }}">
                                                    / {{ $note->view_limit }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            @if($note->expires_at)
                                                @php
                                                    $hoursRemaining = now()->diffInHours($note->expires_at, false);
                                                @endphp
                                                <span class="{{ $hoursRemaining < 24 ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                                                    {{ $note->expires_at->diffForHumans() }}
                                                </span>
                                            @else
                                                <span class="text-zinc-500 dark:text-zinc-400">Never</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $note->created_at->diffForHumans() }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2" x-data="{ copied: @entangle('copiedHash') }">
                                                <a href="/n/{{ $note->hash }}" target="_blank" class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                                <button
                                                    type="button"
                                                    @click="navigator.clipboard.writeText('{{ url('/n/' . $note->hash) }}').then(() => { copied = '{{ $note->hash }}'; $wire.copyNoteUrl('{{ $note->hash }}'); setTimeout(() => copied = null, 2000); })"
                                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                >
                                                    <svg x-show="copied !== '{{ $note->hash }}'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                    <svg x-show="copied === '{{ $note->hash }}'" x-cloak class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="confirmNoteDeletion({{ $note->id }})"
                                                    class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Loading State --}}
    <div wire:loading wire:target="switchTab" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($confirmingDeletion)
        @php
            $noteToDelete = $this->notes->firstWhere('id', $confirmingDeletion);
        @endphp
        <flux:modal name="delete-note-modal" variant="flyout" wire:model="confirmingDeletion">
            <form wire:submit.prevent="deleteNote({{ $confirmingDeletion }})">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete Note</flux:heading>
                        <flux:text>Are you sure you want to delete this note? This action cannot be undone.</flux:text>
                    </div>

                    @if($noteToDelete)
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-zinc-500 dark:text-zinc-400">Hash:</span>
                                    <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $noteToDelete->hash }}</span>
                                </div>
                                @if($noteToDelete->title)
                                    <div class="flex justify-between">
                                        <span class="text-zinc-500 dark:text-zinc-400">Title:</span>
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $noteToDelete->title }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-zinc-500 dark:text-zinc-400">Created:</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $noteToDelete->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-3">
                        <flux:button type="button" variant="ghost" wire:click="cancelDeletion" class="flex-1">Cancel</flux:button>
                        <flux:button type="submit" variant="danger" class="flex-1">Delete</flux:button>
                    </div>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
