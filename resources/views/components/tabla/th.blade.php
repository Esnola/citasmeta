@if ($condicion)
  <th class="px-4 py-3 text-xs">
    <div class="flex items-center justify-center gap-2">
     {{ $slot }}
    </div>
  </th>
@endif
