<flux:main container>
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Platform statistics and system health at a glance
            </p>
        </div>

        {{-- Privacy Warning --}}
        <flux:callout variant="warning" class="mb-8">
            <div class="flex items-start gap-3">
                <flux:icon.exclamation-triangle variant="solid" class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-semibold mb-1">Data Privacy Responsibility</h3>
                    <p class="text-sm">
                        As an administrator, you have access to sensitive platform data. Never share user information externally.
                        All IP addresses are hashed. User-generated content should only be accessed when moderating reports.
                    </p>
                </div>
            </div>
        </flux:callout>

        {{-- Statistics Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" wire:poll.60s>
            {{-- Total Links --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Links</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ number_format($this->stats['total_links']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ number_format($this->stats['active_links']) }} active
                        </p>
                    </div>
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <flux:icon.link class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
            </flux:card>

            {{-- Total Notes --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Notes</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ number_format($this->stats['total_notes']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ number_format($this->stats['active_notes']) }} active
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <flux:icon.document-text class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </flux:card>

            {{-- Total Users --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ number_format($this->stats['total_users']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ number_format($this->stats['verified_users']) }} verified
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <flux:icon.users class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </flux:card>

            {{-- Pending Reports --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Reports</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ number_format($this->stats['pending_reports']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Require attention
                        </p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <flux:icon.flag class="w-8 h-8 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- System Health --}}
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">System Health</h2>
                </div>

                <div class="space-y-4">
                    {{-- Disk Usage --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Disk Usage</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $this->health['disk_usage_percent'] }}% used
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div
                                class="h-2 rounded-full {{ $this->health['disk_usage_percent'] > 80 ? 'bg-red-600' : 'bg-green-600' }}"
                                style="width: {{ $this->health['disk_usage_percent'] }}%"
                            ></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ $this->health['disk_free_gb'] }} GB free
                        </p>
                    </div>

                    {{-- Cache Status --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cache</span>
                        <flux:badge variant="{{ $this->health['cache_working'] ? 'success' : 'danger' }}">
                            {{ $this->health['cache_working'] ? 'Working' : 'Not Working' }}
                        </flux:badge>
                    </div>
                </div>
            </flux:card>

            {{-- Quick Actions --}}
            <flux:card>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
                </div>

                <div class="space-y-2">
                    <flux:button href="{{ route('admin.reports') }}" variant="ghost" class="w-full justify-start" icon="flag">
                        View Pending Reports
                        @if($this->stats['pending_reports'] > 0)
                            <flux:badge variant="danger" class="ml-auto">{{ $this->stats['pending_reports'] }}</flux:badge>
                        @endif
                    </flux:button>

                    <flux:button href="{{ route('admin.users') }}" variant="ghost" class="w-full justify-start" icon="users">
                        Manage Users
                    </flux:button>

                    <flux:button href="{{ route('admin.links') }}" variant="ghost" class="w-full justify-start" icon="link">
                        View Links
                    </flux:button>

                    <flux:button href="{{ route('admin.notes') }}" variant="ghost" class="w-full justify-start" icon="document-text">
                        View Notes
                    </flux:button>

                    <flux:button href="{{ route('admin.allowlist') }}" variant="ghost" class="w-full justify-start" icon="shield-exclamation">
                        Manage Allow List
                    </flux:button>
                </div>
            </flux:card>
        </div>
</flux:main>
