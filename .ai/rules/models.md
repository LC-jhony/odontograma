---
paths:
  - app/Models/OdontogramTreatmentLog.php
---

# Models

## $table explícito: odontogram_treatment_log
Eloquent pluraliza OdontogramTreatmentLog a `odontogram_treatment_logs`, pero la tabla real es `odontogram_treatment_log`. El modelo requiere `protected $table = 'odontogram_treatment_log';` o las inserciones fallan con "table doesn't exist".
