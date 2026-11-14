# Changelog

## 1.0.0 - 2026-11-14

- First release, extracted from production (two-site live-data pipeline).
- `src/browser-direct-feed.php`: slim CORS JSON endpoint, 30-min cache, all-or-nothing payload.
- `src/feed-client.js`: same-origin-first fetch with browser-direct CORS fallback.
- docs/: self-contained demo with optional live-fetch button.
