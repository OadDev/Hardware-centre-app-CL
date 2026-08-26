// Hardware Centers — shared site behaviour
(function () {
  "use strict";

  var API_BASE = "/api/v1";

  function showToast(message) {
    var toast = document.querySelector("[data-toast]");
    if (!toast) return;
    var textEl = toast.querySelector("[data-toast-text]");
    if (textEl && message) textEl.textContent = message;
    toast.classList.add("show");
    setTimeout(function () { toast.classList.remove("show"); }, 3200);
  }

  function fmtINR(n) {
    return "₹" + Math.round(n).toLocaleString("en-IN");
  }

  // ---------------- Customer session (Sanctum token, shared with the app's API) ----------------
  var TOKEN_KEY = "hc_customer_token";
  var CUSTOMER_KEY = "hc_customer";

  function getToken() { return localStorage.getItem(TOKEN_KEY); }
  function getCustomer() {
    try { return JSON.parse(localStorage.getItem(CUSTOMER_KEY) || "null"); } catch (e) { return null; }
  }
  function isLoggedIn() { return !!getToken(); }
  function setSession(token, customer) {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(CUSTOMER_KEY, JSON.stringify(customer));
    paintAccountUI();
    refreshCartBadge();
    refreshWishlistState();
  }
  function clearSession() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(CUSTOMER_KEY);
    paintAccountUI();
    refreshCartBadge();
    refreshWishlistState();
  }

  function apiFetch(path, options) {
    options = options || {};
    var headers = Object.assign({ Accept: "application/json" }, options.headers || {});
    if (options.body && !(options.body instanceof FormData)) headers["Content-Type"] = "application/json";
    var token = getToken();
    if (token) headers.Authorization = "Bearer " + token;

    return fetch(API_BASE + path, Object.assign({}, options, { headers: headers })).then(function (r) {
      if (r.status === 401 && token) clearSession();
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) {
          var err = new Error(data.message || "Something went wrong. Please try again.");
          err.data = data;
          throw err;
        }
        return data;
      });
    });
  }

  function refreshCartBadge() {
    var dots = document.querySelectorAll("[data-cart-badge]");
    var texts = document.querySelectorAll("[data-cart-badge-text]");
    if (!isLoggedIn()) {
      dots.forEach(function (el) { el.style.display = "none"; });
      texts.forEach(function (el) { el.textContent = ""; });
      return;
    }
    apiFetch("/cart")
      .then(function (data) {
        var count = (data.totals && data.totals.count) || 0;
        dots.forEach(function (el) { el.style.display = count > 0 ? "block" : "none"; });
        texts.forEach(function (el) { el.textContent = count > 0 ? "(" + count + ")" : ""; });
      })
      .catch(function () {});
  }

  // ---------------- Login modal (phone/email OTP, same flow as the app) ----------------
  var otpChannelPromise = null;
  function getOtpChannel() {
    if (!otpChannelPromise) {
      otpChannelPromise = apiFetch("/auth/otp/channel").then(function (d) { return d.channel; }).catch(function () { return "email"; });
    }
    return otpChannelPromise;
  }

  var loginModalEl = null;
  var loginOnSuccess = null;
  var loginChannel = "email";
  var loginContact = "";

  function ensureLoginModal() {
    if (loginModalEl) return loginModalEl;
    var wrap = document.createElement("div");
    wrap.className = "hc-modal-backdrop";
    wrap.setAttribute("data-login-modal", "");
    wrap.innerHTML =
      '<div class="hc-modal-sheet">' +
        '<button type="button" class="hc-modal-close" data-login-close aria-label="Close">' +
          '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>' +
        '</button>' +
        '<div data-login-step="contact">' +
          '<h3 class="hc-modal-title">Login or Sign Up</h3>' +
          '<p class="hc-modal-sub" data-login-contact-label>Enter your mobile number to continue</p>' +
          '<div class="field" style="margin-top:16px;"><input type="text" data-login-contact placeholder="10-digit mobile number" /></div>' +
          '<div class="hc-modal-error" data-login-error style="display:none;"></div>' +
          '<button type="button" class="btn btn-primary btn-block" style="margin-top:16px;" data-login-send>Send OTP</button>' +
        '</div>' +
        '<div data-login-step="otp" style="display:none;">' +
          '<h3 class="hc-modal-title">Enter verification code</h3>' +
          '<p class="hc-modal-sub" data-login-otp-sub></p>' +
          '<div class="field" style="margin-top:16px;"><input type="text" maxlength="6" inputmode="numeric" data-login-otp placeholder="6-digit code" /></div>' +
          '<div class="hc-modal-error" data-login-error-otp style="display:none;"></div>' +
          '<button type="button" class="btn btn-primary btn-block" style="margin-top:16px;" data-login-verify>Verify &amp; Continue</button>' +
          '<button type="button" class="hc-modal-link" data-login-back>&larr; Use a different number</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);
    loginModalEl = wrap;

    var stepContact = wrap.querySelector('[data-login-step="contact"]');
    var stepOtp = wrap.querySelector('[data-login-step="otp"]');

    var showStep = function (step) {
      stepContact.style.display = step === "contact" ? "" : "none";
      stepOtp.style.display = step === "otp" ? "" : "none";
      wrap.querySelector("[data-login-error]").style.display = "none";
      wrap.querySelector("[data-login-error-otp]").style.display = "none";
    };

    var closeModal = function () {
      wrap.classList.remove("show");
      loginOnSuccess = null;
    };

    wrap.addEventListener("click", function (e) {
      if (e.target === wrap) closeModal();
    });
    wrap.querySelector("[data-login-close]").addEventListener("click", closeModal);
    wrap.querySelector("[data-login-back]").addEventListener("click", function () { showStep("contact"); });

    wrap.querySelector("[data-login-send]").addEventListener("click", function () {
      var btn = this;
      var input = wrap.querySelector("[data-login-contact]");
      var errorEl = wrap.querySelector("[data-login-error]");
      var value = input.value.trim();
      errorEl.style.display = "none";

      if (!value) {
        errorEl.textContent = loginChannel === "sms" ? "Enter your mobile number." : "Enter your email address.";
        errorEl.style.display = "";
        return;
      }

      btn.disabled = true;
      var payload = loginChannel === "sms" ? { phone: value } : { email: value };
      apiFetch("/auth/otp/request", { method: "POST", body: JSON.stringify(payload) })
        .then(function (data) {
          btn.disabled = false;
          loginContact = value;
          wrap.querySelector("[data-login-otp-sub]").textContent = data.message || "We've sent you a verification code.";
          wrap.querySelector("[data-login-otp]").value = data.debug_otp || "";
          showStep("otp");
          wrap.querySelector("[data-login-otp]").focus();
        })
        .catch(function (err) {
          btn.disabled = false;
          errorEl.textContent = err.message;
          errorEl.style.display = "";
        });
    });

    wrap.querySelector("[data-login-verify]").addEventListener("click", function () {
      var btn = this;
      var input = wrap.querySelector("[data-login-otp]");
      var errorEl = wrap.querySelector("[data-login-error-otp]");
      var code = input.value.trim();
      errorEl.style.display = "none";

      if (!code) {
        errorEl.textContent = "Enter the 6-digit code.";
        errorEl.style.display = "";
        return;
      }

      btn.disabled = true;
      var payload = loginChannel === "sms" ? { phone: loginContact, otp: code } : { email: loginContact, otp: code };
      apiFetch("/auth/otp/verify", { method: "POST", body: JSON.stringify(payload) })
        .then(function (data) {
          btn.disabled = false;
          var cb = loginOnSuccess;
          setSession(data.token, data.customer);
          closeModal();
          showToast("Welcome, " + (data.customer.name || "there").split(" ")[0] + "!");
          if (cb) cb();
        })
        .catch(function (err) {
          btn.disabled = false;
          errorEl.textContent = err.message;
          errorEl.style.display = "";
        });
    });

    return wrap;
  }

  function openLoginModal(onSuccess) {
    var wrap = ensureLoginModal();
    loginOnSuccess = onSuccess || null;
    wrap.querySelector('[data-login-step="contact"]').style.display = "";
    wrap.querySelector('[data-login-step="otp"]').style.display = "none";
    wrap.querySelector("[data-login-contact]").value = "";
    getOtpChannel().then(function (channel) {
      loginChannel = channel;
      wrap.querySelector("[data-login-contact-label]").textContent = channel === "sms" ? "Enter your mobile number to continue" : "Enter your email address to continue";
      wrap.querySelector("[data-login-contact]").placeholder = channel === "sms" ? "10-digit mobile number" : "you@example.com";
    });
    wrap.classList.add("show");
    setTimeout(function () { wrap.querySelector("[data-login-contact]").focus(); }, 50);
  }

  function paintAccountUI() {
    var customer = getCustomer();
    document.querySelectorAll("[data-account-label]").forEach(function (el) {
      el.textContent = customer ? "Hi, " + (customer.name || "there").split(" ")[0] : "Login / Sign Up";
    });
    document.querySelectorAll("[data-account-trigger]").forEach(function (link) {
      if (link.dataset.accountBound) return;
      link.dataset.accountBound = "1";
      link.addEventListener("click", function (e) {
        e.preventDefault();
        if (isLoggedIn()) {
          if (confirm("Log out of your Hardware Centers account?")) {
            apiFetch("/logout", { method: "POST" }).catch(function () {}).then(function () {
              clearSession();
              showToast("Logged out");
              if (window.location.pathname === "/cart" || window.location.pathname === "/checkout") {
                window.location.href = "/";
              }
            });
          }
        } else {
          openLoginModal();
        }
      });
    });
  }

  // ---------------- Delivery location (GPS or a saved address, same UX as the app) ----------------
  var DELIVERY_LABEL_KEY = "hc_delivery_label_v1";

  function getDeliveryLabel() { return localStorage.getItem(DELIVERY_LABEL_KEY) || ""; }
  function paintDeliveryLabel() {
    var label = getDeliveryLabel();
    document.querySelectorAll("[data-deliver-label]").forEach(function (el) {
      el.textContent = label || "Set delivery location";
    });
    document.querySelectorAll("[data-pdp-delivery-to]").forEach(function (el) {
      el.textContent = label ? "to " + label : "to your location";
    });
  }
  function setDeliveryLabel(label) {
    localStorage.setItem(DELIVERY_LABEL_KEY, label);
    paintDeliveryLabel();
  }

  var deliveryModalEl = null;
  function closeDeliveryModal() {
    if (deliveryModalEl) deliveryModalEl.classList.remove("show");
  }

  function ensureDeliveryModal() {
    if (deliveryModalEl) return deliveryModalEl;
    var wrap = document.createElement("div");
    wrap.className = "hc-modal-backdrop";
    wrap.setAttribute("data-delivery-modal", "");
    wrap.innerHTML =
      '<div class="hc-modal-sheet">' +
        '<button type="button" class="hc-modal-close" data-deliver-close aria-label="Close">' +
          '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>' +
        '</button>' +
        '<h3 class="hc-modal-title">Delivery Location</h3>' +
        '<p class="hc-modal-sub">Find products and delivery times for your area.</p>' +
        '<button type="button" class="btn btn-outline btn-block" data-deliver-use-gps style="margin-top:16px;">' +
          '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:7px;vertical-align:-3px;"><path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>' +
          '<span data-deliver-gps-label>Use My Current Location</span>' +
        '</button>' +
        '<div data-deliver-addresses></div>' +
        '<a href="/checkout" class="hc-modal-link" style="display:block;margin-top:14px;">+ Add a new address</a>' +
      '</div>';
    document.body.appendChild(wrap);
    deliveryModalEl = wrap;

    wrap.addEventListener("click", function (e) { if (e.target === wrap) closeDeliveryModal(); });
    wrap.querySelector("[data-deliver-close]").addEventListener("click", closeDeliveryModal);

    wrap.querySelector("[data-deliver-use-gps]").addEventListener("click", function () {
      var btn = this;
      var label = wrap.querySelector("[data-deliver-gps-label]");
      if (!navigator.geolocation) {
        showToast("Location isn't supported in this browser");
        return;
      }
      btn.disabled = true;
      label.textContent = "Getting your location…";
      navigator.geolocation.getCurrentPosition(
        function (pos) {
          fetch("https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" + pos.coords.latitude + "&lon=" + pos.coords.longitude, {
            headers: { "Accept-Language": "en" },
          })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              btn.disabled = false;
              label.textContent = "Use My Current Location";
              var a = data.address || {};
              var city = a.city || a.town || a.village || a.suburb || "";
              var pincode = a.postcode || "";
              var text = [city, pincode].filter(Boolean).join(" ");
              if (!text) {
                showToast("Could not determine your address");
                return;
              }
              setDeliveryLabel(text);
              showToast("Delivery location updated");
              closeDeliveryModal();
            })
            .catch(function () {
              btn.disabled = false;
              label.textContent = "Use My Current Location";
              showToast("Could not look up your address");
            });
        },
        function () {
          btn.disabled = false;
          label.textContent = "Use My Current Location";
          showToast("Location permission denied");
        },
        { enableHighAccuracy: false, timeout: 12000 }
      );
    });

    return wrap;
  }

  function openDeliveryModal() {
    var wrap = ensureDeliveryModal();
    var listEl = wrap.querySelector("[data-deliver-addresses]");
    listEl.innerHTML = "";

    if (isLoggedIn()) {
      apiFetch("/addresses")
        .then(function (res) {
          var addresses = res.data || res;
          if (!addresses || !addresses.length) return;
          var heading = document.createElement("div");
          heading.style.cssText = "font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin:18px 0 10px;";
          heading.textContent = "Saved addresses";
          listEl.appendChild(heading);
          addresses.forEach(function (a) {
            var row = document.createElement("div");
            row.className = "addr-card";
            row.style.cssText = "margin-bottom:8px;";
            row.innerHTML =
              '<div style="flex:1;min-width:0;">' +
                '<div style="font-weight:700;font-size:13.5px;">' + a.label + ' · ' + a.name + '</div>' +
                '<div class="addr-line">' + a.line + ', ' + a.city + ', ' + a.state + ' ' + a.pincode + '</div>' +
              '</div>';
            row.addEventListener("click", function () {
              setDeliveryLabel(a.city + " " + a.pincode);
              showToast("Delivery location updated");
              closeDeliveryModal();
            });
            listEl.appendChild(row);
          });
        })
        .catch(function () {});
    }

    wrap.classList.add("show");
  }

  // Runs on every page load so the header always reflects the current session.
  document.addEventListener("DOMContentLoaded", function () {
    paintAccountUI();
    refreshCartBadge();
    refreshWishlistState();
    paintDeliveryLabel();
    document.querySelectorAll("[data-deliver-trigger]").forEach(function (el) {
      el.addEventListener("click", openDeliveryModal);
    });
  });

  // Mobile nav drawer
  var toggle = document.querySelector(".mobile-toggle");
  var drawer = document.querySelector(".mobile-nav");
  if (toggle && drawer) {
    toggle.addEventListener("click", function () { drawer.classList.add("open"); });
    drawer.addEventListener("click", function (e) {
      if (e.target.closest(".close-btn") || e.target.classList.contains("backdrop")) {
        drawer.classList.remove("open");
      }
    });
  }

  // FAQ accordion
  document.querySelectorAll(".faq-q").forEach(function (q) {
    q.addEventListener("click", function () {
      var item = q.closest(".faq-item");
      var wasOpen = item.classList.contains("open");
      item.parentElement.querySelectorAll(".faq-item.open").forEach(function (o) {
        if (o !== item) o.classList.remove("open");
      });
      item.classList.toggle("open", !wasOpen);
    });
  });

  // Generic tab groups: [data-tabs] wraps [data-tab] buttons + [data-tab-panel] panels
  document.querySelectorAll("[data-tabs]").forEach(function (group) {
    var buttons = group.querySelectorAll("[data-tab]");
    var panels = document.querySelectorAll("[data-tab-panel]");
    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        buttons.forEach(function (b) { b.classList.remove("active"); });
        btn.classList.add("active");
        var key = btn.getAttribute("data-tab");
        panels.forEach(function (p) {
          p.style.display = p.getAttribute("data-tab-panel") === key ? "" : "none";
        });
      });
    });
  });

  // Deals countdown timer
  var timerEls = document.querySelectorAll("[data-countdown]");
  if (timerEls.length) {
    var end = Date.now() + 1000 * (6 * 3600 + 24 * 60 + 51);
    setInterval(function () {
      var diff = Math.max(0, end - Date.now());
      var h = Math.floor(diff / 3600000);
      var m = Math.floor((diff % 3600000) / 60000);
      var s = Math.floor((diff % 60000) / 1000);
      var pad = function (n) { return String(n).padStart(2, "0"); };
      timerEls.forEach(function (el) {
        var box = el.querySelectorAll(".box");
        if (box.length >= 3) {
          box[0].textContent = pad(h);
          box[1].textContent = pad(m);
          box[2].textContent = pad(s);
        }
      });
    }, 1000);
  }

  // Category -> subcategory panel switcher (categories.html)
  var catCards = document.querySelectorAll("[data-catcard]");
  var subPanels = document.querySelectorAll("[data-subpanel]");
  if (catCards.length && subPanels.length) {
    var selectCat = function (key, scroll) {
      catCards.forEach(function (c) { c.classList.toggle("is-active", c.getAttribute("data-catcard") === key); });
      subPanels.forEach(function (p) {
        p.style.display = p.getAttribute("data-subpanel") === key ? "" : "none";
      });
      if (scroll) {
        var target = document.getElementById("subcat-panel-anchor");
        if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    };
    catCards.forEach(function (card) {
      card.addEventListener("click", function () {
        selectCat(card.getAttribute("data-catcard"), true);
      });
    });
    if (window.location.hash) {
      var key = window.location.hash.replace("#", "");
      if (document.querySelector('[data-catcard="' + key + '"]')) selectCat(key, true);
    }
  }

  // Recent/popular search chip -> fill search box
  document.querySelectorAll("[data-fill-search]").forEach(function (chip) {
    chip.addEventListener("click", function () {
      var input = document.querySelector("[data-search-input]");
      if (input) { input.value = chip.getAttribute("data-fill-search"); input.dispatchEvent(new Event("input")); input.focus(); }
    });
  });

  // ---------------- Live product search (search.html) ----------------
  function productCardHTML(p) {
    var off = p.mrp > 0 ? Math.round((1 - p.price / p.mrp) * 100) : 0;
    var tagHTML = p.tag
      ? '<div class="prod-tag" style="background:#FFF7ED;color:#EA580C;">' + p.tag + '</div>'
      : '';
    var mrpHTML = off > 0 ? '<span class="prod-mrp">' + fmtINR(p.mrp) + '</span>' : '';
    var thumbHTML = (p.images && p.images.length)
      ? '<img src="' + p.images[0] + '" alt="' + p.name + '" loading="lazy">'
      : '<span>' + p.brand + '<br>' + p.name + '</span>';
    return (
      '<a class="prod-card" href="/product/' + p.id + '" style="width:100%;" data-brand="' + p.brand + '">' +
      '<div class="prod-thumb" style="background:linear-gradient(135deg,#F8FAFC,#EEF1F4);">' +
      thumbHTML + tagHTML +
      '<button class="prod-wish" data-wish-id="' + p.id + '" aria-label="Save"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></button>' +
      '</div><div class="prod-body">' +
      '<div class="prod-brand">' + p.brand + '</div>' +
      '<div class="prod-name">' + p.name + '</div>' +
      '<div class="prod-rating"><svg width="12" height="12" viewBox="0 0 24 24" fill="#F59E0B" stroke="none" stroke-width="0"><path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z"/></svg> ' + Number(p.rating).toFixed(1) + ' <span class="rc">(' + Number(p.reviews).toLocaleString('en-IN') + ')</span></div>' +
      '<div class="prod-price-row"><div><span class="prod-price">' + fmtINR(p.price) + '</span>' + mrpHTML + '</div>' +
      '<button class="prod-add" data-add-to-cart="' + p.id + '" aria-label="Add to cart"' + (p.stock <= 0 ? ' disabled' : '') + '><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></button></div>' +
      '</div></a>'
    );
  }

  var searchResults = document.getElementById("search-results");
  var searchInput = document.querySelector("[data-search-input]");
  var brandChecks = document.querySelectorAll("[data-brand-filter]");

  if (searchResults && searchInput) {
    var searchDebounce = null;
    var urlCategory = new URLSearchParams(window.location.search).get("category");
    var urlBrand = new URLSearchParams(window.location.search).get("brand");
    if (urlBrand) {
      Array.prototype.forEach.call(brandChecks, function (c) {
        if (c.getAttribute("data-brand-filter") === urlBrand) c.checked = true;
      });
    }
    var runSearch = function () {
      var q = searchInput.value.trim();
      var brands = Array.prototype.filter.call(brandChecks, function (c) { return c.checked; })
        .map(function (c) { return c.getAttribute("data-brand-filter"); });

      var params = new URLSearchParams();
      if (q) params.set("q", q);
      if (urlCategory) params.set("category", urlCategory);
      if (brands.length) params.set("brands", brands.join(","));
      params.set("per_page", "24");

      fetch(API_BASE + "/products?" + params.toString())
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var items = data.data || [];
          var countEl = document.querySelector("[data-result-count]");
          var titleEl = document.getElementById("search-results-title");
          if (countEl) countEl.textContent = items.length + " products found";
          if (titleEl) titleEl.textContent = q ? ('Results for "' + q + '"') : (brands.length ? brands.join(", ") + " Products" : (urlCategory ? "Category Results" : "Trending Products"));
          searchResults.innerHTML = items.length
            ? items.map(productCardHTML).join("")
            : '<p style="color:var(--muted);grid-column:1/-1;">No products matched your search.</p>';
          bindWishButtons(searchResults);
        })
        .catch(function () {
          showToast("Could not load search results. Try again.");
        });
    };

    if (urlCategory || urlBrand) runSearch();
    searchInput.addEventListener("input", function () {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(runSearch, 300);
    });
    brandChecks.forEach(function (c) { c.addEventListener("change", runSearch); });
  } else if (searchInput) {
    // categories.html / other pages: plain client-side name filter, no live results container
    searchInput.addEventListener("input", function () {
      var term = searchInput.value.trim().toLowerCase();
      document.querySelectorAll("[data-search-target]").forEach(function (card) {
        var name = (card.getAttribute("data-search-target") || "").toLowerCase();
        card.style.display = !term || name.indexOf(term) !== -1 ? "" : "none";
      });
    });
  }

  // ---------------- Track order ----------------
  var trackForm = document.querySelector("[data-track-form]");
  if (trackForm) {
    trackForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var url = trackForm.getAttribute("data-track-form");
      var errorEl = document.querySelector("[data-track-error]");
      var resultEl = document.querySelector("[data-track-result]");
      var formData = new FormData(trackForm);
      var submitBtn = trackForm.querySelector("button[type=submit]");

      if (errorEl) errorEl.style.display = "none";
      if (submitBtn) submitBtn.disabled = true;

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ order_no: formData.get("order_no"), contact: formData.get("contact") }),
      })
        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
        .then(function (res) {
          if (submitBtn) submitBtn.disabled = false;
          if (!res.ok) {
            if (errorEl) { errorEl.textContent = res.data.message || "Order not found."; errorEl.style.display = ""; }
            if (resultEl) resultEl.style.display = "none";
            return;
          }
          var order = res.data.order;
          var timeline = res.data.timeline;

          document.querySelector("[data-track-order-no]").textContent = order.no;
          var statusEl = document.querySelector("[data-track-status]");
          statusEl.textContent = order.status;
          statusEl.className = "badge " + (order.status === "Delivered" ? "" : order.status === "Cancelled" || order.status === "Returned" ? "" : "badge-orange");
          statusEl.style.color = order.status === "Delivered" ? "#16A34A" : order.status === "Cancelled" || order.status === "Returned" ? "#EF4444" : "#EA580C";
          statusEl.style.background = order.status === "Delivered" ? "#F0FDF4" : order.status === "Cancelled" || order.status === "Returned" ? "#FEF2F2" : "#FFF7ED";

          document.querySelector("[data-track-title]").textContent = order.title;
          document.querySelector("[data-track-sub]").textContent = order.item_count + (order.item_count > 1 ? " items · " : " item · ") + fmtINR(order.total) + " total";

          var timelineEl = document.querySelector("[data-track-timeline]");
          timelineEl.innerHTML = timeline.map(function (step) {
            var cls = "tstep" + (step.state === "done" ? " done" : step.state === "current" ? " current" : "");
            var dotInner = step.state === "pending"
              ? ""
              : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
            return '<div class="' + cls + '"><div class="dot-col"><div class="dot">' + dotInner + '</div><div class="bar"></div></div>' +
              '<div class="content"><div class="title">' + step.title + '</div><div class="time">' + step.time + '</div></div></div>';
          }).join("");

          if (resultEl) { resultEl.style.display = ""; resultEl.scrollIntoView({ behavior: "smooth", block: "start" }); }
        })
        .catch(function () {
          if (submitBtn) submitBtn.disabled = false;
          if (errorEl) { errorEl.textContent = "Something went wrong. Please try again."; errorEl.style.display = ""; }
        });
    });
  }

  // ---------------- My Orders lookup ----------------
  var myOrdersBtn = document.querySelector("[data-my-orders-submit]");
  if (myOrdersBtn) {
    var STATUS_BUCKET = {
      Pending: "ongoing", Confirmed: "ongoing", Packed: "ongoing", Shipped: "ongoing", "Out for Delivery": "ongoing",
      Delivered: "Delivered", Cancelled: "Cancelled", Returned: "Cancelled",
    };
    var orderCardHTML = function (o) {
      var badgeColor = o.status === "Delivered" ? { c: "#16A34A", bg: "#F0FDF4" } : (o.status === "Cancelled" || o.status === "Returned") ? { c: "#EF4444", bg: "#FEF2F2" } : { c: "#EA580C", bg: "#FFF7ED" };
      return '<div class="order-card" data-order-status="' + (STATUS_BUCKET[o.status] || "ongoing") + '">' +
        '<div class="order-top"><span class="order-no">' + o.no + '</span><span class="badge" style="color:' + badgeColor.c + ';background:' + badgeColor.bg + ';">' + o.status + '</span></div>' +
        '<div class="order-date">' + o.date + '</div>' +
        '<div class="order-item"><div class="thumb"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.6"><path d="M6 2h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></div>' +
        '<div><div class="title">' + o.title + '</div><div class="sub">' + o.item_count + (o.item_count > 1 ? ' items' : ' item') + (o.seller ? ' · ' + o.seller : '') + '</div></div></div>' +
        '<div class="order-foot"><div class="order-total">Total<b>' + fmtINR(o.total) + '</b></div></div></div>';
    };

    var runMyOrdersLookup = function () {
      var url = myOrdersBtn.getAttribute("data-my-orders-submit");
      var input = document.querySelector("[data-my-orders-input]");
      var errorEl = document.querySelector("[data-my-orders-error]");
      var resultsEl = document.querySelector("[data-my-orders-results]");
      var listEl = document.querySelector("[data-my-orders-list]");
      var emptyEl = document.querySelector("[data-my-orders-empty]");
      var contact = input.value.trim();

      if (errorEl) errorEl.style.display = "none";
      if (!contact) {
        if (errorEl) { errorEl.textContent = "Enter the email or phone used at checkout."; errorEl.style.display = ""; }
        return;
      }

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ contact: contact }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var orders = data.orders || [];
          if (resultsEl) resultsEl.style.display = "";
          if (listEl) listEl.innerHTML = orders.map(orderCardHTML).join("");
          if (emptyEl) emptyEl.style.display = orders.length ? "none" : "";
          if (resultsEl) resultsEl.scrollIntoView({ behavior: "smooth", block: "start" });
        })
        .catch(function () {
          if (errorEl) { errorEl.textContent = "Something went wrong. Please try again."; errorEl.style.display = ""; }
        });
    };

    myOrdersBtn.addEventListener("click", runMyOrdersLookup);
    var moInput = document.querySelector("[data-my-orders-input]");
    if (moInput) moInput.addEventListener("keydown", function (e) { if (e.key === "Enter") runMyOrdersLookup(); });

    document.querySelectorAll("[data-order-tab]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        document.querySelectorAll("[data-order-tab]").forEach(function (b) { b.classList.remove("active"); });
        btn.classList.add("active");
        var key = btn.getAttribute("data-order-tab");
        document.querySelectorAll("[data-order-status]").forEach(function (card) {
          card.style.display = (key === "all" || card.getAttribute("data-order-status") === key) ? "" : "none";
        });
      });
    });
  }

  // ---------------- Generic API-backed forms (contact, sell, support, newsletter) ----------------
  document.querySelectorAll("[data-api-form]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var url = form.getAttribute("data-api-form");
      var submitBtn = form.querySelector("button[type=submit]");
      var payload = {};
      Array.prototype.forEach.call(form.elements, function (el) {
        if (el.name) payload[el.name] = el.value;
      });

      if (submitBtn) submitBtn.disabled = true;
      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(payload),
      })
        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
        .then(function (res) {
          if (submitBtn) submitBtn.disabled = false;
          if (res.ok) {
            showToast(res.data.message || "Thanks! We'll be in touch shortly.");
            form.reset();
          } else {
            var msg = res.data.message || (res.data.errors ? Object.values(res.data.errors).flat().join(" ") : "Something went wrong.");
            showToast(msg);
          }
        })
        .catch(function () {
          if (submitBtn) submitBtn.disabled = false;
          showToast("Something went wrong. Please try again.");
        });
    });
  });

  // ---------------- Wishlist (synced with the same account as the app) ----------------
  var wishlistIds = null; // null = not loaded yet; Set of product_codes once loaded

  function paintWish(btn, active) {
    btn.classList.toggle("active", active);
    var svg = btn.querySelector("svg");
    if (svg) {
      svg.setAttribute("fill", active ? "#F97316" : "#9CA3AF");
      svg.setAttribute("stroke", active ? "#F97316" : "#9CA3AF");
    }
  }

  function paintAllWishButtons() {
    document.querySelectorAll(".prod-wish, [data-pdp-wish]").forEach(function (btn) {
      var id = btn.getAttribute("data-wish-id") || btn.getAttribute("data-pdp-wish");
      if (!id || !wishlistIds) return;
      paintWish(btn, wishlistIds.has(id));
      if (btn.hasAttribute("data-pdp-wish")) {
        btn.classList.toggle("active", wishlistIds.has(id));
      }
    });
  }

  function refreshWishlistState() {
    if (!isLoggedIn()) {
      wishlistIds = new Set();
      paintAllWishButtons();
      return;
    }
    apiFetch("/wishlist")
      .then(function (res) {
        var items = res.data || res;
        wishlistIds = new Set((items || []).map(function (p) { return p.id; }));
        paintAllWishButtons();
      })
      .catch(function () { wishlistIds = new Set(); });
  }

  function toggleWish(pid, btn) {
    var run = function () {
      var active = wishlistIds.has(pid);
      var method = active ? "DELETE" : "POST";
      apiFetch("/wishlist/" + pid, { method: method })
        .then(function () {
          if (active) wishlistIds.delete(pid); else wishlistIds.add(pid);
          paintWish(btn, !active);
          if (btn.hasAttribute("data-pdp-wish")) btn.classList.toggle("active", !active);
        })
        .catch(function (err) { showToast(err.message); });
    };
    if (!isLoggedIn()) { openLoginModal(run); return; }
    run();
  }

  function bindWishButtons(root) {
    (root || document).querySelectorAll(".prod-wish").forEach(function (btn) {
      var id = btn.getAttribute("data-wish-id");
      if (id && wishlistIds) paintWish(btn, wishlistIds.has(id));
      if (btn.dataset.wishBound) return;
      btn.dataset.wishBound = "1";
      btn.addEventListener("click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var pid = btn.getAttribute("data-wish-id");
        if (pid) toggleWish(pid, btn);
      });
    });
  }
  bindWishButtons(document);

  // A blocked PDP button (no variant picked yet, or the picked one is out
  // of stock) stays clickable on purpose — tapping it explains why instead
  // of doing nothing, which otherwise reads as "the button is broken".
  function explainBlockedPdpButton(btn) {
    var reason = btn.getAttribute("data-block");
    if (!reason) return false;
    if (reason === "select-option") {
      showToast("Please select an option above before continuing");
      var group = document.querySelector(".pdp-variant-group");
      if (group) {
        group.scrollIntoView({ behavior: "smooth", block: "center" });
        group.classList.add("pdp-variant-group-flash");
        setTimeout(function () { group.classList.remove("pdp-variant-group-flash"); }, 900);
      }
    } else if (reason === "out-of-stock") {
      showToast("That option is out of stock");
    }
    return true;
  }

  // ---------------- Add to cart ----------------
  document.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-add-to-cart]");
    if (!btn || btn.disabled) return;
    e.preventDefault();
    e.stopPropagation();
    if (explainBlockedPdpButton(btn)) return;

    var code = btn.getAttribute("data-add-to-cart");
    if (!code) return;
    var variant = btn.getAttribute("data-variant") || null;
    var qty = parseInt(btn.getAttribute("data-qty"), 10) || 1;

    var run = function () {
      var payload = { product_code: code, quantity: qty };
      if (variant) payload.variant_code = variant;
      btn.disabled = true;
      apiFetch("/cart/items", { method: "POST", body: JSON.stringify(payload) })
        .then(function () {
          btn.disabled = false;
          showToast("Added to cart");
          refreshCartBadge();
        })
        .catch(function (err) {
          btn.disabled = false;
          showToast(err.message);
        });
    };

    if (isLoggedIn()) run();
    else openLoginModal(run);
  });

  // ---------------- Buy Now (add to cart, then go straight to cart) ----------------
  document.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-buy-now]");
    if (!btn || btn.disabled) return;
    e.preventDefault();
    e.stopPropagation();
    if (explainBlockedPdpButton(btn)) return;

    var code = btn.getAttribute("data-buy-now");
    if (!code) return;
    var variant = btn.getAttribute("data-variant") || null;
    var qty = parseInt(btn.getAttribute("data-qty"), 10) || 1;

    var run = function () {
      var payload = { product_code: code, quantity: qty };
      if (variant) payload.variant_code = variant;
      btn.disabled = true;
      apiFetch("/cart/items", { method: "POST", body: JSON.stringify(payload) })
        .then(function () {
          window.location.href = "/cart";
        })
        .catch(function (err) {
          btn.disabled = false;
          showToast(err.message);
        });
    };

    if (isLoggedIn()) run();
    else openLoginModal(run);
  });

  // ---------------- Product detail page ----------------
  var pdpWishBtn = document.querySelector("[data-pdp-wish]");
  if (pdpWishBtn) {
    var pdpWishId = pdpWishBtn.getAttribute("data-pdp-wish");
    pdpWishBtn.addEventListener("click", function () {
      if (wishlistIds) toggleWish(pdpWishId, pdpWishBtn);
    });
  }

  document.querySelectorAll("[data-pdp-thumb]").forEach(function (thumb) {
    thumb.addEventListener("click", function () {
      var url = thumb.getAttribute("data-pdp-thumb");
      var heroImg = document.getElementById("pdp-hero-img");
      if (heroImg) heroImg.src = url;
      document.querySelectorAll("[data-pdp-thumb]").forEach(function (t) { t.classList.remove("active"); });
      thumb.classList.add("active");
    });
  });

  var variantDataEl = document.getElementById("pdp-variants-data");
  var pdpAddBtn = document.querySelector('[data-add-to-cart][data-pdp]');
  var pdpBuyBtn = document.querySelector('[data-buy-now][data-pdp]');
  var pdpData = null;
  if (variantDataEl) {
    try { pdpData = JSON.parse(variantDataEl.textContent) || null; } catch (e) { pdpData = null; }
  }

  // ---------------- Quantity stepper ----------------
  if (pdpAddBtn) {
    var qtyValEl = document.querySelector("[data-pdp-qty-val]");
    var qtyMinusBtn = document.querySelector("[data-pdp-qty-minus]");
    var qtyPlusBtn = document.querySelector("[data-pdp-qty-plus]");
    var pdpQty = 1;
    var pdpQtyMax = null; // null = no known stock cap yet (variant products before a pick)

    var syncQtyButtons = function () {
      if (qtyValEl) qtyValEl.textContent = pdpQty;
      pdpAddBtn.setAttribute("data-qty", pdpQty);
      if (pdpBuyBtn) pdpBuyBtn.setAttribute("data-qty", pdpQty);
      if (qtyMinusBtn) qtyMinusBtn.disabled = pdpQty <= 1;
      if (qtyPlusBtn) qtyPlusBtn.disabled = pdpQtyMax !== null && pdpQty >= pdpQtyMax;
    };

    var setQtyMax = function (max) {
      pdpQtyMax = max;
      if (max !== null && pdpQty > max) pdpQty = Math.max(1, max);
      syncQtyButtons();
    };

    if (qtyMinusBtn) qtyMinusBtn.addEventListener("click", function () {
      if (pdpQty > 1) { pdpQty -= 1; syncQtyButtons(); }
    });
    if (qtyPlusBtn) qtyPlusBtn.addEventListener("click", function () {
      if (pdpQtyMax === null || pdpQty < pdpQtyMax) { pdpQty += 1; syncQtyButtons(); }
    });

    var qtyRowEl = document.querySelector("[data-pdp-qty-row]");
    var baseStockAttr = qtyRowEl ? qtyRowEl.getAttribute("data-base-stock") : "";
    if (baseStockAttr) setQtyMax(parseInt(baseStockAttr, 10));
    else syncQtyButtons();
  }

  if (pdpAddBtn && pdpData && pdpData.variants && pdpData.variants.length) {
    var priceEl = document.querySelector(".pdp-price");
    var stockTitleEl = document.querySelector("[data-pdp-stock-title]");
    var stockSubEl = document.querySelector("[data-pdp-stock-sub]");
    var stockCardEl = document.querySelector("[data-pdp-stock-card]");
    var heroImg = document.getElementById("pdp-hero-img");
    var tierBoxEl = document.getElementById("pdp-tier-box-dynamic");
    var defaultHeroSrc = heroImg ? heroImg.src : null;
    var unit = pdpData.unit || "unit";

    var renderTierBox = function (variant) {
      if (!tierBoxEl) return;
      var tiers = (variant.price_tiers && variant.price_tiers.length)
        ? variant.price_tiers
        : (pdpData.apply_bulk_pricing_to_all_variants ? pdpData.product_price_tiers : []);

      if (!tiers || !tiers.length) { tierBoxEl.innerHTML = ""; return; }

      var sorted = tiers.slice().sort(function (a, b) { return a.min_qty - b.min_qty; });
      var rows = '<tr><td>1 – ' + (sorted[0].min_qty - 1) + ' ' + unit + '</td><td>₹' + Math.round(variant.price).toLocaleString("en-IN") + ' / ' + unit + '</td></tr>';
      sorted.forEach(function (tier, i) {
        var next = sorted[i + 1];
        var range = next ? (tier.min_qty + ' – ' + (next.min_qty - 1)) : (tier.min_qty + '+');
        rows += '<tr><td>' + range + ' ' + unit + '</td><td>₹' + Math.round(tier.price).toLocaleString("en-IN") + ' / ' + unit + '</td></tr>';
      });
      tierBoxEl.innerHTML = '<div class="pdp-tier-box"><div class="pdp-tier-title">Buy more, save more</div><table class="pdp-tier-table"><tbody>' + rows + '</tbody></table></div>';
    };

    // Buttons stay clickable even when a variant still needs picking or the
    // picked one is out of stock — a truly `disabled` button swallows the
    // click silently, so a customer tapping it gets no feedback at all and
    // assumes the button is broken. Instead we mark *why* it's blocked via
    // data-block and let the click handlers explain it with a toast.
    var setBtnBlocked = function (btn, reason) {
      if (!btn) return;
      if (reason) { btn.setAttribute("data-block", reason); btn.classList.add("is-blocked"); }
      else { btn.removeAttribute("data-block"); btn.classList.remove("is-blocked"); }
    };

    var applySelection = function (variant) {
      if (!variant) {
        pdpAddBtn.removeAttribute("data-variant");
        setBtnBlocked(pdpAddBtn, "select-option");
        setBtnBlocked(pdpBuyBtn, "select-option");
        if (pdpBuyBtn) pdpBuyBtn.removeAttribute("data-variant");
        if (stockTitleEl) stockTitleEl.textContent = "Select an option";
        if (stockSubEl) stockSubEl.textContent = "";
        if (tierBoxEl) tierBoxEl.innerHTML = "";
        if (heroImg && defaultHeroSrc) heroImg.src = defaultHeroSrc;
        if (typeof setQtyMax === "function") setQtyMax(null);
        return;
      }

      pdpAddBtn.setAttribute("data-variant", variant.id);
      if (priceEl) priceEl.textContent = "₹" + Math.round(variant.price).toLocaleString("en-IN");
      var inStock = variant.stock > 0;
      setBtnBlocked(pdpAddBtn, inStock ? null : "out-of-stock");
      if (pdpBuyBtn) { pdpBuyBtn.setAttribute("data-variant", variant.id); setBtnBlocked(pdpBuyBtn, inStock ? null : "out-of-stock"); }
      if (stockTitleEl) stockTitleEl.textContent = inStock ? variant.stock + " in stock" : "Out of Stock";
      if (stockSubEl) stockSubEl.textContent = inStock ? "Ready to ship" : "Currently unavailable";
      if (stockCardEl) stockCardEl.style.background = inStock ? "var(--green-bg)" : "var(--red-bg)";
      if (heroImg && variant.image) heroImg.src = variant.image;
      else if (heroImg && defaultHeroSrc) heroImg.src = defaultHeroSrc;
      renderTierBox(variant);
      if (typeof setQtyMax === "function") setQtyMax(inStock ? variant.stock : 0);
    };

    var variantSelectEl = document.querySelector("[data-variant-select]");
    if (variantSelectEl) {
      variantSelectEl.addEventListener("change", function () {
        var id = variantSelectEl.value;
        var variant = pdpData.variants.find(function (v) { return v.id === id; });
        applySelection(variant || null);
      });
    }

    applySelection(null);
  }

  // ---------------- Cart page ----------------
  var cartPage = document.getElementById("cart-page");
  if (cartPage) {
    var loggedOutEl = cartPage.querySelector("[data-cart-logged-out]");
    var loggedInEl = cartPage.querySelector("[data-cart-logged-in]");
    var emptyEl = cartPage.querySelector("[data-cart-empty]");
    var layoutEl = cartPage.querySelector("[data-cart-layout]");
    var itemsEl = cartPage.querySelector("[data-cart-items]");

    var cartItemHTML = function (item) {
      var p = item.product;
      var img = (p.images && p.images[0])
        ? '<img src="' + p.images[0] + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:11px;" />'
        : '<span>' + p.brand + '<br>' + p.name + '</span>';
      return '<div class="cart-item" data-cart-item data-product="' + p.id + '" data-variant="' + (item.variant ? item.variant.id : '') + '">' +
        '<div class="cart-item-thumb">' + img + '</div>' +
        '<div style="flex:1;min-width:0;">' +
          '<div class="cart-item-name">' + p.name + '</div>' +
          '<div class="cart-item-meta">' + p.brand + (item.variant ? ' · ' + item.variant.label : '') + '</div>' +
          '<div class="cart-item-bottom">' +
            '<div class="qty-stepper"><button type="button" data-qty="-1">−</button><span>' + item.quantity + '</span><button type="button" data-qty="1">+</button></div>' +
            '<button type="button" class="cart-item-remove" data-cart-remove>' +
              '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg> Remove' +
            '</button>' +
            '<div class="cart-item-price">' + fmtINR(item.line_total) + '</div>' +
          '</div>' +
        '</div>' +
      '</div>';
    };

    var renderCart = function (data) {
      var items = data.items || [];
      var totals = data.totals || { sub: 0, disc: 0, gst: 0, deliv: 0, total: 0 };

      emptyEl.style.display = items.length ? "none" : "";
      layoutEl.style.display = items.length ? "" : "none";

      if (items.length) {
        itemsEl.innerHTML = items.map(cartItemHTML).join("");
        cartPage.querySelector("[data-cart-sub]").textContent = fmtINR(totals.sub);
        cartPage.querySelector("[data-cart-gst]").textContent = fmtINR(totals.gst);
        cartPage.querySelector("[data-cart-deliv]").textContent = totals.deliv === 0 ? "Free" : fmtINR(totals.deliv);
        var cartPackagingRow = cartPage.querySelector("[data-cart-packaging-row]");
        if (totals.packaging > 0) {
          cartPackagingRow.style.display = "";
          cartPage.querySelector("[data-cart-packaging]").textContent = fmtINR(totals.packaging);
        } else {
          cartPackagingRow.style.display = "none";
        }
        cartPage.querySelector("[data-cart-total]").textContent = fmtINR(totals.total);
        var discRow = cartPage.querySelector("[data-cart-disc-row]");
        if (totals.disc > 0) {
          discRow.style.display = "";
          cartPage.querySelector("[data-cart-disc]").textContent = "-" + fmtINR(totals.disc);
        } else {
          discRow.style.display = "none";
        }
      }
      refreshCartBadge();
    };

    var loadCart = function () {
      apiFetch("/cart").then(renderCart).catch(function (err) { showToast(err.message); });
    };

    var mutate = function (path, options) {
      apiFetch(path, options).then(renderCart).catch(function (err) { showToast(err.message); });
    };

    if (!isLoggedIn()) {
      loggedOutEl.style.display = "";
      cartPage.querySelector("[data-cart-login-btn]").addEventListener("click", function () {
        openLoginModal(function () { window.location.reload(); });
      });
    } else {
      loggedInEl.style.display = "";
      loadCart();
    }

    itemsEl.addEventListener("click", function (e) {
      var row = e.target.closest("[data-cart-item]");
      if (!row) return;
      var productCode = row.getAttribute("data-product");
      var variantCode = row.getAttribute("data-variant") || "";
      var qs = variantCode ? "?variant=" + encodeURIComponent(variantCode) : "";

      var qtyBtn = e.target.closest("[data-qty]");
      if (qtyBtn) {
        var delta = parseInt(qtyBtn.getAttribute("data-qty"), 10);
        var currentQty = parseInt(row.querySelector(".qty-stepper span").textContent, 10);
        var next = currentQty + delta;
        if (next < 1) return;
        mutate("/cart/items/" + encodeURIComponent(productCode) + qs, { method: "PATCH", body: JSON.stringify({ quantity: next }) });
        return;
      }

      if (e.target.closest("[data-cart-remove]")) {
        mutate("/cart/items/" + encodeURIComponent(productCode) + qs, { method: "DELETE" });
      }
    });

    cartPage.querySelector("[data-cart-coupon-apply]").addEventListener("click", function () {
      var input = cartPage.querySelector("[data-cart-coupon-input]");
      var msgEl = cartPage.querySelector("[data-cart-coupon-msg]");
      var code = input.value.trim();
      if (!code) return;

      apiFetch("/coupons/apply", { method: "POST", body: JSON.stringify({ code: code }) })
        .then(function (data) {
          msgEl.style.display = "";
          msgEl.style.color = "var(--green-dark)";
          msgEl.textContent = data.message;
          loadCart();
        })
        .catch(function (err) {
          msgEl.style.display = "";
          msgEl.style.color = "#EF4444";
          msgEl.textContent = err.message;
        });
    });
  }

  // ---------------- Checkout page ----------------
  var checkoutPage = document.getElementById("checkout-page");
  if (checkoutPage) {
    var coLoggedOut = checkoutPage.querySelector("[data-checkout-logged-out]");
    var coLoggedIn = checkoutPage.querySelector("[data-checkout-logged-in]");
    var coAddresses = checkoutPage.querySelector("[data-checkout-addresses]");
    var coAddForm = checkoutPage.querySelector("[data-checkout-add-address-form]");
    var coState = { addresses: [], selectedAddressId: null, shipping: "standard", payment: "upi", totals: null, shippingOptions: null };

    var addressCardHTML = function (a) {
      var active = a.id === coState.selectedAddressId;
      return '<div class="addr-card' + (active ? ' active' : '') + '" data-address-id="' + a.id + '">' +
        '<div class="addr-radio' + (active ? ' active' : '') + '"></div>' +
        '<div style="flex:1;">' +
          '<div class="addr-name-row"><span style="font-weight:700;font-size:14px;">' + a.name + '</span><span class="badge badge-orange">' + a.label + '</span></div>' +
          '<div class="addr-line">' + a.line + ', ' + a.city + ', ' + a.state + ' ' + a.pincode + '</div>' +
        '</div>' +
      '</div>';
    };

    var renderAddresses = function () {
      coAddresses.innerHTML = coState.addresses.map(addressCardHTML).join("") +
        '<button type="button" class="addr-add-btn" data-checkout-show-add-form>' +
          '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Add New Address' +
        '</button>';
    };

    var renderSummary = function () {
      if (!coState.totals) return;
      var t = coState.totals;
      checkoutPage.querySelector("[data-co-sub]").textContent = fmtINR(t.sub);
      checkoutPage.querySelector("[data-co-gst]").textContent = fmtINR(t.gst);
      checkoutPage.querySelector("[data-co-deliv]").textContent = t.deliv === 0 ? "Free" : fmtINR(t.deliv);
      var packagingRow = checkoutPage.querySelector("[data-co-packaging-row]");
      if (t.packaging > 0) {
        packagingRow.style.display = "";
        checkoutPage.querySelector("[data-co-packaging]").textContent = fmtINR(t.packaging);
      } else {
        packagingRow.style.display = "none";
      }
      var codRow = checkoutPage.querySelector("[data-co-cod-row]");
      if (t.codFee > 0) {
        codRow.style.display = "";
        checkoutPage.querySelector("[data-co-cod]").textContent = fmtINR(t.codFee);
      } else {
        codRow.style.display = "none";
      }
      checkoutPage.querySelector("[data-co-total]").textContent = fmtINR(t.total);
      var discRow = checkoutPage.querySelector("[data-co-disc-row]");
      if (t.disc > 0) {
        discRow.style.display = "";
        checkoutPage.querySelector("[data-co-disc]").textContent = "-" + fmtINR(t.disc);
      } else {
        discRow.style.display = "none";
      }
    };

    var etaLabel = function (days) {
      if (!days) return "";
      return days <= 1 ? "Next-day delivery" : days + "–" + (days + 1) + " business days";
    };

    var renderShipOptions = function () {
      var opts = coState.shippingOptions;
      if (!opts) return false;
      var forcedSwitch = false;
      ["standard", "express"].forEach(function (method) {
        var o = opts[method];
        var card = checkoutPage.querySelector('[data-ship-option="' + method + '"]');
        var priceEl = checkoutPage.querySelector('[data-ship-price="' + method + '"]');
        var etaEl = checkoutPage.querySelector('[data-ship-eta="' + method + '"]');
        if (!o || !o.available) {
          if (card) { card.style.display = "none"; }
          if (coState.shipping === method) {
            coState.shipping = "standard";
            forcedSwitch = true;
            var standardCard = checkoutPage.querySelector('[data-ship-option="standard"]');
            if (standardCard) {
              checkoutPage.querySelectorAll("[data-ship-option]").forEach(function (r) { r.querySelector(".addr-radio").classList.toggle("active", r === standardCard); });
            }
          }
          return;
        }
        if (card) card.style.display = "";
        if (priceEl) priceEl.textContent = o.charge === 0 ? "Free" : fmtINR(o.charge);
        if (priceEl) priceEl.style.color = o.charge === 0 ? "var(--green-dark)" : "";
        if (etaEl) etaEl.textContent = etaLabel(o.etaDays);
      });
      return forcedSwitch;
    };

    var loadCheckout = function () {
      apiFetch("/addresses").then(function (res) {
        var addresses = res.data || res;
        coState.addresses = addresses;
        if (addresses.length) coState.selectedAddressId = addresses.find(function (a) { return a.is_default; }) ? addresses.find(function (a) { return a.is_default; }).id : addresses[0].id;
        renderAddresses();
        return fetchCartTotals();
      }).catch(function (err) { showToast(err.message); });
    };

    var cartQuery = function () {
      var params = "?payment_method=" + coState.payment + "&shipping_method=" + coState.shipping;
      if (coState.selectedAddressId) params += "&address_id=" + coState.selectedAddressId;
      return params;
    };

    var fetchCartTotals = function () {
      return apiFetch("/cart" + cartQuery()).then(function (cart) {
        if (!cart.items || !cart.items.length) {
          window.location.href = "/cart";
          return;
        }
        coState.totals = cart.totals;
        coState.shippingOptions = cart.shippingOptions || null;
        var forcedSwitch = renderShipOptions();
        renderSummary();
        if (forcedSwitch) return fetchCartTotals();
      });
    };

    var refreshTotals = function () {
      fetchCartTotals().catch(function () {});
    };

    if (!isLoggedIn()) {
      coLoggedOut.style.display = "";
      checkoutPage.querySelector("[data-checkout-login-btn]").addEventListener("click", function () {
        openLoginModal(function () { window.location.reload(); });
      });
    } else {
      coLoggedIn.style.display = "";
      loadCheckout();
    }

    coAddresses.addEventListener("click", function (e) {
      if (e.target.closest("[data-checkout-show-add-form]")) {
        showAddAddressForm();
        return;
      }
      var card = e.target.closest("[data-address-id]");
      if (card) {
        coState.selectedAddressId = parseInt(card.getAttribute("data-address-id"), 10);
        renderAddresses();
        refreshTotals();
      }
    });

    checkoutPage.querySelector("[data-checkout-cancel-add-form]").addEventListener("click", function () {
      coAddForm.style.display = "none";
    });

    var showAddAddressForm = function () {
      coAddForm.style.display = "";
      coAddForm.scrollIntoView({ behavior: "smooth", block: "center" });
    };

    var coUseLocationBtn = checkoutPage.querySelector("[data-checkout-use-location]");
    if (coUseLocationBtn) {
      coUseLocationBtn.addEventListener("click", function () {
        var label = checkoutPage.querySelector("[data-checkout-use-location-label]");
        if (!navigator.geolocation) {
          showToast("Location isn't supported in this browser");
          return;
        }
        coUseLocationBtn.disabled = true;
        label.textContent = "Getting your location…";
        navigator.geolocation.getCurrentPosition(
          function (pos) {
            var lat = pos.coords.latitude, lon = pos.coords.longitude;
            fetch("https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" + lat + "&lon=" + lon, {
              headers: { "Accept-Language": "en" },
            })
              .then(function (res) { return res.json(); })
              .then(function (data) {
                coUseLocationBtn.disabled = false;
                label.textContent = "Use Current Location";
                var a = data.address || {};
                var line = [a.house_number, a.road].filter(Boolean).join(" ") || a.suburb || a.neighbourhood || "";
                var city = a.city || a.town || a.village || a.suburb || "";
                var state = a.state || "";
                var pincode = a.postcode || "";
                if (line) coAddForm.querySelector("[name=line]").value = line;
                if (city) coAddForm.querySelector("[name=city]").value = city;
                if (pincode) coAddForm.querySelector("[name=pincode]").value = pincode;
                var stateSelect = coAddForm.querySelector("[name=state]");
                if (state) {
                  var matched = Array.prototype.some.call(stateSelect.options, function (o) { return o.value === state; });
                  if (!matched) {
                    var opt = document.createElement("option");
                    opt.value = state;
                    opt.textContent = state;
                    stateSelect.insertBefore(opt, stateSelect.options[1] || null);
                  }
                  stateSelect.value = state;
                }
                if (!line && !city && !state) {
                  showToast("Could not determine your address — enter it manually");
                } else {
                  showToast("Address filled from your location — check and edit if needed");
                }
              })
              .catch(function () {
                coUseLocationBtn.disabled = false;
                label.textContent = "Use Current Location";
                showToast("Could not look up your address — enter it manually");
              });
          },
          function () {
            coUseLocationBtn.disabled = false;
            label.textContent = "Use Current Location";
            showToast("Location permission denied");
          },
          { enableHighAccuracy: false, timeout: 12000 }
        );
      });
    }

    checkoutPage.querySelector("[data-checkout-save-address]").addEventListener("click", function () {
      var btn = this;
      var payload = {
        label: coAddForm.querySelector("[name=label]").value || "Home",
        name: coAddForm.querySelector("[name=name]").value.trim(),
        line: coAddForm.querySelector("[name=line]").value.trim(),
        city: coAddForm.querySelector("[name=city]").value.trim(),
        state: coAddForm.querySelector("[name=state]").value.trim(),
        pincode: coAddForm.querySelector("[name=pincode]").value.trim(),
        gst_number: coAddForm.querySelector("[name=gst_number]").value.trim().toUpperCase() || undefined,
        email: coAddForm.querySelector("[name=email]").value.trim() || undefined,
        is_default: coState.addresses.length === 0,
      };
      if (!payload.name || !payload.line || !payload.city || !payload.state || !payload.pincode) {
        showToast("Fill in all address fields");
        return;
      }
      btn.disabled = true;
      apiFetch("/addresses", { method: "POST", body: JSON.stringify(payload) })
        .then(function (res) {
          btn.disabled = false;
          var address = res.data || res;
          coState.addresses.push(address);
          coState.selectedAddressId = address.id;
          coAddForm.style.display = "none";
          Array.prototype.forEach.call(coAddForm.querySelectorAll("input"), function (i) { i.value = ""; });
          coAddForm.querySelector("[name=state]").value = "";
          renderAddresses();
          refreshTotals();
        })
        .catch(function (err) {
          btn.disabled = false;
          showToast(err.message);
        });
    });

    document.querySelectorAll("[data-ship-option]").forEach(function (row) {
      row.addEventListener("click", function () {
        var next = row.getAttribute("data-ship-option");
        if (next === coState.shipping) return;
        coState.shipping = next;
        document.querySelectorAll("[data-ship-option]").forEach(function (r) { r.querySelector(".addr-radio").classList.toggle("active", r === row); });
        refreshTotals();
      });
    });

    document.querySelectorAll("[data-pay-option]").forEach(function (row) {
      row.addEventListener("click", function () {
        var next = row.getAttribute("data-pay-option");
        if (next === coState.payment) return;
        coState.payment = next;
        document.querySelectorAll("[data-pay-option]").forEach(function (r) { r.querySelector(".addr-radio").classList.toggle("active", r === row); });
        refreshTotals();
      });
    });

    var placeOrderBtn = checkoutPage.querySelector("[data-place-order]");
    if (placeOrderBtn) {
      var resetPlaceOrderBtn = function () {
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = "Place Order";
      };

      var finalizeOrder = function (couponCode, extra) {
        var payload = Object.assign(
          { address_id: coState.selectedAddressId, shipping_method: coState.shipping, payment_method: coState.payment },
          extra || {}
        );
        if (couponCode) payload.coupon_code = couponCode;
        return apiFetch("/orders", { method: "POST", body: JSON.stringify(payload) }).then(function (res) {
          var order = res.data || res;
          window.location.href = "/order-confirmation/" + order.no;
        });
      };

      placeOrderBtn.addEventListener("click", function () {
        if (!coState.selectedAddressId) {
          showToast("Save a delivery address first");
          showAddAddressForm();
          return;
        }
        var couponInput = checkoutPage.querySelector("[data-co-coupon-input]");
        var couponCode = couponInput && couponInput.value.trim() ? couponInput.value.trim() : undefined;

        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = "Placing order…";

        if (coState.payment === "cod") {
          finalizeOrder(couponCode).catch(function (err) {
            resetPlaceOrderBtn();
            showToast(err.message);
          });
          return;
        }

        var gatewayPayload = { address_id: coState.selectedAddressId, shipping_method: coState.shipping };
        if (couponCode) gatewayPayload.coupon_code = couponCode;

        apiFetch("/checkout/gateway-order", { method: "POST", body: JSON.stringify(gatewayPayload) })
          .then(function (gw) {
            if (typeof Razorpay === "undefined") {
              throw new Error("Payment gateway failed to load. Please refresh and try again.");
            }
            var rzp = new Razorpay({
              key: gw.key_id,
              amount: gw.amount,
              currency: gw.currency,
              order_id: gw.gateway_order_id,
              name: "Hardware Centers",
              description: "Order payment",
              theme: { color: "#F97316" },
              handler: function (response) {
                finalizeOrder(couponCode, {
                  gateway_order_id: response.razorpay_order_id,
                  gateway_payment_id: response.razorpay_payment_id,
                  gateway_signature: response.razorpay_signature,
                }).catch(function (err) {
                  resetPlaceOrderBtn();
                  showToast(err.message);
                });
              },
              modal: { ondismiss: resetPlaceOrderBtn },
            });
            rzp.on("payment.failed", function () {
              resetPlaceOrderBtn();
              showToast("Payment failed. Please try again.");
            });
            rzp.open();
          })
          .catch(function (err) {
            resetPlaceOrderBtn();
            showToast(err.message);
          });
      });
    }
  }

  // ---------------- Order confirmation ----------------
  var confirmationPage = document.getElementById("order-confirmation-page");
  if (confirmationPage && isLoggedIn()) {
    var orderNo = confirmationPage.getAttribute("data-order-no");
    apiFetch("/orders/" + encodeURIComponent(orderNo))
      .then(function (res) {
        var o = res.data || res;
        var detailsEl = confirmationPage.querySelector("[data-order-details]");
        detailsEl.style.display = "";
        detailsEl.style.padding = "18px 20px";
        detailsEl.innerHTML =
          o.items.map(function (item) {
            return '<div style="display:flex;justify-content:space-between;font-size:13px;padding:8px 0;border-bottom:1px solid var(--border-soft);">' +
              '<span>' + item.name + (item.variant ? ' (' + item.variant + ')' : '') + ' &times;' + item.q + '</span></div>';
          }).join("") +
          '<div style="display:flex;justify-content:space-between;font-size:14px;font-weight:800;padding-top:12px;">' +
            '<span>Total Paid</span><span>' + fmtINR(o.total) + '</span>' +
          '</div>';
        refreshCartBadge();
      })
      .catch(function () {});
  }

  // ---------------- My Orders (real, logged-in customer) ----------------
  var myOrdersRealList = document.querySelector("[data-my-orders-real-list]");
  if (myOrdersRealList) {
    if (isLoggedIn()) {
      document.querySelector("[data-my-orders-real]").style.display = "";
      document.querySelector("[data-my-orders-guest]").style.display = "none";
      apiFetch("/orders").then(function (orders) {
        var list = orders.data || orders;
        myOrdersRealList.innerHTML = list.length
          ? list.map(function (o) {
              var badgeColor = o.status === "delivered" ? { c: "#16A34A", bg: "#F0FDF4" } : (o.status === "cancelled" || o.status === "returned") ? { c: "#EF4444", bg: "#FEF2F2" } : { c: "#EA580C", bg: "#FFF7ED" };
              return '<div class="order-card">' +
                '<div class="order-top"><span class="order-no">' + o.no + '</span><span class="badge" style="color:' + badgeColor.c + ';background:' + badgeColor.bg + ';">' + o.stage + '</span></div>' +
                '<div class="order-date">' + o.date + '</div>' +
                '<div class="order-item"><div class="thumb"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.6"><path d="M6 2h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></div>' +
                '<div><div class="title">' + (o.items[0] ? o.items[0].name + (o.items.length > 1 ? ' + ' + (o.items.length - 1) + ' more' : '') : 'Order') + '</div><div class="sub">' + fmtINR(o.total) + ' total</div></div></div>' +
                '</div>';
            }).join("")
          : '<p style="text-align:center;color:var(--muted);padding:30px 0;">You haven\'t placed any orders yet.</p>';
      }).catch(function (err) { showToast(err.message); });

      document.querySelector("[data-my-orders-login-toggle]")?.addEventListener("click", function () {
        document.querySelector("[data-my-orders-real]").style.display = "none";
        document.querySelector("[data-my-orders-guest]").style.display = "";
      });
    } else {
      document.querySelector("[data-my-orders-login-btn]")?.addEventListener("click", function () {
        openLoginModal(function () { window.location.reload(); });
      });
    }
  }
})();
