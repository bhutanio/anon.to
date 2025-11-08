<?php

namespace App\Livewire\Admin;

use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Notes extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public ?int $viewingNoteId = null;

    public bool $showDeleteConfirm = false;

    public ?int $deletingNoteId = null;

    /**
     * Update search and reset pagination.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update status filter and reset pagination.
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get the notes query with filters and search.
     */
    protected function getNotesQuery(): Builder
    {
        return Note::query()
            ->with(['user:id,name,email'])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('hash', 'like', "%{$this->search}%")
                        ->orWhere('title', 'like', "%{$this->search}%")
                        ->orWhere('content', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function (Builder $userQuery) {
                            $userQuery->where('name', 'like', "%{$this->search}%")
                                ->orWhere('email', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function (Builder $query) {
                match ($this->statusFilter) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'reported' => $query->where('is_reported', true),
                    'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                    default => null,
                };
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * View note details.
     */
    public function viewNote(int $noteId): void
    {
        $this->viewingNoteId = $noteId;
    }

    /**
     * Close view modal.
     */
    public function closeView(): void
    {
        $this->viewingNoteId = null;
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(int $noteId): void
    {
        $this->deletingNoteId = $noteId;
        $this->showDeleteConfirm = true;
    }

    /**
     * Delete a note.
     */
    public function deleteNote(): void
    {
        if ($this->deletingNoteId === null) {
            return;
        }

        $note = Note::findOrFail($this->deletingNoteId);
        $this->authorize('adminDelete', $note);

        $note->delete();

        $this->showDeleteConfirm = false;
        $this->deletingNoteId = null;

        $this->dispatch('note-deleted');
    }

    /**
     * Cancel delete operation.
     */
    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deletingNoteId = null;
    }

    /**
     * Get the viewing note details.
     */
    public function getViewingNoteProperty(): ?Note
    {
        if ($this->viewingNoteId === null) {
            return null;
        }

        return Note::with(['user:id,name,email', 'reports'])->find($this->viewingNoteId);
    }

    /**
     * Helper to truncate content for preview.
     */
    public function getContentPreview(string $content, int $length = 50): string
    {
        return Str::limit($content, $length);
    }

    public function render()
    {
        return view('livewire.admin.notes', [
            'notes' => $this->getNotesQuery()->paginate(25),
        ])->layout('components.layouts.admin');
    }
}
