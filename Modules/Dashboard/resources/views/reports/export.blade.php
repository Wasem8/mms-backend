<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: cairo; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #6b7280; margin-bottom: 16px; }
        .summary { background: #f3f4f6; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; }
        .summary div { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: right; }
        th { background: #111827; color: #fff; }
        tr:nth-child(even) { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}
        @if(!empty($filters['date_from']) || !empty($filters['date_to']))
            — الفترة:
            {{ $filters['date_from'] ?? '—' }} إلى {{ $filters['date_to'] ?? '—' }}
        @endif
        @if(!empty($filters['mosque_id']))
            — المسجد #{{ $filters['mosque_id'] }}
        @endif
    </div>

    <div class="summary">
        @foreach($summary as $key => $value)
            <div><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}">لا توجد سجلات مطابقة</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
