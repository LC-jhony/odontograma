---
paths:
  - 'app/Domain/Odontogram/**'
---

# Odontogram

## SVG interactivo: Alpine @click, no wire:click
ToothSvgBuilder emite las zonas del diente como `<polygon>`/`<text>` SVG. Para interacción NO usar `wire:click` sobre SVG (las directivas wire:* sobre elementos SVG no se inicializan de forma fiable en Livewire v4 + Alpine); usar Alpine `@click="$wire.selectZone(...)"`. La paleta y los botones HTML sí pueden seguir usando wire:click.
