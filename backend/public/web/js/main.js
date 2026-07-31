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
    return (
      '<a class="prod-card" href="/product/' + p.id + '" style="width:100%;" data-brand="' + p.brand + '">' +
      '<div class="prod-thumb" style="background:linear-gradient(135deg,#F8FAFC,#EEF1F4);">' +
      '<span>' + p.brand + '<br>' + p.name + '</span>' + tagHTML +
      '<button class="prod-wish" data-wish-id="' + p.id + '" aria-label="Save"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><path d="M12 21s-7.5-4.6-10-9.1C.4 8.8 1.6 5.5 4.7 5c2-.3 3.6.8 4.3 2 .7-1.2 2.3-2.3 4.3-2 3.1.5 4.3 3.8 2.7 6.9C19.5 16.4 12 21 12 21Z"/></svg></button>' +
      '</div><div class="prod-body">' +
      '<div class="prod-brand">' + p.brand + '</div>' +
      '<div class="prod-name">' + p.name + '</div>' +
      '<div class="prod-rating"><svg width="12" height="12" viewBox="0 0 24 24" fill="#F59E0B" stroke="none" stroke-width="0"><path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z"/></svg> ' + Number(p.rating).toFixed(1) + ' <span class="rc">(' + Number(p.reviews).toLocaleString('en-IN') + ')</span></div>' +
      '<div class="prod-price-row"><div><span class="prod-price">' + fmtINR(p.price) + '</span>' + mrpHTML + '</div>' +
      '<button class="prod-add" data-add-to-cart aria-label="Add to cart"><svg width="15" height="15" viewBox="0 0 24 24" fill="#fff" stroke="none" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg></button></div>' +
      '</div></a>'
    );
  }

  var searchResults = document.getElementById("search-results");
  var searchInput = document.querySelector("[data-search-input]");
  var brandChecks = document.querySelectorAll("[data-brand-filter]");

  if (searchResults && searchInput) {
    var searchDebounce = null;
    var urlCategory = new URLSearchParams(window.location.search).get("category");
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
          if (titleEl) titleEl.textContent = q ? ('Results for "' + q + '"') : (urlCategory ? "Category Results" : "Trending Products");
          searchResults.innerHTML = items.length
            ? items.map(productCardHTML).join("")
            : '<p style="color:var(--muted);grid-column:1/-1;">No products matched your search.</p>';
          bindWishButtons(searchResults);
        })
        .catch(function () {
          showToast("Could not load search results. Try again.");
        });
    };

    if (urlCategory) runSearch();
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
              : '<svg width="15" height="15" viewBox="0 0 24 24" fill="#fff" stroke="none" stroke-width="0"><path d="M20 6 9 17l-5-5"/></svg>';
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

  // ---------------- Wishlist (persisted to localStorage) ----------------
  var WISHLIST_KEY = "hc_web_wishlist";
  function loadWishlist() {
    try { return JSON.parse(localStorage.getItem(WISHLIST_KEY) || "{}"); } catch (e) { return {}; }
  }
  function saveWishlist(w) {
    try { localStorage.setItem(WISHLIST_KEY, JSON.stringify(w)); } catch (e) { /* ignore */ }
  }
  function paintWish(btn, active) {
    btn.classList.toggle("active", active);
    var svg = btn.querySelector("svg");
    if (svg) {
      svg.setAttribute("fill", active ? "#F97316" : "none");
      svg.setAttribute("stroke", active ? "#F97316" : "#6B7280");
    }
  }
  function bindWishButtons(root) {
    var wishlist = loadWishlist();
    (root || document).querySelectorAll(".prod-wish").forEach(function (btn) {
      var id = btn.getAttribute("data-wish-id");
      if (id) paintWish(btn, !!wishlist[id]);
      if (btn.dataset.wishBound) return;
      btn.dataset.wishBound = "1";
      btn.addEventListener("click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var pid = btn.getAttribute("data-wish-id");
        var w = loadWishlist();
        if (!pid) {
          paintWish(btn, !btn.classList.contains("active"));
          return;
        }
        if (w[pid]) delete w[pid]; else w[pid] = true;
        saveWishlist(w);
        paintWish(btn, !!w[pid]);
      });
    });
  }
  bindWishButtons(document);

  // ---------------- Add to cart -> nudge to download the app ----------------
  document.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-add-to-cart]");
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    showToast("Download the Hardware Centers app to complete your purchase.");
  });

  // ---------------- Product detail page ----------------
  var pdpWishBtn = document.querySelector("[data-pdp-wish]");
  if (pdpWishBtn) {
    var wishlist = loadWishlist();
    var pid = pdpWishBtn.getAttribute("data-pdp-wish");
    var paintPdpWish = function (active) {
      pdpWishBtn.classList.toggle("btn-primary", active);
      pdpWishBtn.classList.toggle("btn-outline", !active);
      pdpWishBtn.lastChild.textContent = active ? "Saved" : "Save";
    };
    paintPdpWish(!!wishlist[pid]);
    pdpWishBtn.addEventListener("click", function () {
      var w = loadWishlist();
      if (w[pid]) delete w[pid]; else w[pid] = true;
      saveWishlist(w);
      paintPdpWish(!!w[pid]);
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

  document.querySelectorAll("[data-variant-option]").forEach(function (chip) {
    chip.addEventListener("click", function () {
      var key = chip.getAttribute("data-variant-option").split(":")[0];
      document.querySelectorAll('[data-variant-option^="' + key + ':"]').forEach(function (c) { c.classList.remove("active"); });
      chip.classList.add("active");
    });
  });
})();
