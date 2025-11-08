<flux:main container>
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Report Queue</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Review and moderate user-submitted reports
            </p>
        </div>

        {{-- Filters --}}
        <flux:card class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="sm:w-48">
                    <flux:select wire:model.live="statusFilter">
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                        <option value="all">All</option>
                    </flux:select>
                </div>
            </div>
        </flux:card>

        {{-- Reports Table --}}
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Content
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Category
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Reporter
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
                        @forelse($reports as $report)
                            <tr wire:key="report-{{ $report->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="{{ $report->reportable_type === 'App\\Models\\Link' ? 'primary' : 'success' }}">
                                        {{ class_basename($report->reportable_type) }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4">
                                    @if($report->reportable)
                                        @if($report->reportable_type === 'App\Models\Link')
                                            <div class="text-sm text-gray-900 dark:text-gray-100 truncate max-w-xs">
                                                {{ $report->reportable->full_url }}
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-900 dark:text-gray-100 truncate max-w-xs">
                                                {{ $report->reportable->title ?: 'Untitled Note' }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Content Deleted</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="warning">
                                        {{ ucfirst($report->category) }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $report->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="{{ match($report->status) {
                                        'pending' => 'warning',
                                        'resolved' => 'success',
                                        'dismissed' => 'neutral',
                                        default => 'neutral'
                                    } }}">
                                        {{ ucfirst($report->status) }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $report->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <flux:button
                                            wire:click="viewReport({{ $report->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                        >
                                            View
                                        </flux:button>

                                        @if($report->status === 'pending')
                                            <flux:button
                                                wire:click="confirmDeleteContent({{ $report->id }})"
                                                variant="danger"
                                                size="sm"
                                                icon="trash"
                                            >
                                                Delete
                                            </flux:button>

                                            <flux:button
                                                wire:click="dismissReport({{ $report->id }})"
                                                variant="ghost"
                                                size="sm"
                                                wire:loading.attr="disabled"
                                            >
                                                Dismiss
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <flux:icon.flag class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm">No reports found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($reports->hasPages())
                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            @endif
        </flux:card>

    {{-- View Report Details Modal --}}
    @if($viewingReportId && $this->viewingReport)
        <flux:modal wire:model="viewingReportId" class="max-w-4xl">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Report Details</h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <div class="mt-1">
                                <flux:badge variant="{{ $this->viewingReport->reportable_type === 'App\\Models\\Link' ? 'primary' : 'success' }}">
                                    {{ class_basename($this->viewingReport->reportable_type) }}
                                </flux:badge>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <div class="mt-1">
                                <flux:badge variant="warning">
                                    {{ ucfirst($this->viewingReport->category) }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>

                    @if($this->viewingReport->reportable)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reported Content</label>
                            <div class="mt-1 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                @if($this->viewingReport->reportable_type === 'App\Models\Link')
                                    <p class="text-sm text-gray-900 dark:text-gray-100 break-all">
                                        {{ $this->viewingReport->reportable->full_url }}
                                    </p>
                                @else
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                        {{ $this->viewingReport->reportable->title ?: 'Untitled' }}
                                    </p>
                                    <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words max-h-48 overflow-y-auto">{{ Str::limit($this->viewingReport->reportable->content, 500) }}</pre>
                                @endif
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reported Content</label>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Content has been deleted</p>
                        </div>
                    @endif

                    @if($this->viewingReport->comment)
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reporter Comment</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingReport->comment }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reporter Email</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingReport->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <div class="mt-1">
                                <flux:badge variant="{{ match($this->viewingReport->status) {
                                    'pending' => 'warning',
                                    'resolved' => 'success',
                                    'dismissed' => 'neutral',
                                    default => 'neutral'
                                } }}">
                                    {{ ucfirst($this->viewingReport->status) }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Submitted</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingReport->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                        @if($this->viewingReport->dealt_at)
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Dealt At</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $this->viewingReport->dealt_at->format('M d, Y H:i:s') }}
                                    @if($this->viewingReport->dealtBy)
                                        <span class="text-gray-500 dark:text-gray-400">by {{ $this->viewingReport->dealtBy->name }}</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Admin Notes
                            <span class="text-gray-500 dark:text-gray-400">({{ $this->adminNotesCharCount }}/{{ $maxAdminNotesLength }})</span>
                        </label>
                        <flux:textarea
                            wire:model.live="adminNotes"
                            rows="4"
                            placeholder="Add internal notes about this report..."
                            class="mt-1"
                            maxlength="{{ $maxAdminNotesLength }}"
                        />
                        <flux:button
                            wire:click="saveAdminNotes"
                            variant="primary"
                            size="sm"
                            class="mt-2"
                            wire:loading.attr="disabled"
                            wire:target="saveAdminNotes"
                        >
                            <span wire:loading.remove wire:target="saveAdminNotes">Save Notes</span>
                            <span wire:loading wire:target="saveAdminNotes">Saving...</span>
                        </flux:button>
                    </div>
                </div>

                <div class="mt-6 flex justify-between gap-3">
                    <div class="flex gap-2">
                        @if($this->viewingReport->status === 'pending')
                            @if($this->viewingReport->reportable)
                                <flux:button
                                    wire:click="confirmDeleteContent({{ $this->viewingReport->id }})"
                                    variant="danger"
                                    icon="trash"
                                >
                                    Delete Content
                                </flux:button>

                                @if($this->viewingReport->reportable->user)
                                    <flux:button
                                        wire:click="confirmBanUser({{ $this->viewingReport->id }})"
                                        variant="danger"
                                        icon="shield-exclamation"
                                    >
                                        Ban User
                                    </flux:button>
                                @endif
                            @endif

                            <flux:button
                                wire:click="dismissReport({{ $this->viewingReport->id }})"
                                variant="ghost"
                            >
                                Dismiss
                            </flux:button>
                        @endif
                    </div>

                    <flux:button wire:click="closeView" variant="ghost">
                        Close
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Delete Content Confirmation Modal --}}
    @if($showDeleteContentConfirm)
        <flux:modal wire:model="showDeleteContentConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Reported Content</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to delete this content? This action cannot be undone. The report will be marked as resolved.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelDeleteContent" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="deleteContent"
                        variant="danger"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="deleteContent"
                    >
                        <span wire:loading.remove wire:target="deleteContent">Delete Content</span>
                        <span wire:loading wire:target="deleteContent">Deleting...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Ban User Confirmation Modal --}}
    @if($showBanUserConfirm)
        <flux:modal wire:model="showBanUserConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.shield-exclamation class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ban Content Creator</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to ban the user who created this content? This will deactivate all their links and notes, and the report will be marked as resolved.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelBanUser" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="banContentCreator"
                        variant="danger"
                        icon="shield-exclamation"
                        wire:loading.attr="disabled"
                        wire:target="banContentCreator"
                    >
                        <span wire:loading.remove wire:target="banContentCreator">Ban User</span>
                        <span wire:loading wire:target="banContentCreator">Banning...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</flux:main>
