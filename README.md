# browser-direct-feed

**Your server's IP is blocked. Your visitors' IPs are not. Ship the data to the browser.**

One PHP file: read a local data source, emit a slim JSON feed with CORS headers. One tiny JS client: try your same-origin proxy first, fall back to the cross-origin endpoint when the local side comes up empty.

## The scar this came from

Two sites, one owner. Site B needed Site A's live data, so Site B's PHP fetched Site A's JSON on a schedule and cached it. For months, fine.

Then Cloudflare flagged the shared-host IP. Every server-side fetch started returning a 403 challenge page: `file_get_contents`, php-curl, even shelling out to `curl`. All of it. From that box, Site A might as well not exist.

The fix was to stop pretending the server would ever win that fight. Site A already serves the JSON - give it CORS headers and a slim shape, and let the **visitor's browser** do the fetching. Residential IPs sail through. The old server-side proxy stays up as a first attempt (it still serves its cache), but the contract changed: empty payload at the edge means fetch direct.

## What's in the box

- `src/browser-direct-feed.php` - production endpoint, verbatim. Reads a local JSON source, ranks, emits `items` + `djs` with `Access-Control-Allow-Origin: *` and a 30-minute cache hint.
- `src/feed-client.js` - `fetchFeed(primaryUrl, fallbackUrl)`. Zero deps, ~15 lines, works everywhere fetch does.

## The shape contract (learned the hard way)

The consumer indexes `djs` by position in `items`, and `items[0]` is the label line. So `djs` is a **1-based object**: `djs["1"]` is the artist for `items[1]`. The first version of this endpoint "cleaned" that up into a 0-based array and every hover in the marquee shifted by one artist. PHP's `json_encode` turns `[1..10]`-keyed arrays into objects and 0-based arrays into lists - replicate the original's key shape **byte-for-byte**, not just the field names.

## All-or-nothing payloads

If the data source is half-readable, this emits nothing rather than a half-truth feed: empty `items`, and the client falls through to the next source. A feed that sometimes lies is worse than a feed that's honestly absent.

## Usage

Drop `browser-direct-feed.php` next to your data file, point the client at it:

```js
fetchFeed('/my-slim-proxy.php', 'https://other-site.com/browser-direct-feed.php')
  .then(function (j) { if (j) render(j); });
```

## Requirements

PHP 7.4+. No dependencies. The endpoint needs `djs.json` (or edit one line to read any local source).

## License

MIT
