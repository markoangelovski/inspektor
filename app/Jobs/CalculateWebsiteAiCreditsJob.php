<?php

namespace App\Jobs;

use App\Actions\AiCredits\CalculateWebsiteAiCreditsAction;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateWebsiteAiCreditsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly Website $website) {}

    public function handle(CalculateWebsiteAiCreditsAction $action): void
    {
        $action->execute($this->website);
    }

    public function failed(\Throwable $e): void
    {
        $this->website->update(['ai_credits_calculating' => false]);
    }
}
