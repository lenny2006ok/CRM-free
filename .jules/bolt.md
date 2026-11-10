# Bolt's Performance Journal

## 2026-11-10 - Consolidate Multi-Status Count Queries with Conditional SQL Aggregation
**Learning:** In dashboards displaying counts across different statuses/ranges of the same table (such as total, active, expired, and due-today clients), executing separate `count()` queries causes multiple round-trips and table scans. Replacing them with a single `selectRaw` containing conditional sums (`SUM(CASE WHEN date(vencimento) >= ? THEN 1 ELSE 0 END)`) reduces database roundtrips by 75% while respecting `TenantScope`. Additionally, wrapping date columns with `date(col)` prevents mismatch issues between `DATE` and `DATETIME` string representations across database engines (e.g., SQLite storing `Y-m-d H:i:s` vs date comparisons against `Y-m-d`).
**Action:** When querying multiple status counts on a scoped model, use a single query with conditional aggregation (`toBase()->selectRaw(...)`) and ensure proper date normalization in SQL expressions.
