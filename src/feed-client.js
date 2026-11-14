/* browser-direct-feed client half: try your same-origin feed first, fall back to a
   CORS-open remote endpoint when the local proxy comes up empty (blocked fetches, stale cache). */
function fetchFeed(primaryUrl, fallbackUrl) {
  return fetch(primaryUrl, { cache: 'no-store' })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (j) {
      if (j && j.items && j.items.length > 1) return j; // more than the label line
      if (!fallbackUrl) return j;
      return fetch(fallbackUrl, { cache: 'no-cache' })
        .then(function (r) { return r.ok ? r.json() : null; });
    });
}
if (typeof window !== 'undefined') window.fetchFeed = fetchFeed;
