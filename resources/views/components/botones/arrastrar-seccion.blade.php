@props([
    'seccion',
    'titulo' => 'Arrastrar',
])

<button
    {{ $attributes->merge([
        'type' => 'button',
        'draggable' => 'true',
        'class' => 'settings-drag-handle',
        'title' => $titulo
    ]) }}
    x-on:dragstart="startDrag(@js($seccion), $event)"
    x-on:dragend="stopDrag"
>
  <x-iconos.arrastrar />
</button>
