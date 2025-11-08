<?php

namespace App\Console\Commands;

use App\Models\Note;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DeleteExpiredNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all expired notes that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredNotes = Note::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->get();

        $count = $expiredNotes->count();

        // Clear cache for each note before deletion
        foreach ($expiredNotes as $note) {
            Cache::forget("note:{$note->hash}");
        }

        // Delete all expired notes
        Note::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->delete();

        $this->info("Deleted {$count} expired notes");

        return Command::SUCCESS;
    }
}
