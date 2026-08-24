---
paths:
  - 'database/migrations/**'
---

# Migrations

## DDL MySQL fuera de DB::transaction
ALTER TABLE (add/drop unique, foreign key) hace commit implícito en MySQL. Si se envuelve un ALTER dentro de DB::transaction, la migración queda a medias (índice creado pero migración sin registrar en `migrations`). Ejecutar la fusión de datos (OdontogramConsolidator) en su propia transacción y el ALTER después, sin transacción externa.
