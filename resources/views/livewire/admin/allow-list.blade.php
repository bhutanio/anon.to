<flux:main container>
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Allow/Block List Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage domain rules for content moderation
            </p>
        </div>

        {{-- Actions Bar --}}
        <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex gap-2">
                <flux:button
                    wire:click="showAddRuleForm"
                    variant="primary"
                    icon="plus"
                >
                    Add Rule
                </flux:button>

                <flux:button
                    wire:click="exportCsv"
                    variant="ghost"
                    icon="arrow-down-tray"
                    wire:loading.attr="disabled"
                    wire:target="exportCsv"
                >
                    Export CSV
                </flux:button>
            </div>

            <div class="flex gap-2 items-center">
                <flux:input
                    type="file"
                    wire:model="csvFile"
                    accept=".csv,.txt"
                />
                <flux:button
                    wire:click="importCsv"
                    variant="ghost"
                    icon="arrow-up-tray"
                    wire:loading.attr="disabled"
                    wire:target="importCsv,csvFile"
                    :disabled="!$csvFile"
                >
                    <span wire:loading.remove wire:target="importCsv">Import CSV</span>
                    <span wire:loading wire:target="importCsv">Importing...</span>
                </flux:button>
            </div>
        </div>

        @error('csvFile')
            <flux:callout variant="danger" class="mb-6">
                {{ $message }}
            </flux:callout>
        @enderror

        {{-- Test Utility --}}
        <flux:card class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Test Domain Matching</h3>
            <div class="flex gap-4 items-start">
                <div class="flex-1">
                    <flux:input
                        wire:model="testDomain"
                        placeholder="Enter domain to test (e.g., example.com)"
                    />
                </div>
                <flux:button
                    wire:click="testDomainMatch"
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="testDomainMatch"
                >
                    Test
                </flux:button>
                @if($testResult)
                    <flux:button
                        wire:click="clearTest"
                        variant="ghost"
                    >
                        Clear
                    </flux:button>
                @endif
            </div>

            @if($testResult)
                <div class="mt-4 p-4 rounded-lg {{ $testResult['matched'] ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' }}">
                    @if($testResult['matched'])
                        <div class="flex items-start gap-3">
                            <flux:icon.shield-exclamation class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="font-semibold text-red-900 dark:text-red-100">Match Found</h4>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                    Domain matches
                                    <flux:badge variant="danger">{{ ucfirst($testResult['rule']->type) }}</flux:badge>
                                    rule: <code class="text-xs bg-red-100 dark:bg-red-900/50 px-2 py-1 rounded">{{ $testResult['rule']->domain }}</code>
                                    ({{ $testResult['rule']->pattern_type }})
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start gap-3">
                            <flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="font-semibold text-green-900 dark:text-green-100">No Match</h4>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                    Domain does not match any active rules.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </flux:card>

        {{-- Rules Table --}}
        <flux:card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Domain
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Pattern
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Hits
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Added By
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
                        @forelse($rules as $rule)
                            <tr wire:key="rule-{{ $rule->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4">
                                    <code class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $rule->domain }}</code>
                                    @if($rule->reason)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $rule->reason }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="{{ $rule->type === 'allow' ? 'success' : 'danger' }}">
                                        {{ ucfirst($rule->type) }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="neutral">
                                        {{ ucfirst($rule->pattern_type) }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($rule->hit_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <flux:badge variant="{{ $rule->is_active ? 'success' : 'neutral' }}">
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $rule->admin?->name ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $rule->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <flux:button
                                            wire:click="toggleActive({{ $rule->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="arrow-path"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleActive({{ $rule->id }})"
                                        >
                                            {{ $rule->is_active ? 'Deactivate' : 'Activate' }}
                                        </flux:button>
                                        <flux:button
                                            wire:click="deleteRule({{ $rule->id }})"
                                            variant="danger"
                                            size="sm"
                                            icon="trash"
                                            wire:loading.attr="disabled"
                                            wire:target="deleteRule({{ $rule->id }})"
                                        >
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <flux:icon.shield-exclamation class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm">No rules found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($rules->hasPages())
                <div class="mt-4">
                    {{ $rules->links() }}
                </div>
            @endif
        </flux:card>

    {{-- Add Rule Modal --}}
    @if($showAddForm)
        <flux:modal wire:model="showAddForm">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Add New Rule</h2>

                <div class="space-y-4">
                    <div>
                        <flux:field>
                            <flux:label>Domain</flux:label>
                            <flux:input
                                wire:model="domain"
                                placeholder="example.com or *.example.com or regex pattern"
                            />
                            <flux:error name="domain" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:field>
                                <flux:label>Type</flux:label>
                                <flux:select wire:model="type">
                                    <option value="block">Block</option>
                                    <option value="allow">Allow</option>
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Pattern Type</flux:label>
                                <flux:select wire:model="patternType">
                                    <option value="exact">Exact Match</option>
                                    <option value="wildcard">Wildcard</option>
                                    <option value="regex">Regex</option>
                                </flux:select>
                                <flux:error name="patternType" />
                            </flux:field>
                        </div>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Reason (Optional)</flux:label>
                            <flux:textarea
                                wire:model="reason"
                                rows="3"
                                placeholder="Internal notes about why this rule was added..."
                                maxlength="500"
                            />
                            <flux:error name="reason" />
                        </flux:field>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="hideAddForm" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button
                        wire:click="addRule"
                        variant="primary"
                        icon="plus"
                        wire:loading.attr="disabled"
                        wire:target="addRule"
                    >
                        <span wire:loading.remove wire:target="addRule">Add Rule</span>
                        <span wire:loading wire:target="addRule">Adding...</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</flux:main>
