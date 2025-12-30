// assets/js/forms.js

import AuthService from "./services/AuthService.js";
import AccountService from "./services/AccountService.js";
import OrderService from "./services/OrderService.js";

(function () {
  "use strict";

  if (window.__FORMS_JS_INITED__) return;
  window.__FORMS_JS_INITED__ = true;

  function setCurrentUser(user) {
    if (user) localStorage.setItem("currentUser", JSON.stringify(user));
    else localStorage.removeItem("currentUser");
  }

  function getCurrentUser() {
    try {
      const raw = localStorage.getItem("currentUser");
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  }

  function applyRoleUI() {
    const user = getCurrentUser();
    if (user && user.role === "admin") $(".admin-only").show();
    else $(".admin-only").hide();
  }

  function updateAuthUI() {
    const user = getCurrentUser();
    const $areas = $(".auth-area");

    $areas.each(function () {
      const $area = $(this);

      if (user) {
        $area.html(`
          <ul>
            <li>Hello, <span class="user-name"></span></li>
            <li>/</li>
            <li><a href="#myaccount">My Account</a></li>
            <li>/</li>
            <li><a href="#" class="logout-link">Logout</a></li>
          </ul>
        `);
        $area.find(".user-name").text(user.name || user.email || "User");
      } else {
        $area.html(`
          <ul>
            <li><a href="#login1">Login</a></li>
            <li>/</li>
            <li><a href="#login1">Register</a></li>
          </ul>
        `);
      }
    });

    applyRoleUI();
  }

  function renderOrders(orders) {
    const $tbody = $("#orders-table-body");
    if (!$tbody.length) return;

    if (!orders || !orders.length) {
      $tbody.html(`<tr><td colspan="5">No orders found.</td></tr>`);
      return;
    }

    let html = "";
    orders.forEach((o) => {
      const id = o.order_id || o.id || "";
      html += `
        <tr>
          <td>${id}</td>
          <td>${o.created_at || ""}</td>
          <td>${o.status || "pending"}</td>
          <td>${o.total_amount || o.total || ""}</td>
          <td><a href="#order-${id}">View</a></td>
        </tr>
      `;
    });

    $tbody.html(html);
  }

  async function loadMyOrders() {
    const user = getCurrentUser();
    if (!user) return;

    try {
      const data = await OrderService.getMyOrders();
      renderOrders(data.items || []);
    } catch {
      $("#orders-table-body").html(
        `<tr><td colspan="5">Failed to load orders.</td></tr>`
      );
    }
  }

  async function updateAccountPage(retries = 8) {
    const $name = $("#acct-name");
    const $email = $("#acct-email");

    if (!$name.length && retries > 0) {
      setTimeout(() => updateAccountPage(retries - 1), 200);
      return;
    }

    const user = getCurrentUser();

    if (!user) {
      $name.text("Guest");
      $email.text("Not logged in");
      return;
    }

    $name.text(user.name || "User");
    $email.text(user.email || "");

    applyRoleUI();
    loadMyOrders();
  }

  async function syncUser() {
    try {
      const user = await AccountService.getProfile();
      setCurrentUser(user);
    } catch {
      setCurrentUser(null);
    }

    updateAuthUI();
    updateAccountPage();
  }

  $(function () {
    // REGISTER
    $(document).on("submit", "#register-form", async function (e) {
      e.preventDefault();

      const $msg = $("#register-message");
      $msg.text("").removeClass("error success");

      const name = $("#reg-name").val().trim();
      const email = $("#reg-email").val().trim();
      const password = $("#reg-password").val();
      const passwordConfirm = $("#reg-password-confirm").val();

      try {
        await AuthService.register(name, email, password, passwordConfirm);
        $msg.text("Registration successful").addClass("success");
        this.reset();
      } catch (err) {
        $msg.text(err.message || "Register failed").addClass("error");
      }
    });

    // LOGIN
    $(document).on("submit", "#login-form", async function (e) {
      e.preventDefault();

      const $msg = $("#login-message");
      $msg.text("").removeClass("error success");

      const email = $("#login-email").val().trim();
      const password = $("#login-password").val();

      try {
        const res = await AuthService.login(email, password);
        if (res && res.user) setCurrentUser(res.user);

        updateAuthUI();
        updateAccountPage();
        window.location.hash = "#home";
      } catch (err) {
        $msg.text(err.message || "Login failed").addClass("error");
      }
    });

    // LOGOUT
    $(document).on("click", ".logout-link, #account-logout", async function (e) {
      e.preventDefault();
      await AuthService.logout();
      setCurrentUser(null);
      updateAuthUI();
      updateAccountPage();
      window.location.hash = "#home";
    });

    // SPApp reload hook
    $(document).on("spapp:loaded.formsjs", function () {
      updateAuthUI();
      updateAccountPage();
    });

    $(window).on("hashchange.formsjs", function () {
      setTimeout(() => {
        updateAuthUI();
        if (window.location.hash === "#myaccount") updateAccountPage();
      }, 200);
    });

    // =====================================================
    // CHECKOUT (main.js triggers this event)
    // =====================================================
    $(document).on("checkout:placeOrder", async function (_e, payloadItems) {
      const $msg = $("#checkout-order-message");
      if ($msg.length) $msg.text("").removeClass("error success");

      try {
        const res = await OrderService.placeOrder(payloadItems);

        // Accept different success shapes
        const ok =
          (res && res.success === true) ||
          (res && typeof res.order_id !== "undefined") ||
          (res && typeof res.id !== "undefined");

        if (ok) {
          if ($msg.length) $msg.text("Order placed successfully!").addClass("success");
          $(document).trigger("checkout:success");
        } else {
          if ($msg.length) $msg.text("Could not place order. Please try again.").addClass("error");
        }
      } catch (err) {
        const msg = err?.message || "Could not place order.";
        if ($msg.length) $msg.text(msg).addClass("error");
      }
    });

    syncUser();
  });
})();
