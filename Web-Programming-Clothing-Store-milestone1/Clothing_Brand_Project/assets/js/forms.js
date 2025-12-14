// forms.js (FINAL - Milestone 4 safe)

// Base URL for the PHP backend
const API_BASE = "http://localhost:8000";

(function () {
    "use strict";

    // =====================================================
    // Prevent duplicate init if this file is included twice
    // =====================================================
    if (window.__FORMS_JS_INITED__) return;
    window.__FORMS_JS_INITED__ = true;

    // -----------------------------
    // Helpers for current user
    // -----------------------------
    function setCurrentUser(user) {
        if (user) localStorage.setItem("currentUser", JSON.stringify(user));
        else localStorage.removeItem("currentUser");
    }

    function getCurrentUser() {
        try {
            const raw = localStorage.getItem("currentUser");
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    // -----------------------------
    // Role-based UI helper
    // (call this after SPApp loads content)
    // -----------------------------
    function applyRoleUI() {
        const user = getCurrentUser();
        if (user && user.role === "admin") $(".admin-only").show();
        else $(".admin-only").hide();
    }

    // -----------------------------
    // Update header / offcanvas UI
    // -----------------------------
    function updateAuthUI() {
        const user = getCurrentUser();
        const $authAreas = $(".auth-area");

        if ($authAreas.length) {
            if (user) {
                $authAreas.each(function () {
                    const $area = $(this);
                    $area.html(`
                        <ul>
                            <li>Hello, <span class="user-name"></span></li>
                            <li>/</li>
                            <li><a href="#myaccount" class="goto-account">My Account</a></li>
                            <li>/</li>
                            <li><a href="#" class="logout-link">Logout</a></li>
                        </ul>
                    `);
                    $area.find(".user-name").text(user.name || user.email || "User");
                });
            } else {
                $authAreas.each(function () {
                    const $area = $(this);
                    $area.html(`
                        <ul>
                            <li><a href="#login1">Login</a></li>
                            <li>/</li>
                            <li><a href="#login1">Register</a></li>
                        </ul>
                    `);
                });
            }
        }

        // ✅ IMPORTANT: apply role UI (admin-only show/hide)
        applyRoleUI();
    }

    // -----------------------------
    // My Orders rendering helpers
    // -----------------------------
    function renderMyOrdersTable(orders) {
        const $tbody = $("#orders-table-body");
        if (!$tbody.length) return;

        if (!orders || !orders.length) {
            $tbody.html('<tr><td colspan="5">You have no orders yet.</td></tr>');
            return;
        }

        let rowsHtml = "";
        orders.forEach(function (order) {
            const id     = order.order_id || order.id || "";
            const date   = order.created_at || order.createdAt || "";
            const status = order.status || "pending";
            const total  = order.total_amount || order.total || "";

            rowsHtml += `
                <tr>
                    <td>${id}</td>
                    <td>${date}</td>
                    <td>${status}</td>
                    <td>${total}</td>
                    <td><a href="#order-${id}" class="view-order" data-order-id="${id}">View</a></td>
                </tr>
            `;
        });

        $tbody.html(rowsHtml);
    }

    function loadMyOrders() {
        const user = getCurrentUser();
        const $tbody = $("#orders-table-body");
        if (!$tbody.length) return;

        if (!user) {
            $tbody.html('<tr><td colspan="5">Please log in to see your orders.</td></tr>');
            return;
        }

        $.ajax({
            url: API_BASE + "/api/v1/my-orders",
            method: "GET",
            dataType: "json",
            xhrFields: { withCredentials: true },
            success: function (res) {
                if (res && res.success && res.data && Array.isArray(res.data.items)) {
                    renderMyOrdersTable(res.data.items);
                } else {
                    renderMyOrdersTable([]);
                }
            },
            error: function () {
                $tbody.html('<tr><td colspan="5">Could not load your orders. Please try again later.</td></tr>');
            }
        });
    }

    // -----------------------------
    // Update My Account page
    // -----------------------------
    function updateAccountPage(retries = 8) {
        const user = getCurrentUser();

        const $nameSpan   = $("#acct-name");
        const $emailSpan  = $("#acct-email");
        const $emailInput = $("#acct-email-input");

        // If not loaded yet (SPApp), retry
        if (!$nameSpan.length && !$emailSpan.length && !$emailInput.length) {
            if (retries > 0) setTimeout(() => updateAccountPage(retries - 1), 200);
            return;
        }

        if (!user) {
            if ($nameSpan.length)   $nameSpan.text("Guest");
            if ($emailSpan.length)  $emailSpan.text("not logged in");
            if ($emailInput.length) $emailInput.val("");
        } else {
            const displayName = user.name || "User";
            const email       = user.email || "";

            if ($nameSpan.length)   $nameSpan.text(displayName);
            if ($emailSpan.length)  $emailSpan.text(email);
            if ($emailInput.length) $emailInput.val(email);
        }

        // ✅ After account page exists, apply role UI again
        applyRoleUI();

        // Load orders
        loadMyOrders();
    }

    // -----------------------------
    // INITIAL USER SYNC FROM BACKEND
    // -----------------------------
    function refreshUserFromBackend() {
        $.ajax({
            url: API_BASE + "/api/me",
            method: "GET",
            dataType: "json",
            xhrFields: { withCredentials: true },
            success: function (res) {
                if (res && res.authenticated && res.user) setCurrentUser(res.user);
                else setCurrentUser(null);

                updateAuthUI();
                updateAccountPage();
            },
            error: function () {
                updateAuthUI();
                updateAccountPage();
            }
        });
    }

    // =====================================================
    // Document ready
    // =====================================================
    $(function () {

        // CONTACT FORM (unchanged)
        let contactInquiries = [];
        if ($("#contact-form").length) {
            $("#contact-form").validate({
                rules: {
                    name: "required",
                    email: { required: true, email: true },
                    subject: "required",
                    message: "required"
                },
                messages: {
                    name: "Please enter your name",
                    email: "Please enter a valid email address",
                    subject: "Please enter a subject",
                    message: "Please enter your message"
                },
                submitHandler: function (form, event) {
                    event.preventDefault();

                    if (typeof blockUI === "function") blockUI("#contact-message");

                    let data = {};
                    $.each($(form).serializeArray(), function () {
                        data[this.name] = this.value;
                    });
                    contactInquiries.push(data);
                    $("#contact-form")[0].reset();

                    if (typeof unblockUI === "function") unblockUI("#contact-message");
                }
            });
        }

        // REGISTER
        $(document).off("submit", "#register-form").on("submit", "#register-form", function (e) {
            e.preventDefault();

            const $msg = $("#register-message");
            $msg.text("").removeClass("error success");

            const name            = $("#reg-name").val().trim();
            const email           = $("#reg-email").val().trim();
            const password        = $("#reg-password").val();
            const passwordConfirm = $("#reg-password-confirm").val();

            $.ajax({
                url: API_BASE + "/api/register",
                method: "POST",
                contentType: "application/json",
                dataType: "json",
                xhrFields: { withCredentials: true },
                data: JSON.stringify({ name, email, password, password_confirm: passwordConfirm }),
                success: function (res) {
                    if (res && res.success) {
                        $msg.text("Registration successful! You can now log in.").addClass("success");
                        $("#register-form")[0].reset();
                    } else {
                        const errors = (res && res.errors) ? res.errors.join(" ") : "Unknown error.";
                        $msg.text("Registration failed: " + errors).addClass("error");
                    }
                },
                error: function (xhr) {
                    let text = "Registration failed.";
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (json && json.errors) text += " " + json.errors.join(" ");
                    } catch (_) {}
                    $msg.text(text).addClass("error");
                }
            });
        });

        // LOGIN
        $(document).off("submit", "#login-form").on("submit", "#login-form", function (e) {
            e.preventDefault();

            const $msg = $("#login-message");
            $msg.text("").removeClass("error success");

            const email    = $("#login-email").val().trim();
            const password = $("#login-password").val();

            $.ajax({
                url: API_BASE + "/api/login",
                method: "POST",
                contentType: "application/json",
                dataType: "json",
                xhrFields: { withCredentials: true },
                data: JSON.stringify({ email, password }),
                success: function (res) {
                    if (res && res.success) {
                        $msg.text("Login successful!").addClass("success");

                        if (res.user) {
                            setCurrentUser(res.user);
                            updateAuthUI();
                            updateAccountPage();
                        }

                        // ✅ FIX: hash must include #
                        window.location.hash = "#home";
                    } else {
                        const errors = (res && res.errors) ? res.errors.join(" ") : "Unknown error.";
                        $msg.text("Login failed: " + errors).addClass("error");
                    }
                },
                error: function (xhr) {
                    let text = "Login failed.";
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (json && json.errors) text += " " + json.errors.join(" ");
                    } catch (_) {}
                    $msg.text(text).addClass("error");
                }
            });
        });

        // LOGOUT (header/offcanvas)
        $(document).off("click", ".logout-link").on("click", ".logout-link", function (e) {
            e.preventDefault();

            $.ajax({
                url: API_BASE + "/api/logout",
                method: "POST",
                xhrFields: { withCredentials: true }
            }).always(function () {
                setCurrentUser(null);
                updateAuthUI();
                updateAccountPage();
                window.location.hash = "#home";
            });
        });

        // LOGOUT (myaccount sidebar)
        $(document).off("click", "#account-logout").on("click", "#account-logout", function (e) {
            e.preventDefault();

            $.ajax({
                url: API_BASE + "/api/logout",
                method: "POST",
                xhrFields: { withCredentials: true }
            }).always(function () {
                setCurrentUser(null);
                updateAuthUI();
                updateAccountPage();
                window.location.hash = "#home";
            });
        });

        // ✅ KEY FIX: Re-apply UI AFTER SPApp loads sections
        // Works even if SPApp is slow/async
        $(document).off("spapp:loaded.formsjs").on("spapp:loaded.formsjs", function () {
            updateAuthUI();
            updateAccountPage();
        });

        // Hash change (myaccount)
        $(window).off("hashchange.formsjs").on("hashchange.formsjs", function () {
            const hash = window.location.hash || "";

            // whenever any page changes, re-apply role UI
            setTimeout(function () {
                updateAuthUI();

                if (hash === "#myaccount") {
                    updateAccountPage();
                }
            }, 200);
        });

        // First load
        refreshUserFromBackend();
    });

})();
