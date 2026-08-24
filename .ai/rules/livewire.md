---
paths:
  - app/Livewire/OdontogramBoard.php
---

# Livewire

## Guardado diferido: pending + botón Guardar
El tablero NO persiste nada al marcar dientes: `selectZone`/`submitObservation` acumulan acciones en la propiedad pública `pending` (cada una: fdi_code, face|null para pieza completa, action apply|clear|observe, condition_code, observation). Solo `save()` persiste todo dentro de DB::transaction (aplica dientes/caras + crea la bitácora) y vacía `pending`. El estado visual (SVG, hallazgos, historial) se calcula fusionando BD + pending en `stagedToothStates()`. La vista muestra "Hay cambios sin guardar" si `count($pending) > 0`.
