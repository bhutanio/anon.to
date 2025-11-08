<?php

namespace App\Livewire\Dashboard;

use App\Models\Note;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $activeTab = 'links';

    public ?int $confirmingDeletion = null;

    public ?string $copiedHash = null;

    /**
     * Switch between tabs.
     */
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->confirmingDeletion = null;
        $this->copiedHash = null;
    }

    /**
     * Confirm deletion of a note.
     */
    public function confirmNoteDeletion(int $id): void
    {
        $this->confirmingDeletion = $id;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDeletion(): void
    {
        $this->confirmingDeletion = null;
    }

    /**
     * Delete a note.
     */
    public function deleteNote(int $id): void
    {
        $note = Note::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($note) {
            // Authorize deletion
            $this->authorize('delete', $note);

            // Delete the note (cache clearing handled by observer)
            $note->delete();
        }

        $this->confirmingDeletion = null;
    }

    /**
     * Copy note URL to clipboard.
     */
    public function copyNoteUrl(string $hash): void
    {
        $this->copiedHash = $hash;

        // Reset after 2 seconds
        $this->dispatch('reset-copied');
    }

    /**
     * Get user's links.
     */
    #[Computed]
    public function links()
    {
        return auth()->user()->links()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get user's notes.
     */
    #[Computed]
    public function notes()
    {
        return auth()->user()->notes()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
