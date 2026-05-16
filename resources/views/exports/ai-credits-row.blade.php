<tr class="page-group main-row" data-page="{{ $pageNum }}" onclick="toggleRow({{ $i }})">
    <td class="url" title="{{ $row->url }}">{{ $row->url }}</td>
    <td class="r" style="width:96px">{{ number_format($row->word_count) }}</td>
    <td class="r" style="width:128px">{{ number_format($row->credits_one_language, 2) }}</td>
    <td class="r" style="width:128px">{{ number_format($row->credits_five_languages, 2) }}</td>
    <td style="width:40px;text-align:center">
        <span class="chevron" id="btn-{{ $i }}">&#8964;</span>
    </td>
</tr>
<tr class="page-group content-row" id="content-{{ $i }}" data-page="{{ $pageNum }}">
    <td colspan="5">
        @if (count($row->translatable_content ?? []) > 0)
            <table class="sub-table">
                <tbody>
                    @foreach ($row->translatable_content as $seg)
                        @php
                            $segWc = $seg['word_count'] ?? null;
                            $segC1 = $seg['credits_one'] ?? ($segWc !== null ? round(0.0711 + $segWc * 0.002098, 4) : null);
                            $segC5 = $seg['credits_five'] ?? ($segWc !== null ? round(0.1265 + $segWc * 0.003725, 4) : null);
                            $badgeClass = match($seg['type']) {
                                'title'       => 'badge-title',
                                'description' => 'badge-description',
                                'heading'     => 'badge-heading',
                                'paragraph'   => 'badge-paragraph',
                                'list-item'   => 'badge-list-item',
                                'cell'        => 'badge-cell',
                                'alt-text'    => 'badge-alt-text',
                                default       => 'badge-default',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $seg['type'] }}</span>
                                {{ $seg['text'] }}
                            </td>
                            <td class="r" style="width:96px">{{ $segWc !== null ? number_format($segWc) : '' }}</td>
                            <td class="r" style="width:128px">{{ $segC1 !== null ? number_format($segC1, 2) : '' }}</td>
                            <td class="r" style="width:128px">{{ $segC5 !== null ? number_format($segC5, 2) : '' }}</td>
                            <td style="width:40px"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding:10px 14px;font-size:13px;color:#9ca3af;">No translatable content extracted.</div>
        @endif
    </td>
</tr>
