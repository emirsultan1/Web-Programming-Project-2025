// assets/js/services/AuthService.js

import http from "./http.js";

export default {
  async login(email, password) {
    // ✅ your backend routes are /api/login, not /login
    return await http.post("/api/login", { email, password });
  },

  async register(name, email, password, passwordConfirm = password) {
    // ✅ your backend expects password_confirm (Milestone 4 code)
    return await http.post("/api/register", {
      name,
      email,
      password,
      password_confirm: passwordConfirm,
    });
  },

  async logout() {
    try {
      // ✅ your backend route is /api/logout
      await http.post("/api/logout");
    } catch (_) {}
  },
};
