<div class="space-y-2 py-2">
    @if($rows->isEmpty())
        <p class="text-sm text-gray-500">No stock records found for this product.</p>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-1 font-medium text-gray-600">Warehouse</th>
                    <th class="text-right py-1 font-medium text-gray-600">On Hand</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="border-b last:border-0">
                        <td class="py-1 text-gray-800">{{ $row['warehouse'] }}</td>
                        <td class="py-1 text-right font-mono
                            {{ $row['stock'] <= $product->reorder_point ? 'text-danger-600 font-bold' : 'text-gray-800' }}">
                            {{ number_format($row['stock'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="text-xs text-gray-400 pt-2">
            Reorder point: {{ $product->reorder_point }}
            {{ $product->unitOfMeasure->abbreviation ?? '' }}
        </p>
    @endif
</div>