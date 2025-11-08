<flux:main container>
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage user accounts and permissions
            </p>
        </div>

        {{-- Search --}}
        <flux:card class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name or email..."
                        icon="magnifying-glass"
                    />
                </div>
            </div>
        </flux:card>

        {{-- Users Table --}}
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Links
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Notes
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
                        @forelse($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($user->links_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($user->notes_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex gap-2">
                                        @if($user->is_admin)
                                            <flux:badge variant="primary">
                                                Admin
                                            </flux:badge>
                                        @endif
                                        @if($user->is_verified)
                                            <flux:badge variant="success">
                                                Verified
                                            </flux:badge>
                                        @endif
                                        @if($user->banned_at)
                                            <flux:badge variant="danger">
                                                Banned
                                            </flux:badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <flux:button
                                            wire:click="viewUser({{ $user->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                        >
                                            View
                                        </flux:button>

                                        @if($user->banned_at)
                                            <flux:button
                                                wire:click="unbanUser({{ $user->id }})"
                                                variant="ghost"
                                                size="sm"
                                                icon="shield-check"
                                                wire:loading.attr="disabled"
                                            >
                                                Unban
                                            </flux:button>
                                        @else
                                            @if($user->id !== auth()->id() && !$user->is_admin)
                                                <flux:button
                                                    wire:click="confirmBan({{ $user->id }})"
                                                    variant="danger"
                                                    size="sm"
                                                    icon="shield-exclamation"
                                                >
                                                    Ban
                                                </flux:button>
                                            @endif
                                        @endif

                                        @if(!$user->is_verified)
                                            <flux:button
                                                wire:click="confirmVerify({{ $user->id }})"
                                                variant="ghost"
                                                size="sm"
                                                icon="check-badge"
                                            >
                                                Verify
                                            </flux:button>
                                        @else
                                            <flux:button
                                                wire:click="unverifyUser({{ $user->id }})"
                                                variant="ghost"
                                                size="sm"
                                                wire:loading.attr="disabled"
                                            >
                                                Unverify
                                            </flux:button>
                                        @endif

                                        @if(!$user->is_admin && $user->id !== auth()->id())
                                            <flux:button
                                                wire:click="confirmPromote({{ $user->id }})"
                                                variant="primary"
                                                size="sm"
                                                icon="star"
                                            >
                                                Promote
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <flux:icon.users class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm">No users found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </flux:card>

    {{-- View User Profile Modal --}}
    @if($viewingUserId && $this->viewingUser)
        <flux:modal wire:model="viewingUserId" class="max-w-4xl">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">User Profile</h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingUser->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingUser->email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <div class="mt-1 flex gap-2">
                                @if($this->viewingUser->is_admin)
                                    <flux:badge variant="primary">Admin</flux:badge>
                                @endif
                                @if($this->viewingUser->is_verified)
                                    <flux:badge variant="success">Verified</flux:badge>
                                @endif
                                @if($this->viewingUser->banned_at)
                                    <flux:badge variant="danger">Banned</flux:badge>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">API Rate Limit</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingUser->api_rate_limit }} per hour</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Links</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->viewingUser->links_count) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->viewingUser->notes_count) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reports</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->viewingUser->reports_count) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Created At</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingUser->created_at->format('M d, Y H:i:s') }}</p>
                        </div>
                        @if($this->viewingUser->banned_at)
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Banned At</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $this->viewingUser->banned_at->format('M d, Y H:i:s') }}</p>
                            </div>
                        @endif
                    </div>

                    @if($this->viewingUser->links->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Recent Links</label>
                            <div class="mt-2 space-y-2">
                                @foreach($this->viewingUser->links as $link)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <code class="text-xs font-mono text-gray-900 dark:text-gray-100">{{ $link->hash }}</code>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $link->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 truncate">{{ $link->full_url }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($this->viewingUser->notes->isNotEmpty())
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Recent Notes</label>
                            <div class="mt-2 space-y-2">
                                @foreach($this->viewingUser->notes as $note)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <code class="text-xs font-mono text-gray-900 dark:text-gray-100">{{ $note->hash }}</code>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $note->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $note->title ?: 'Untitled' }}</p>
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

    {{-- Ban Confirmation Modal --}}
    @if($showBanConfirm)
        <flux:modal wire:model="showBanConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.shield-exclamation class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ban User</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to ban this user? This will deactivate all their links and notes.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelBan" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="banUser"
                        variant="danger"
                        icon="shield-exclamation"
                        wire:loading.attr="disabled"
                        wire:target="banUser"
                    >
                        <span wire:loading.remove wire:target="banUser">Ban User</span>
                        <span wire:loading wire:target="banUser">Banning...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Verify Confirmation Modal --}}
    @if($showVerifyConfirm)
        <flux:modal wire:model="showVerifyConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <flux:icon.check-badge class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Verify User</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            This will mark the user as verified and increase their API rate limit to 500 requests per hour.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelVerify" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="verifyUser"
                        variant="primary"
                        icon="check-badge"
                        wire:loading.attr="disabled"
                        wire:target="verifyUser"
                    >
                        <span wire:loading.remove wire:target="verifyUser">Verify User</span>
                        <span wire:loading wire:target="verifyUser">Verifying...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Promote Confirmation Modal --}}
    @if($showPromoteConfirm)
        <flux:modal wire:model="showPromoteConfirm">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <flux:icon.exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Promote to Admin</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Are you sure you want to promote this user to admin? They will have full access to all admin features.
                        </p>
                        <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">
                            This action should only be performed for trusted users.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelPromote" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="promoteUser"
                        variant="primary"
                        icon="star"
                        wire:loading.attr="disabled"
                        wire:target="promoteUser"
                    >
                        <span wire:loading.remove wire:target="promoteUser">Promote to Admin</span>
                        <span wire:loading wire:target="promoteUser">Promoting...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</flux:main>
