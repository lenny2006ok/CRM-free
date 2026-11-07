# Bolt's Journal

Critical learnings only: architectural performance bottlenecks, counterintuitive optimization results, rejected changes, and app-specific performance patterns.

## 2026-11-07 - In-Memory Caching for Multi-Tenant Cron Notification Templates
**Learning:** In multi-tenant scheduled jobs (`cron:verificar-vencimentos`) where global scopes are disabled (`withoutGlobalScopes()`), repeatedly querying templates per client creates an O(N) N+1 query pattern. Because notification templates are fixed per reseller and finality (`vencimento_3dias`, `vencimento_hoje`, `vencido`), caching them in an in-memory hash keyed by `[reseller_id][finalidade]` during command execution reduces queries from O(N) to at most O(R x 3) without any cross-job stale state risk.
**Action:** When writing or optimizing batch CLI commands operating across tenant records, look for template or config lookups inside the iteration loop and cache them in a local associative array across the batch.
