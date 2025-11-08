<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $viewingUserId = null;

    public bool $showBanConfirm = false;

    public ?int $banningUserId = null;

    public bool $showVerifyConfirm = false;

    public ?int $verifyingUserId = null;

    public bool $showPromoteConfirm = false;

    public ?int $promotingUserId = null;

    /**
     * Update search and reset pagination.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get the users query with filters and search.
     */
    protected function getUsersQuery(): Builder
    {
        return User::query()
            ->withCount(['links', 'notes'])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * View user profile.
     */
    public function viewUser(int $userId): void
    {
        $this->viewingUserId = $userId;
    }

    /**
     * Close view modal.
     */
    public function closeView(): void
    {
        $this->viewingUserId = null;
    }

    /**
     * Show ban confirmation modal.
     */
    public function confirmBan(int $userId): void
    {
        $this->banningUserId = $userId;
        $this->showBanConfirm = true;
    }

    /**
     * Ban a user.
     */
    public function banUser(): void
    {
        if ($this->banningUserId === null) {
            return;
        }

        $user = User::findOrFail($this->banningUserId);
        $this->authorize('ban', $user);

        DB::transaction(function () use ($user) {
            $user->update([
                'banned_at' => now(),
                'banned_by' => auth()->id(),
            ]);

            $user->links()->update(['is_active' => false]);
            $user->notes()->update(['is_active' => false]);
        });

        $this->showBanConfirm = false;
        $this->banningUserId = null;

        $this->dispatch('user-banned');
    }

    /**
     * Unban a user.
     */
    public function unbanUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('ban', $user);

        $user->update([
            'banned_at' => null,
            'banned_by' => null,
        ]);

        $this->dispatch('user-unbanned');
    }

    /**
     * Cancel ban operation.
     */
    public function cancelBan(): void
    {
        $this->showBanConfirm = false;
        $this->banningUserId = null;
    }

    /**
     * Show verify confirmation modal.
     */
    public function confirmVerify(int $userId): void
    {
        $this->verifyingUserId = $userId;
        $this->showVerifyConfirm = true;
    }

    /**
     * Verify a user.
     */
    public function verifyUser(): void
    {
        if ($this->verifyingUserId === null) {
            return;
        }

        $user = User::findOrFail($this->verifyingUserId);
        $this->authorize('verify', $user);

        $user->update([
            'is_verified' => true,
            'api_rate_limit' => 500,
        ]);

        $this->showVerifyConfirm = false;
        $this->verifyingUserId = null;

        $this->dispatch('user-verified');
    }

    /**
     * Unverify a user.
     */
    public function unverifyUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('verify', $user);

        $user->update([
            'is_verified' => false,
            'api_rate_limit' => 100,
        ]);

        $this->dispatch('user-unverified');
    }

    /**
     * Cancel verify operation.
     */
    public function cancelVerify(): void
    {
        $this->showVerifyConfirm = false;
        $this->verifyingUserId = null;
    }

    /**
     * Show promote confirmation modal.
     */
    public function confirmPromote(int $userId): void
    {
        $this->promotingUserId = $userId;
        $this->showPromoteConfirm = true;
    }

    /**
     * Promote user to admin.
     */
    public function promoteUser(): void
    {
        if ($this->promotingUserId === null) {
            return;
        }

        $user = User::findOrFail($this->promotingUserId);
        $this->authorize('promote', $user);

        $user->update([
            'is_admin' => true,
        ]);

        $this->showPromoteConfirm = false;
        $this->promotingUserId = null;

        $this->dispatch('user-promoted');
    }

    /**
     * Cancel promote operation.
     */
    public function cancelPromote(): void
    {
        $this->showPromoteConfirm = false;
        $this->promotingUserId = null;
    }

    /**
     * Get the viewing user details.
     */
    public function getViewingUserProperty(): ?User
    {
        if ($this->viewingUserId === null) {
            return null;
        }

        return User::withCount(['links', 'notes', 'reports'])
            ->with(['links' => fn ($query) => $query->latest()->take(5), 'notes' => fn ($query) => $query->latest()->take(5)])
            ->find($this->viewingUserId);
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'users' => $this->getUsersQuery()->paginate(25),
        ])->layout('components.layouts.admin');
    }
}
