// assets/js/services/OrderService.js

import http from "./http.js";

export default {
  // -----------------------------
  // Get logged-in user's orders
  // -----------------------------
  async getMyOrders() {
    const res = await http.get("/api/v1/my-orders");

    // normalize response for forms.js
    // accepted shapes:
    // 1) { success:true, data:[...] }
    // 2) { success:true, items:[...] }
    // 3) { items:[...] }
    const items =
      (res && Array.isArray(res.data) && res.data) ||
      (res && Array.isArray(res.items) && res.items) ||
      [];

    return { items };
  },

  // -----------------------------
  // Place new order (checkout)
  // -----------------------------
  async placeOrder(items) {
    // items = [{ product_id, quantity, size, color }]
    const res = await http.post("/api/v1/orders", { items });

    // normalize:
    // accepted shapes:
    // 1) { success:true, data:{order_id:...} }
    // 2) { success:true, order_id:... }
    // 3) { order_id:... }
    if (res && res.success === true) return res;
    if (res && typeof res.order_id !== "undefined") return { success: true, ...res };
    if (res && res.data && typeof res.data.order_id !== "undefined") return { success: true, ...res.data };

    return { success: false, raw: res };
  },
};
