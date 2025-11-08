<?php

namespace App\Livewire\Admin;

use App\Models\AllowList as AllowListModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AllowList extends Component
{
    use WithFileUploads, WithPagination;

    public string $domain = '';

    public string $type = 'block';

    public string $patternType = 'exact';

    public string $reason = '';

    public ?TemporaryUploadedFile $csvFile = null;

    public string $testDomain = '';

    public ?array $testResult = null;

    public bool $showAddForm = false;

    /**
     * Get the allow list query.
     */
    protected function getAllowListQuery(): Builder
    {
        return AllowListModel::query()
            ->with('admin:id,name')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Show add form.
     */
    public function showAddRuleForm(): void
    {
        $this->authorize('create', AllowListModel::class);
        $this->showAddForm = true;
    }

    /**
     * Hide add form.
     */
    public function hideAddForm(): void
    {
        $this->showAddForm = false;
        $this->reset(['domain', 'type', 'patternType', 'reason']);
        $this->resetValidation();
    }

    /**
     * Add a new rule.
     */
    public function addRule(): void
    {
        $this->authorize('create', AllowListModel::class);

        $validated = $this->validate([
            'domain' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:allow,block'],
            'patternType' => ['required', 'in:exact,wildcard,regex'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->patternType === 'regex') {
            $isValid = @preg_match($this->domain, '') !== false;

            if (! $isValid) {
                $this->addError('domain', 'The regex pattern is invalid.');

                return;
            }
        }

        AllowListModel::create([
            'domain' => $validated['domain'],
            'type' => $validated['type'],
            'pattern_type' => $validated['patternType'],
            'reason' => $validated['reason'],
            'is_active' => true,
            'hit_count' => 0,
            'added_by' => auth()->id(),
        ]);

        $this->reset(['domain', 'type', 'patternType', 'reason', 'showAddForm']);
        $this->dispatch('rule-added');
    }

    /**
     * Toggle rule active status.
     */
    public function toggleActive(int $ruleId): void
    {
        $rule = AllowListModel::findOrFail($ruleId);
        $this->authorize('update', $rule);

        $rule->update([
            'is_active' => ! $rule->is_active,
        ]);

        $this->dispatch('rule-updated');
    }

    /**
     * Delete a rule.
     */
    public function deleteRule(int $ruleId): void
    {
        $rule = AllowListModel::findOrFail($ruleId);
        $this->authorize('delete', $rule);

        $rule->delete();

        $this->dispatch('rule-deleted');
    }

    /**
     * Import CSV file.
     */
    public function importCsv(): void
    {
        $this->authorize('import', AllowListModel::class);

        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if (! $this->csvFile) {
            return;
        }

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');

        if (! $file) {
            $this->addError('csvFile', 'Failed to open CSV file.');

            return;
        }

        $header = fgetcsv($file);
        $rules = [];
        $lineNumber = 1;

        while (($row = fgetcsv($file)) !== false) {
            $lineNumber++;

            if (count($row) < 3) {
                continue;
            }

            $domain = trim($row[0] ?? '');
            $type = trim($row[1] ?? '');
            $patternType = trim($row[2] ?? '');
            $reason = trim($row[3] ?? '');

            if (empty($domain) || ! in_array($type, ['allow', 'block']) || ! in_array($patternType, ['exact', 'wildcard', 'regex'])) {
                continue;
            }

            if ($patternType === 'regex') {
                $isValid = @preg_match($domain, '') !== false;

                if (! $isValid) {
                    continue;
                }
            }

            $rules[] = [
                'domain' => $domain,
                'type' => $type,
                'pattern_type' => $patternType,
                'reason' => $reason,
                'is_active' => true,
                'hit_count' => 0,
                'added_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($file);

        if (empty($rules)) {
            $this->addError('csvFile', 'No valid rules found in CSV file.');

            return;
        }

        DB::transaction(function () use ($rules) {
            AllowListModel::insert($rules);
        });

        $this->reset(['csvFile']);
        $this->dispatch('csv-imported');
    }

    /**
     * Export rules to CSV.
     */
    public function exportCsv()
    {
        $this->authorize('export', AllowListModel::class);

        $rules = AllowListModel::with('admin:id,name')->get();

        $csv = "Domain,Type,Pattern Type,Reason,Hit Count,Active,Added By,Created At\n";

        foreach ($rules as $rule) {
            $csv .= sprintf(
                '"%s","%s","%s","%s",%d,%s,"%s","%s"'."\n",
                $rule->domain,
                $rule->type,
                $rule->pattern_type,
                $rule->reason ?? '',
                $rule->hit_count,
                $rule->is_active ? 'Yes' : 'No',
                $rule->admin?->name ?? '',
                $rule->created_at->format('Y-m-d H:i:s')
            );
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'allow-list-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Test a domain against rules.
     */
    public function testDomainMatch(): void
    {
        $this->validate([
            'testDomain' => 'required|string',
        ]);

        $matchedRule = AllowListModel::where('is_active', true)
            ->get()
            ->first(function ($rule) {
                return $this->matchesDomain($rule, $this->testDomain);
            });

        if ($matchedRule) {
            $this->testResult = [
                'matched' => true,
                'rule' => $matchedRule,
            ];
        } else {
            $this->testResult = [
                'matched' => false,
                'rule' => null,
            ];
        }
    }

    /**
     * Check if a domain matches a rule.
     */
    protected function matchesDomain(AllowListModel $rule, string $domain): bool
    {
        return match ($rule->pattern_type) {
            'exact' => strtolower($rule->domain) === strtolower($domain),
            'wildcard' => fnmatch($rule->domain, $domain, FNM_CASEFOLD),
            'regex' => @preg_match($rule->domain, $domain) === 1,
            default => false,
        };
    }

    /**
     * Clear test results.
     */
    public function clearTest(): void
    {
        $this->reset(['testDomain', 'testResult']);
    }

    public function render()
    {
        return view('livewire.admin.allow-list', [
            'rules' => $this->getAllowListQuery()->paginate(50),
        ])->layout('components.layouts.admin');
    }
}
