/**
 * Autenticação simples (localStorage). Para produção use um backend.
 */
const AUTH_KEY = "nu-chao-auth";
const DEFAULT_USER = "admin";
const DEFAULT_PASS = "admin123";

function getSession() {
  try {
    const raw = localStorage.getItem(AUTH_KEY);
    if (!raw) return null;
    const data = JSON.parse(raw);
    if (data && data.user && data.expiresAt && Date.now() < data.expiresAt) return data;
    localStorage.removeItem(AUTH_KEY);
    return null;
  } catch {
    return null;
  }
}

function setSession(user, expiresInDays = 7) {
  const expiresAt = Date.now() + expiresInDays * 24 * 60 * 60 * 1000;
  localStorage.setItem(AUTH_KEY, JSON.stringify({ user, expiresAt }));
}

function logout() {
  localStorage.removeItem(AUTH_KEY);
}

function login(emailOrUser, password) {
  const u = (emailOrUser || "").trim().toLowerCase();
  const p = (password || "").trim();
  if (u === DEFAULT_USER && p === DEFAULT_PASS) {
    setSession({ name: "Admin", login: u });
    return true;
  }
  return false;
}

function requireAuth(redirectUrl) {
  if (getSession()) return true;
  const url = redirectUrl || "login.html";
  const returnTo = encodeURIComponent(window.location.href);
  window.location.href = url + (url.includes("?") ? "&" : "?") + "return=" + returnTo;
  return false;
}
