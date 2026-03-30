<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ApproveUserCommand extends Command
{
    protected $signature = 'user:approve {email? : The email of the user to approve} {--all : Approve all pending users} {--list : List all pending users}';

    protected $description = 'Approve a registered user by email, or list/approve all pending users';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listPendingUsers();
        }

        if ($this->option('all')) {
            return $this->approveAllUsers();
        }

        $email = $this->argument('email');

        if (! $email) {
            $this->error('Please provide a user email, or use --all or --list.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        if ($user->isApproved()) {
            $this->info("User {$email} is already approved.");

            return self::SUCCESS;
        }

        $user->approve();

        $this->info("User {$email} has been approved.");

        return self::SUCCESS;
    }

    protected function listPendingUsers(): int
    {
        $users = User::whereNull('approved_at')->get();

        if ($users->isEmpty()) {
            $this->info('No pending users.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Registered'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->created_at->format('Y-m-d H:i'),
            ])
        );

        return self::SUCCESS;
    }

    protected function approveAllUsers(): int
    {
        $count = User::whereNull('approved_at')->update(['approved_at' => now()]);

        $this->info("Approved {$count} user(s).");

        return self::SUCCESS;
    }
}
