<div>
    <x-inspektor.website-detail.breadcrumbs :website="$website" current="AI Credits" />

    <x-inspektor.website-detail.page-header :name="$website->name" :url="$website->url" />

    <x-inspektor.website-detail.page-nav-tabs :website="$website" />

    <div class="space-y-6 mt-6">

        {{-- Dashboard summary --}}
        @php
            $billable1 = max(0, $totals['total_credits_one'] - 1000);
            $overage1  = round($billable1 / 100 * 1.5, 2);
            $billable5 = max(0, $totals['total_credits_five'] - 1000);
            $overage5  = round($billable5 / 100 * 1.5, 2);
        @endphp

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 p-4">
                <div class="text-xs text-gray-500 dark:text-zinc-400 mb-1">Pages</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-zinc-100">{{ number_format($totals['page_count']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 p-4">
                <div class="text-xs text-gray-500 dark:text-zinc-400 mb-1">Total words</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-zinc-100">{{ number_format($totals['total_words']) }}</div>
            </div>

            {{-- Credits × 1 language --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 p-4">
                <div class="text-xs text-gray-500 dark:text-zinc-400 mb-1">Credits &times; 1 language</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-zinc-100">{{ number_format($totals['total_credits_one'], 1) }}</div>
                @if ($totals['total_credits_one'] > 1000)
                    <div class="text-xs mt-1 text-amber-500 dark:text-amber-400 font-medium">+${{ number_format($overage1, 2) }} overage</div>
                    <div class="text-xs mt-1.5 text-gray-400 dark:text-zinc-500 space-y-0.5 leading-relaxed border-t border-gray-100 dark:border-zinc-800 pt-1.5">
                        <div>{{ number_format($totals['total_credits_one'], 1) }} total credits</div>
                        <div>− 1,000 included in plan</div>
                        <div>= {{ number_format($billable1, 1) }} billable credits</div>
                        <div class="font-medium text-gray-500 dark:text-zinc-400">{{ number_format($billable1, 1) }} ÷ 100 × $1.50 = ${{ number_format($overage1, 2) }}</div>
                    </div>
                @else
                    <div class="text-xs mt-1 text-gray-400 dark:text-zinc-500">within 1,000 credit plan</div>
                @endif
            </div>

            {{-- Credits × 5 languages --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 p-4">
                <div class="text-xs text-gray-500 dark:text-zinc-400 mb-1">Credits &times; 5 languages</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-zinc-100">{{ number_format($totals['total_credits_five'], 1) }}</div>
                @if ($totals['total_credits_five'] > 1000)
                    <div class="text-xs mt-1 text-amber-500 dark:text-amber-400 font-medium">+${{ number_format($overage5, 2) }} overage</div>
                    <div class="text-xs mt-1.5 text-gray-400 dark:text-zinc-500 space-y-0.5 leading-relaxed border-t border-gray-100 dark:border-zinc-800 pt-1.5">
                        <div>{{ number_format($totals['total_credits_five'], 1) }} total credits</div>
                        <div>− 1,000 included in plan</div>
                        <div>= {{ number_format($billable5, 1) }} billable credits</div>
                        <div class="font-medium text-gray-500 dark:text-zinc-400">{{ number_format($billable5, 1) }} ÷ 100 × $1.50 = ${{ number_format($overage5, 2) }}</div>
                    </div>
                @else
                    <div class="text-xs mt-1 text-gray-400 dark:text-zinc-500">within 1,000 credit plan</div>
                @endif
            </div>
        </div>

        {{-- Footnote with info button + Strapi reference modal --}}
        <div x-data="{ showInfo: false }" class="-mt-2">
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-gray-400 dark:text-zinc-500">
                    Estimates use: 1 language → 0.0711 + 0.002098&times;words; 5 languages → 0.1265 + 0.003725&times;words.
                    Strapi Growth plan includes 1,000 credits/month ($45/mo); overages billed at $1.50 per 100 credits.
                </span>
                <button @click="showInfo = true" type="button"
                    class="shrink-0 text-gray-400 dark:text-zinc-500 hover:text-[#4945FF] dark:hover:text-indigo-400 transition-colors cursor-pointer"
                    title="View Strapi AI Credits reference">
                    <flux:icon.information-circle class="size-4" />
                </button>
            </div>

            {{-- Strapi.md modal --}}
            <div x-show="showInfo" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @keydown.escape.window="showInfo = false">
                <div @click="showInfo = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                <div class="relative bg-white dark:bg-zinc-950 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col border border-gray-200 dark:border-zinc-800 overflow-hidden">

                    {{-- Modal header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800 bg-gradient-to-r from-[#4945FF]/5 to-transparent shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="size-7 rounded-lg bg-[#4945FF]/10 flex items-center justify-center shrink-0">
                                <svg class="size-4 text-[#4945FF]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-.5 5a.5.5 0 0 1 1 0v5.5H17a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5V7z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-zinc-100">Strapi AI Credits Reference</h3>
                                <p class="text-xs text-gray-400 dark:text-zinc-500">Growth plan · $45/mo · 1,000 credits included</p>
                            </div>
                        </div>
                        <button @click="showInfo = false" type="button"
                            class="text-gray-400 dark:text-zinc-500 hover:text-gray-700 dark:hover:text-zinc-200 transition-colors cursor-pointer rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-zinc-800">
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    {{-- Modal body --}}
                    <div class="overflow-y-auto px-6 py-5 strapi-ref-content">
                        {!! $strapiMdHtml !!}
                    </div>

                    {{-- Modal footer --}}
                    <div class="px-6 py-3 border-t border-gray-100 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-900 shrink-0">
                        <p class="text-xs text-gray-400 dark:text-zinc-500">
                            Source: <a href="https://strapi.io/pricing-cms" target="_blank" class="text-[#4945FF] dark:text-indigo-400 hover:underline">strapi.io/pricing-cms</a>
                            &nbsp;·&nbsp;
                            <a href="https://support.strapi.io/articles/2817318284-ai-translation-credit-usage" target="_blank" class="text-[#4945FF] dark:text-indigo-400 hover:underline">AI Translation Credit Usage</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Per-page table --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="p-4 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between gap-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-zinc-100">Per-page estimates</h2>

                <div class="flex items-center gap-2">
                    <flux:button wire:click="calculate" wire:loading.attr="disabled" wire:target="calculate"
                        variant="ghost" size="sm" icon="arrow-path" class="cursor-pointer">
                        <span wire:loading.remove wire:target="calculate">Recalculate</span>
                        <span wire:loading wire:target="calculate">Calculating&hellip;</span>
                    </flux:button>

                    <flux:dropdown>
                        <flux:button icon:trailing="ellipsis-vertical" variant="ghost" size="sm" class="cursor-pointer"></flux:button>
                        <flux:menu>
                            <flux:menu.item icon="arrow-down-tray" :href="route('ai-credits.export', $website)">
                                Download as HTML
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>

            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-800 text-left text-xs text-gray-500 dark:text-zinc-400">
                            <th class="px-4 py-3 font-medium">URL</th>
                            <th class="px-4 py-3 font-medium text-right w-24">Words</th>
                            <th class="px-4 py-3 font-medium text-right w-32">Credits &times; 1</th>
                            <th class="px-4 py-3 font-medium text-right w-32">Credits &times; 5</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>

                    @forelse ($credits as $row)
                        {{-- One <tbody> per row group — Alpine scope covers both rows --}}
                        <tbody wire:key="group-{{ $row->id }}" x-data="{ open: false }">
                            <tr @click="open = !open"
                                class="border-b border-gray-100 dark:border-zinc-800/50 cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="px-4 py-3 text-gray-700 dark:text-zinc-300 max-w-xs">
                                    <span class="truncate block" title="{{ $row->url }}">{{ $row->url }}</span>
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-zinc-300 w-24">
                                    {{ number_format($row->word_count) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-zinc-300 w-32">
                                    {{ number_format($row->credits_one_language, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-zinc-300 w-32">
                                    {{ number_format($row->credits_five_languages, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center w-10">
                                    <div class="inline-flex items-center justify-center text-gray-400 dark:text-zinc-500">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="size-4 transition-transform duration-150"
                                            :class="{ 'rotate-180': open }"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                                        </svg>
                                    </div>
                                </td>
                            </tr>

                            {{-- Expanded content: sub-table aligned to parent columns --}}
                            <tr x-show="open" x-cloak
                                class="border-b border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/30">
                                <td colspan="5" class="p-0">
                                    @if (count($row->translatable_content ?? []) > 0)
                                        <table class="w-full text-sm">
                                            <tbody>
                                                @foreach ($row->translatable_content as $segment)
                                                    @php
                                                        $segWc = $segment['word_count'] ?? null;
                                                        $segC1 = $segment['credits_one'] ?? ($segWc !== null ? round($segWc * 0.002098, 4) : null);
                                                        $segC5 = $segment['credits_five'] ?? ($segWc !== null ? round($segWc * 0.003725, 4) : null);
                                                    @endphp
                                                    <tr class="border-b border-gray-100 dark:border-zinc-800/40 last:border-0">
                                                        <td class="px-4 py-2 text-gray-700 dark:text-zinc-300">
                                                            <div class="flex items-start gap-2">
                                                                <span class="shrink-0 text-xs font-medium uppercase tracking-wide px-2 py-0.5 rounded mt-0.5
                                                                    @switch($segment['type'])
                                                                        @case('title') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 @break
                                                                        @case('description') bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 @break
                                                                        @case('heading') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 @break
                                                                        @case('paragraph') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 @break
                                                                        @case('list-item') bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 @break
                                                                        @case('cell') bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 @break
                                                                        @case('alt-text') bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300 @break
                                                                        @default bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-zinc-300
                                                                    @endswitch
                                                                ">{{ $segment['type'] }}</span>
                                                                <span class="leading-relaxed">{{ $segment['text'] }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-2 text-right tabular-nums text-gray-500 dark:text-zinc-400 w-24 align-top">
                                                            {{ $segWc !== null ? number_format($segWc) : '' }}
                                                        </td>
                                                        <td class="px-4 py-2 text-right tabular-nums text-gray-500 dark:text-zinc-400 w-32 align-top">
                                                            {{ $segC1 !== null ? number_format($segC1, 2) : '' }}
                                                        </td>
                                                        <td class="px-4 py-2 text-right tabular-nums text-gray-500 dark:text-zinc-400 w-32 align-top">
                                                            {{ $segC5 !== null ? number_format($segC5, 2) : '' }}
                                                        </td>
                                                        <td class="w-10"></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="px-4 py-3 text-sm text-gray-400 dark:text-zinc-500">No translatable content extracted for this page.</div>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-zinc-500">
                                    <div wire:loading wire:target="calculate">Calculating credits&hellip;</div>
                                    <div wire:loading.remove wire:target="calculate">No data available. Click Recalculate to generate estimates.</div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse

                </table>
            </div>

            {{-- Pagination --}}
            @if ($credits->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-zinc-800">
                    {{ $credits->links() }}
                </div>
            @endif

        </div>

    </div>
</div>
