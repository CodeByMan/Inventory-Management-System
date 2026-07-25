<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CreateUser extends Command
{
    protected $signature = 'stockmanager:create-user';

    protected $description = 'Create a StockManager administrator or staff account';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Name'),
            'email' => $this->ask('Email'),
            'password' => $this->secret('Password (minimum 12 characters)'),
            'role' => $this->choice('Role', ['admin', 'staff'], 1),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(12)],
            'role' => ['required', Rule::in(['admin', 'staff'])],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return SymfonyCommand::FAILURE;
        }

        $validated = $validator->validated();

        $user = DB::transaction(function () use ($validated): User {
            $role = Role::findOrCreate($validated['role'], 'web');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole($role);

            return $user;
        });

        $this->info(sprintf(
            'Created %s account for %s.',
            $validated['role'],
            $user->email
        ));

        return SymfonyCommand::SUCCESS;
    }
}
