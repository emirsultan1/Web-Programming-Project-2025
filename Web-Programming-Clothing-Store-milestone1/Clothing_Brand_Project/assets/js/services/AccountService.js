// assets/js/services/AccountService.js

import http from "./http.js";

export default {
  async getProfile() {
    // ✅ your backend "current user" endpoint is /api/me
    const res = await http.get("/api/me");
    // forms.js expects a user object; normalize it
    return res && res.authenticated ? res.user : null;
  },

  async updateProfile(data) {
    // keep if you actually have this endpoint; otherwise remove later
    return await http.put("/api/v1/account", data);
  },
};
