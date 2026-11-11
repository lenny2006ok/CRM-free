# Bolt's Performance Journal

## 2026-11-11 - Preserving TenantScope in Aggregations with toBase() & SQLite date comparisons
**Learning:** In multi-tenant Eloquent models protected by `TenantScope`, calling `Model::toBase()->selectRaw(...)` preserves the global scope (`user_id = ?`) while bypassing model hydration overhead. However, when writing raw SQL conditional aggregations on date fields (e.g. `vencimento`) in SQLite, date attributes can be stored with a time component (`YYYY-MM-DD HH:MM:SS`), causing direct string equality `vencimento = ?` to fail; wrapping both sides in `date(...)` (e.g. `date(vencimento) = date(?)`) ensures consistent date comparison across database drivers.
**Action:** When consolidating multiple Eloquent count queries into a single conditional aggregation, always use `Model::toBase()` to retain tenant isolation and normalize date comparisons with `date(...)`.
