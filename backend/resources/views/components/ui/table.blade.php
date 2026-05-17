@props(['empty' => null])
<div class="ui-table-wrap" x-data x-init="
    const table = $el.querySelector('table');
    if (!table) return;
    const heads = Array.from(table.querySelectorAll('thead th')).map((th) => (th.textContent || '').trim());
    table.querySelectorAll('tbody tr').forEach((tr) => {
        tr.querySelectorAll('td').forEach((td, i) => td.setAttribute('data-label', heads[i] || 'Alan'));
    });
">
    <table {{ $attributes->merge(['class' => 'ui-table']) }}>
        @if(isset($head))<thead>{{ $head }}</thead>@endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
@if(trim((string) $slot) === '' && $empty)
    {{ $empty }}
@endif
@if(isset($footer))
    <div class="mt-4">{{ $footer }}</div>
@endif

