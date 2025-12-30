// assets/js/services/http.js

const API_BASE =
  (window && window.API_BASE) ||
  document.querySelector('meta[name="api-base"]')?.getAttribute("content") ||
  (location.hostname === "localhost" || location.hostname === "127.0.0.1"
    ? "http://localhost:8000"
    : ""); // leave empty in prod unless set via window.API_BASE or meta tag

async function request(method, path, data = null) {
  if (!API_BASE) {
    throw new Error(
      "API_BASE is not set. Set window.API_BASE or <meta name='api-base' content='...'>."
    );
  }

  const url = API_BASE + path;

  const headers = {
    "X-Requested-With": "XMLHttpRequest",
  };

  // Only send JSON header when sending a body
  const opts = {
    method,
    credentials: "include",
    headers,
  };

  if (data !== null) {
    headers["Content-Type"] = "application/json";
    opts.body = JSON.stringify(data);
  }

  const res = await fetch(url, opts);

  // Try JSON; fallback to text
  let payload = null;
  let text = "";
  try {
    payload = await res.json();
  } catch (_) {
    try {
      text = await res.text();
    } catch (_) {}
  }

  if (!res.ok) {
    const msg =
      (payload && Array.isArray(payload.errors) && payload.errors.join(" ")) ||
      (payload && payload.error && payload.error.message) ||
      (payload && payload.message) ||
      (text && text.slice(0, 200)) ||
      `Request failed (${res.status})`;

    const err = new Error(msg);
    err.status = res.status;
    err.payload = payload;
    throw err;
  }

  return payload;
}

export default {
  get: (path) => request("GET", path),
  post: (path, data) => request("POST", path, data),
  put: (path, data) => request("PUT", path, data),
  patch: (path, data) => request("PATCH", path, data),
  delete: (path) => request("DELETE", path),
};
