<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public ?string $plainTextToken = null;

    public function with(): array
    {
        return [
            'tokens' => Auth::user()->tokens()->latest()->get(),
        ];
    }

    public function createToken(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = Auth::user()->createToken($this->name);

        $this->plainTextToken = $token->plainTextToken;
        $this->name = '';
    }

    public function revokeToken(string $id): void
    {
        Auth::user()->tokens()->where('id', $id)->delete();
    }

    public function dismissToken(): void
    {
        $this->plainTextToken = null;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('API Tokens')" :subheading="__('Create personal access tokens to authenticate API requests')">

        {{-- New token form --}}
        <form wire:submit="createToken" class="my-6 w-full space-y-4">
            <flux:input wire:model="name" :label="__('Token name')" type="text" placeholder="e.g. My integration"
                required autofocus />
            <flux:button variant="primary" type="submit">{{ __('Create token') }}</flux:button>
        </form>

        {{-- Newly created token (shown once) --}}
        @if ($plainTextToken)
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950 p-4 space-y-3">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                    {{ __('Your new token — copy it now, it will not be shown again.') }}
                </p>
                <div class="flex items-center gap-2">
                    <code class="flex-1 break-all rounded bg-white dark:bg-zinc-900 border border-green-200 dark:border-green-800 px-3 py-2 text-xs font-mono text-gray-800 dark:text-zinc-200 select-all">{{ $plainTextToken }}</code>
                    <flux:button size="sm" x-on:click="navigator.clipboard.writeText('{{ $plainTextToken }}')">
                        {{ __('Copy') }}
                    </flux:button>
                </div>
                <div>
                    <flux:button size="sm" variant="ghost" wire:click="dismissToken">{{ __('Dismiss') }}</flux:button>
                </div>
            </div>
        @endif

        {{-- Token list --}}
        @if ($tokens->isEmpty())
            <p class="text-sm text-gray-500 dark:text-zinc-500">{{ __('No tokens yet.') }}</p>
        @else
            <div class="divide-y divide-gray-200 dark:divide-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-800">
                @foreach ($tokens as $token)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div class="space-y-0.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-zinc-100">{{ $token->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-zinc-500">
                                {{ __('Created') }} {{ $token->created_at->diffForHumans() }}
                                @if ($token->last_used_at)
                                    &middot; {{ __('Last used') }} {{ $token->last_used_at->diffForHumans() }}
                                @else
                                    &middot; {{ __('Never used') }}
                                @endif
                            </p>
                        </div>
                        <flux:button size="sm" variant="danger" wire:click="revokeToken('{{ $token->id }}')"
                            wire:confirm="{{ __('Revoke this token? Any integrations using it will stop working.') }}">
                            {{ __('Revoke') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif

    </x-settings.layout>
</section>
