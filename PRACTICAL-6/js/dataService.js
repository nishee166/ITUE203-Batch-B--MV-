/* ==========================================================
   dataService.js
   Responsible ONLY for fetching + caching JSON data.
   (Key Question 1: How is JSON fetched, parsed and rendered?
    Key Question 4: modularity -> this file never touches the DOM)
   ========================================================== */

const CACHE_PREFIX = "studentHub_cache_";

/**
 * Fetch a JSON file with the Fetch API, parse it, and cache the
 * result in localStorage so the page can still show the last known
 * data if the network request fails later (Advanced Extension).
 * @param {string} url - path to the JSON file
 * @param {string} cacheKey - key used to store data in localStorage
 * @returns {Promise<Array>} parsed JSON array
 */
export async function fetchJSON(url, cacheKey) {
    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Advanced Extension: cache latest successful response
        try {
            localStorage.setItem(
                CACHE_PREFIX + cacheKey,
                JSON.stringify({ data, savedAt: new Date().toISOString() })
            );
        } catch (storageErr) {
            console.warn("Could not cache data:", storageErr);
        }

        return data;

    } catch (error) {
        console.error(`Fetch failed for ${url}:`, error);

        // Fall back to cached copy (offline-like display)
        const cached = getCachedData(cacheKey);
        if (cached) {
            return cached;
        }

        // No network AND no cache -> rethrow so UI can show an error
        throw error;
    }
}

/** Read cached data (if any) for a given key. Returns null if none exists. */
export function getCachedData(cacheKey) {
    const raw = localStorage.getItem(CACHE_PREFIX + cacheKey);
    if (!raw) return null;

    try {
        const parsed = JSON.parse(raw);
        return parsed.data;
    } catch {
        return null;
    }
}

/** Returns the timestamp the cached copy was saved, or null. */
export function getCacheTimestamp(cacheKey) {
    const raw = localStorage.getItem(CACHE_PREFIX + cacheKey);
    if (!raw) return null;

    try {
        return JSON.parse(raw).savedAt;
    } catch {
        return null;
    }
}
