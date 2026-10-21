export function readWidgetCache(key, maxAgeMs) {
  try {
    const cached = JSON.parse(window.localStorage.getItem(key) || 'null');
    if (!cached?.savedAt || Date.now() - cached.savedAt > maxAgeMs) return null;
    return cached.payload || null;
  } catch {
    return null;
  }
}

export function writeWidgetCache(key, payload) {
  try {
    window.localStorage.setItem(key, JSON.stringify({ savedAt: Date.now(), payload }));
  } catch {
    // Storage may be unavailable in private browsing.
  }
}

export function localDateKey() {
  const date = new Date();
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}
