<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Hardware Centers — Admin Console</title>

<link rel="stylesheet" href="{{ asset('vendor/admin.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/admin-brand-orange.css') }}?v={{ @filemtime(public_path('vendor/admin-brand-orange.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
<script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
<script src="{{ asset('vendor/lucide.min.js') }}"></script>

<style>
  [x-cloak] { display:none !important; }
</style>
</head>
<body class="bg-ink-50 dark:bg-ink-950 text-ink-800 dark:text-ink-100 antialiased">

<div id="app" class="min-h-screen flex"></div>
<div id="boot-loading" class="fixed inset-0 z-[200] bg-ink-50 flex items-center justify-center">
  <div class="text-center">
    <img src="{{ asset('img/brand-icon.png') }}" alt="Hardware Centers" class="w-10 h-10 rounded-2xl mx-auto shadow-sm animate-pulse object-cover">
    <p class="text-sm text-ink-400 mt-3">Loading marketplace data…</p>
  </div>
</div>

<script>
window.ADMIN_USER = @json(['name' => $user->name, 'email' => $user->email, 'role' => $user->getRoleNames()->first() ?? 'Admin']);
</script>

<script>
/* ============================================================
   HARDWARE CENTRE — ADMIN CONSOLE (live data)
   1. App State & Utilities   2. API client + data store
   3. Layout Shell            4. Section Renderers
   5. Charts                  6. Init
   ============================================================ */

/* ---------------- 1. APP STATE & UTILITIES ---------------- */
const AppState = { darkMode: false, sidebarCollapsed: false, mobileSidebarOpen: false, currentSection: 'dashboard' };

function fmtINR(num, opts={}) {
  num = Number(num) || 0;
  const { compact=false, decimals=0 } = opts;
  if (compact) {
    if (Math.abs(num) >= 1e7) return '₹' + (num/1e7).toFixed(2).replace(/\.00$/,'') + ' Cr';
    if (Math.abs(num) >= 1e5) return '₹' + (num/1e5).toFixed(2).replace(/\.00$/,'') + ' L';
    if (Math.abs(num) >= 1e3) return '₹' + (num/1e3).toFixed(1).replace(/\.0$/,'') + 'K';
  }
  return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}
function fmtNum(num) { return (Number(num)||0).toLocaleString('en-IN'); }
function el(tag, cls, html) { const e = document.createElement(tag); if (cls) e.className = cls; if (html !== undefined) e.innerHTML = html; return e; }
function icon(name, cls='w-4 h-4') { return `<i data-lucide="${name}" class="${cls}"></i>`; }
function refreshIcons() { if (window.lucide) lucide.createIcons(); }
function randBetween(min, max) { return Math.floor(Math.random()*(max-min+1))+min; }

function toast(message, type='success') {
  const wrap = document.getElementById('toast-wrap');
  const colors = { success: 'bg-emerald-600', error: 'bg-rose-600', info: 'bg-brand-600', warn: 'bg-amber-500' };
  const icons = { success:'check-circle', error:'x-circle', info:'info', warn:'alert-triangle' };
  const t = el('div', `${colors[type]} text-white px-4 py-3 rounded-xl shadow-popover flex items-center gap-2.5 animate-slide-up min-w-[260px] max-w-sm`);
  t.innerHTML = `${icon(icons[type], 'w-4.5 h-4.5 flex-shrink-0')}<span class="text-sm font-medium leading-snug">${message}</span>`;
  wrap.appendChild(t);
  refreshIcons();
  setTimeout(()=>{ t.style.transition='opacity .25s, transform .25s'; t.style.opacity='0'; t.style.transform='translateX(8px)'; setTimeout(()=>t.remove(), 260); }, 3200);
}

function statusBadge(status) {
  const map = {
    'Active': 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
    'Inactive': 'bg-ink-100 text-ink-600 ring-ink-200 dark:bg-ink-800 dark:text-ink-400 dark:ring-ink-700',
    'Pending': 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
    'Approved': 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
    'Rejected': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Suspended': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Blocked': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Confirmed': 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
    'Packed': 'bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/20',
    'Shipped': 'bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20',
    'Delivered': 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
    'Cancelled': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Out for Delivery': 'bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20',
    'Returned': 'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-500/20',
    'Paid': 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
    'Unpaid': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Failed': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Refunded': 'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-500/20',
    'In Stock': 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
    'Low Stock': 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
    'Out of Stock': 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
    'Expired': 'bg-ink-100 text-ink-600 ring-ink-200 dark:bg-ink-800 dark:text-ink-400 dark:ring-ink-700',
    'Abandoned': 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
    'Purchased': 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
  };
  const cls = map[status] || 'bg-ink-100 text-ink-600 ring-ink-200 dark:bg-ink-800 dark:text-ink-300 dark:ring-ink-700';
  return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset ${cls}"><span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>${status}</span>`;
}

function progressBar(pct, color='brand') {
  const colors = { brand:'bg-brand-500', emerald:'bg-emerald-500', amber:'bg-amber-500', rose:'bg-rose-500' };
  return `<div class="w-full h-1.5 rounded-full bg-ink-100 dark:bg-ink-800 overflow-hidden"><div class="h-full ${colors[color]} rounded-full" style="width:${Math.min(100,Math.max(0,pct))}%"></div></div>`;
}

function avatarChip(name, size='w-9 h-9') {
  const colors = ['bg-brand-500','bg-violet-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-sky-500','bg-indigo-500'];
  const initials = (name||'?').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();
  const c = colors[(name||'').length % colors.length];
  return `<div class="${size} rounded-full ${c} text-white flex items-center justify-center text-xs font-semibold flex-shrink-0">${initials}</div>`;
}

function downloadCsv(filename, rows) {
  if (!rows || !rows.length) { toast('Nothing to export', 'warn'); return; }
  const headers = Object.keys(rows[0]);
  const escape = (v) => {
    const s = v === null || v === undefined ? '' : String(v);
    return /[",\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
  };
  const csv = [headers.join(','), ...rows.map(r => headers.map(h => escape(r[h])).join(','))].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = filename;
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  URL.revokeObjectURL(url);
  toast('Exported '+rows.length+' rows to '+filename);
}

/* ---------------- 2. API CLIENT + DATA STORE ---------------- */
const DATA = {};

function csrfToken() { return document.querySelector('meta[name="csrf-token"]').getAttribute('content'); }

async function api(method, path, body) {
  const res = await fetch('/admin/api' + path, {
    method,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });
  if (res.status === 401 || res.status === 419) { window.location.href = '/admin/login'; return; }
  let data = {};
  try { data = await res.json(); } catch (e) {}
  if (!res.ok) { throw new Error(data.message || ('Request failed (' + res.status + ')')); }
  return data;
}
const unwrap = (resp) => (resp && resp.data !== undefined) ? resp.data : resp;

/**
 * Multipart requests (file uploads) can't be JSON, and PHP never
 * populates $_FILES for a real PUT/PATCH body — so a "PATCH with a file"
 * has to be sent as POST with Laravel's `_method` override field instead.
 */
async function apiForm(method, path, formData) {
  if (method !== 'POST') formData.append('_method', method);
  const res = await fetch('/admin/api' + path, {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    body: formData,
  });
  if (res.status === 401 || res.status === 419) { window.location.href = '/admin/login'; return; }
  let data = {};
  try { data = await res.json(); } catch (e) {}
  if (!res.ok) { throw new Error(data.message || ('Request failed (' + res.status + ')')); }
  return data;
}

async function refreshSellers() { DATA.sellers = unwrap(await api('GET', '/sellers?per_page=250')); }
async function refreshCustomers() { DATA.customers = unwrap(await api('GET', '/customers?per_page=250')); }
async function refreshProducts() { DATA.products = unwrap(await api('GET', '/products?per_page=250')); }
async function refreshCategories() { DATA.categories = await api('GET', '/categories'); }
async function refreshBrands() { DATA.brands = await api('GET', '/brands'); }
async function refreshOrders() { DATA.orders = unwrap(await api('GET', '/orders?per_page=250')); }
async function refreshReturns() { DATA.returns = unwrap(await api('GET', '/returns?per_page=250')); }
async function refreshCoupons() { DATA.coupons = await api('GET', '/coupons'); }
async function refreshBanners() { DATA.banners = await api('GET', '/banners'); }
async function refreshOnboardingSlides() { DATA.onboardingSlides = await api('GET', '/onboarding-slides'); }
async function refreshSettlements() { DATA.settlements = unwrap(await api('GET', '/settlements?per_page=250')); }
async function refreshWarehouseOverview() { DATA.warehouseOverview = await api('GET', '/warehouses'); }
async function refreshWarehouseInventory() { DATA.warehouseInventory = unwrap(await api('GET', '/warehouses/inventory?per_page=250')); }
async function refreshWarehouseShipments() { DATA.warehouseShipments = unwrap(await api('GET', '/warehouses/shipments?per_page=250')); }
async function refreshDispatchQueue() { DATA.dispatchQueue = unwrap(await api('GET', '/warehouses/dispatch-queue?per_page=250')); }
async function refreshCommission() { DATA.commission = await api('GET', '/commission?per_page=250'); }
async function refreshDashboard() { DATA.dashboard = await api('GET', '/dashboard'); DATA.monthlyRevenue = DATA.dashboard.monthly_revenue; DATA.activities = DATA.dashboard.activities; }
async function refreshGst() { DATA.gst = await api('GET', '/gst'); }
async function refreshShipping() { DATA.shipping = await api('GET', '/shipping'); }
async function refreshAbandonedCarts() { DATA.abandonedCarts = unwrap(await api('GET', '/abandoned-carts?per_page=250')); }
async function refreshPayments() { DATA.payments = await api('GET', '/payments'); }
async function refreshShiprocket() { DATA.shiprocket = await api('GET', '/shiprocket'); }
async function refreshNotifications() { DATA.notifications = await api('GET', '/notifications'); }
async function refreshRoles() { DATA.rolesData = await api('GET', '/roles'); }
async function refreshSettings() { DATA.settings = await api('GET', '/settings'); }

/*
 * The database's connection quota is shared and can't be raised, so booting
 * the console must not open ~20 simultaneous DB connections at once (each
 * fetch below hits a separate PHP-FPM worker with its own MySQL connection).
 * Running a handful at a time keeps peak concurrent connections low while
 * still loading everything in a few short waves instead of one big burst.
 */
async function runLimited(tasks, limit) {
  const failures = [];
  let next = 0;
  async function worker() {
    while (next < tasks.length) {
      const i = next++;
      const [name, fn] = tasks[i];
      try {
        await fn();
      } catch (e) {
        failures.push({ name, message: e?.message || 'Unknown error' });
      }
    }
  }
  await Promise.all(Array.from({ length: Math.min(limit, tasks.length) }, worker));
  return failures;
}

const LOAD_TASKS = [
  ['Dashboard', refreshDashboard], ['Sellers', refreshSellers], ['Customers', refreshCustomers],
  ['Products', refreshProducts], ['Categories', refreshCategories], ['Brands', refreshBrands],
  ['Orders', refreshOrders], ['Returns', refreshReturns], ['Coupons', refreshCoupons],
  ['Banners', refreshBanners], ['Onboarding Slides', refreshOnboardingSlides], ['Settlements', refreshSettlements], ['Commission', refreshCommission],
  ['GST', refreshGst], ['Shipping', refreshShipping], ['Payments', refreshPayments],
  ['Abandoned Carts', refreshAbandonedCarts],
  ['Shiprocket', refreshShiprocket], ['Notifications', refreshNotifications], ['Roles', refreshRoles],
  ['Settings', refreshSettings], ['Warehouse Overview', refreshWarehouseOverview],
  ['Warehouse Inventory', refreshWarehouseInventory], ['Warehouse Shipments', refreshWarehouseShipments],
  ['Dispatch Queue', refreshDispatchQueue],
];

async function loadAll() {
  const failures = await runLimited(LOAD_TASKS, 4);
  // Give every section a safe empty default so a section that failed to
  // load doesn't crash its renderer when the admin navigates to it.
  const defaults = {
    dashboard: {}, sellers: [], customers: [], products: [], categories: [], brands: [],
    orders: [], returns: [], coupons: [], banners: [], onboardingSlides: [], settlements: [],
    commission: { sellers: [], tiers: [] }, gst: {}, shipping: {}, payments: {}, shiprocket: {},
    abandonedCarts: [],
    notifications: {}, rolesData: {}, settings: {},
    warehouseOverview: { warehouses: [], dispatch_pending: 0 },
    warehouseInventory: [], warehouseShipments: [], dispatchQueue: [],
  };
  Object.keys(defaults).forEach(key => { if (DATA[key] === undefined) DATA[key] = defaults[key]; });
  return failures;
}

function showLoadFailuresBanner(failures) {
  document.getElementById('load-failures-banner')?.remove();
  const wrap = el('div', 'fixed top-4 left-1/2 -translate-x-1/2 z-[150] w-[min(640px,92vw)] bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 rounded-2xl shadow-popover p-4');
  wrap.id = 'load-failures-banner';
  wrap.innerHTML = `
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-500/20 text-rose-600 flex items-center justify-center flex-shrink-0">${icon('alert-triangle','w-4.5 h-4.5')}</div>
      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">${failures.length} section${failures.length>1?'s':''} failed to load</p>
        <ul class="mt-1.5 space-y-0.5 text-xs text-rose-600 dark:text-rose-400 max-h-32 overflow-y-auto">
          ${failures.map(f => `<li><b>${f.name}:</b> ${String(f.message).replace(/</g,'&lt;')}</li>`).join('')}
        </ul>
        <div class="flex gap-2 mt-3">
          <button id="load-failures-retry" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white">Retry</button>
          <button id="load-failures-dismiss" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-rose-200 dark:border-rose-500/30 text-rose-600 dark:text-rose-400">Dismiss</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(wrap);
  refreshIcons();
  document.getElementById('load-failures-dismiss').addEventListener('click', () => wrap.remove());
  document.getElementById('load-failures-retry').addEventListener('click', withErr(async () => {
    wrap.remove();
    const stillFailing = await loadAll();
    navigateTo(AppState.currentSection || 'dashboard');
    if (stillFailing.length) showLoadFailuresBanner(stillFailing);
    else toast('All sections loaded successfully');
  }));
}

/* ---------------- 3. LAYOUT SHELL ---------------- */
const NAV_GROUPS = [
  { items: [ { key:'dashboard', label:'Dashboard', icon:'layout-dashboard' } ] },
  { title:'Catalog', items: [
    { key:'sellers', label:'Seller Management', icon:'store' },
    { key:'customers', label:'Customer Management', icon:'users' },
    { key:'products', label:'Product Management', icon:'package' },
    { key:'categories', label:'Categories', icon:'layout-grid' },
    { key:'brands', label:'Brands', icon:'tag' },
  ]},
  { title:'Sales', items: [
    { key:'orders', label:'Orders', icon:'shopping-cart' },
    { key:'abandoned-carts', label:'Abandoned Carts', icon:'shopping-basket' },
    { key:'returns', label:'Returns & Refunds', icon:'undo-2' },
    { key:'commission', label:'Commission Management', icon:'percent' },
    { key:'settlements', label:'Settlement Management', icon:'banknote' },
    { key:'coupons', label:'Coupons & Offers', icon:'ticket-percent' },
    { key:'banners', label:'Banner Management', icon:'image' },
    { key:'onboarding', label:'Onboarding Screens', icon:'smartphone' },
  ]},
  { title:'Fulfillment', items: [
    { key:'warehouse', label:'Warehouse & FBA', icon:'warehouse' },
  ]},
  { title:'Insights', items: [
    { key:'reports', label:'Reports & Analytics', icon:'bar-chart-3' },
    { key:'gst', label:'GST & Tax', icon:'receipt' },
  ]},
  { title:'Configuration', items: [
    { key:'shipping', label:'Shipping Configuration', icon:'truck' },
    { key:'payments', label:'Payment Gateway Configuration', icon:'credit-card' },
    { key:'shiprocket', label:'Shiprocket Integration', icon:'truck' },
    { key:'notifications-config', label:'Notification Management', icon:'bell' },
    { key:'roles', label:'Roles & Permissions', icon:'shield-check' },
    { key:'settings', label:'Settings', icon:'settings' },
  ]},
];

const SECTION_TITLES = {
  dashboard: ['Dashboard', 'Overview of your marketplace performance'],
  sellers: ['Seller Management', 'Manage vendor onboarding, KYC and performance'],
  customers: ['Customer Management', 'View and manage customer accounts'],
  products: ['Product Management', 'Manage your marketplace catalog'],
  categories: ['Categories', 'Organize products into categories'],
  brands: ['Brands', 'Manage brand listings shown on the storefront'],
  orders: ['Orders', 'Track and fulfil orders across all sellers'],
  'abandoned-carts': ['Abandoned Carts', 'Customers who added a product but didn\'t check out'],
  returns: ['Returns & Refunds', 'Review and action return and refund requests'],
  commission: ['Commission Management', 'Configure marketplace commission and view earnings'],
  settlements: ['Settlement Management', 'Track payouts to sellers'],
  coupons: ['Coupons & Offers', 'Create and manage promotional discounts'],
  banners: ['Banner Management', 'Manage homepage and offer banners'],
  onboarding: ['Onboarding Screens', 'Manage the welcome slides shown the first time a customer opens the app'],
  warehouse: ['Warehouse & FBA', 'Manage warehouse stock, inbound shipments and order dispatch'],
  reports: ['Reports & Analytics', 'Deep dive into marketplace performance'],
  gst: ['GST & Tax Settings', 'Configure tax rules and HSN codes'],
  shipping: ['Shipping Configuration', 'Set up shipping zones, charges and rules'],
  payments: ['Payment Gateway Configuration', 'Manage payment integrations'],
  shiprocket: ['Shiprocket API Integration', 'Automate courier assignment, AWB generation and tracking'],
  'notifications-config': ['Notification Management', 'Configure customer and seller communication channels'],
  roles: ['Roles & Permissions', 'Control access across your admin team'],
  settings: ['Settings', 'Company, security and system configuration'],
};

function buildSidebar() {
  const wrap = el('aside', 'sidebar fixed lg:sticky top-0 left-0 h-screen z-40 flex flex-col bg-white dark:bg-ink-900 border-r border-ink-200/70 dark:border-ink-800 transition-all duration-200 -translate-x-full lg:translate-x-0 w-64');
  wrap.id = 'sidebar';
  wrap.innerHTML = `
    <div class="flex items-center gap-2.5 h-16 px-4 border-b border-ink-200/70 dark:border-ink-800 flex-shrink-0">
      <img src="{{ asset('img/brand-icon.png') }}" alt="Hardware Centers" class="w-8 h-8 rounded-lg flex-shrink-0 shadow-sm object-cover">
      <div class="sidebar-label leading-tight overflow-hidden whitespace-nowrap">
        <p class="text-sm font-bold text-ink-900 dark:text-white">Hardware Centers</p>
        <p class="text-[11px] text-ink-400 -mt-0.5">Admin Console</p>
      </div>
      <button id="sidebar-close-mobile" class="ml-auto lg:hidden p-1.5 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800">${icon('x', 'w-4.5 h-4.5')}</button>
    </div>
    <nav id="sidebar-nav" class="flex-1 overflow-y-auto py-3 px-2.5 space-y-0.5 scrollbar-none"></nav>
    <div class="border-t border-ink-200/70 dark:border-ink-800 p-2.5 flex-shrink-0">
      <button id="logout-btn" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-ink-600 dark:text-ink-300 transition-colors">
        ${icon('log-out', 'w-[18px] h-[18px] flex-shrink-0')}<span class="sidebar-label whitespace-nowrap overflow-hidden">Logout</span>
      </button>
    </div>`;
  const nav = wrap.querySelector('#sidebar-nav');
  NAV_GROUPS.forEach(group => {
    if (group.title) nav.appendChild(el('p', 'sidebar-label px-3 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-wider text-ink-400 dark:text-ink-500 whitespace-nowrap overflow-hidden', group.title));
    group.items.forEach(item => {
      const btn = el('button', 'nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-ink-600 dark:text-ink-300 transition-colors relative group');
      btn.dataset.section = item.key;
      btn.innerHTML = `${icon(item.icon, 'w-[18px] h-[18px] flex-shrink-0')}<span class="sidebar-label whitespace-nowrap overflow-hidden text-left">${item.label}</span>`;
      btn.addEventListener('click', () => navigateTo(item.key));
      nav.appendChild(btn);
    });
  });
  return wrap;
}

function buildTopbar() {
  const bar = el('header', 'sticky top-0 z-30 h-16 glass border-b border-ink-200/70 dark:border-ink-800 flex items-center gap-3 px-4 lg:px-6 flex-shrink-0');
  const initials = (window.ADMIN_USER.name || 'A').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();
  bar.innerHTML = `
    <button id="hamburger-btn" class="lg:hidden p-2 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 text-ink-600 dark:text-ink-300 flex-shrink-0">${icon('menu', 'w-5 h-5')}</button>
    <div class="relative flex-1 max-w-md hidden sm:block">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-400">${icon('search','w-[16px] h-[16px]')}</span>
      <input type="text" placeholder="Search orders, products, sellers..." class="w-full pl-9 pr-4 py-2 text-sm rounded-xl bg-ink-100/70 dark:bg-ink-800/70 border border-transparent focus:border-brand-400 focus:bg-white dark:focus:bg-ink-800 focus:ring-2 focus:ring-brand-500/15 outline-none transition-all placeholder:text-ink-400">
    </div>
    <div class="flex-1"></div>
    <div class="flex items-center gap-1.5">
      <button id="theme-toggle" class="p-2 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 text-ink-500 dark:text-ink-400 transition-colors" title="Toggle theme">${icon('sun-moon', 'w-[18px] h-[18px]')}</button>
      <div class="w-px h-6 bg-ink-200 dark:bg-ink-700 mx-1 hidden sm:block"></div>
      <div class="relative">
        <button id="profile-btn" class="flex items-center gap-2 px-1.5 py-1 rounded-xl hover:bg-ink-100 dark:hover:bg-ink-800 transition-colors">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-violet-600 text-white flex items-center justify-center text-xs font-semibold">${initials}</div>
          <div class="hidden md:block text-left">
            <p class="text-sm font-semibold leading-tight">${window.ADMIN_USER.name}</p>
            <p class="text-[11px] text-ink-400 leading-tight">${window.ADMIN_USER.role}</p>
          </div>
          ${icon('chevron-down', 'w-4 h-4 text-ink-400 hidden md:block')}
        </button>
        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-ink-900 rounded-2xl shadow-popover border border-ink-200/70 dark:border-ink-800 overflow-hidden animate-scale-in z-50 py-1.5">
          <div class="border-t border-ink-100 dark:border-ink-800 pt-1.5">
            <button id="dropdown-logout" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors">${icon('log-out','w-4 h-4')}<span>Logout</span></button>
          </div>
        </div>
      </div>
    </div>`;
  return bar;
}

function buildAppShell() {
  const app = document.getElementById('app');
  app.innerHTML = '';
  const overlay = el('div', 'fixed inset-0 bg-ink-900/40 z-30 hidden lg:hidden');
  overlay.id = 'mobile-overlay';
  app.appendChild(overlay);
  app.appendChild(buildSidebar());
  const right = el('div', 'flex-1 flex flex-col min-w-0');
  right.id = 'main-col';
  right.appendChild(buildTopbar());
  const main = el('main', 'flex-1 p-4 lg:p-6 max-w-[1600px] w-full mx-auto');
  main.id = 'main-content';
  right.appendChild(main);
  app.appendChild(right);
  const toastWrap = el('div', 'fixed bottom-5 right-5 z-[100] flex flex-col gap-2 items-end');
  toastWrap.id = 'toast-wrap';
  app.appendChild(toastWrap);
  const modalRoot = el('div', 'fixed inset-0 z-[90] hidden');
  modalRoot.id = 'modal-root';
  app.appendChild(modalRoot);
  refreshIcons();
  bindGlobalEvents();
}

async function doLogout() {
  try {
    await fetch('/admin/logout', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() } });
  } catch (e) {}
  window.location.href = '/admin/login';
}

function bindGlobalEvents() {
  document.getElementById('hamburger-btn').addEventListener('click', () => toggleMobileSidebar(true));
  document.getElementById('sidebar-close-mobile').addEventListener('click', () => toggleMobileSidebar(false));
  document.getElementById('mobile-overlay').addEventListener('click', () => toggleMobileSidebar(false));
  document.getElementById('theme-toggle').addEventListener('click', toggleDarkMode);
  document.getElementById('logout-btn').addEventListener('click', confirmLogout);
  document.getElementById('dropdown-logout').addEventListener('click', confirmLogout);
  setupDropdownToggle('profile-btn', 'profile-dropdown');
}

function setupDropdownToggle(btnId, dropId) {
  const btn = document.getElementById(btnId);
  const drop = document.getElementById(dropId);
  btn.addEventListener('click', (e) => { e.stopPropagation(); const isOpen = !drop.classList.contains('hidden'); document.querySelectorAll('[id$="-dropdown"]').forEach(d => d.classList.add('hidden')); if (!isOpen) drop.classList.remove('hidden'); });
  document.addEventListener('click', () => drop.classList.add('hidden'));
}

function toggleMobileSidebar(open) {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('mobile-overlay');
  sb.classList.toggle('-translate-x-full', !open);
  ov.classList.toggle('hidden', !open);
}

function toggleDarkMode() {
  AppState.darkMode = !AppState.darkMode;
  document.documentElement.classList.toggle('dark', AppState.darkMode);
  toast(AppState.darkMode ? 'Dark mode enabled' : 'Light mode enabled', 'info');
}

function confirmLogout() {
  openModal({
    title: 'Log out of admin console?',
    body: `<p class="text-sm text-ink-500 dark:text-ink-400">You'll need to sign in again to access the marketplace admin dashboard.</p>`,
    confirmLabel: 'Log out', confirmClass: 'bg-rose-600 hover:bg-rose-700',
    onConfirm: doLogout,
  });
}

function navigateTo(sectionKey) {
  AppState.currentSection = sectionKey;
  document.querySelectorAll('.nav-item[data-section]').forEach(b => b.classList.toggle('active', b.dataset.section === sectionKey));
  renderSection(sectionKey);
  toggleMobileSidebar(false);
  window.scrollTo(0,0);
}

function pageHeader(title, subtitle, actions='') {
  return `<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl lg:text-2xl font-bold text-ink-900 dark:text-white tracking-tight">${title}</h1><p class="text-sm text-ink-500 dark:text-ink-400 mt-0.5">${subtitle}</p></div>
    <div class="flex items-center gap-2 flex-wrap">${actions}</div>
  </div>`;
}
function btnPrimary(label, iconName='plus', id='') { return `<button ${id?`id="${id}"`:''} class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">${icon(iconName,'w-4 h-4')}${label}</button>`; }
function btnSecondary(label, iconName='', id='') { return `<button ${id?`id="${id}"`:''} class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-ink-800 hover:bg-ink-50 dark:hover:bg-ink-700 text-ink-700 dark:text-ink-200 text-sm font-medium rounded-xl border border-ink-200 dark:border-ink-700 shadow-sm transition-colors">${iconName?icon(iconName,'w-4 h-4'):''}${label}</button>`; }
function card(innerHtml, extraCls='') { return `<div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card ${extraCls}">${innerHtml}</div>`; }
function tableCard(id) { return `<div id="${id}" class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card"></div>`; }

/* ---------------- MODAL SYSTEM ---------------- */
function openModal({ title, body, confirmLabel='Confirm', cancelLabel='Cancel', confirmClass='bg-brand-600 hover:bg-brand-700', onConfirm, hideCancel=false, wide=false }) {
  const root = document.getElementById('modal-root');
  root.classList.remove('hidden');
  root.innerHTML = `
    <div class="absolute inset-0 bg-ink-900/50 backdrop-blur-sm animate-fade-in" id="modal-backdrop"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-ink-900 rounded-2xl shadow-popover w-full ${wide?'max-w-2xl':'max-w-md'} animate-scale-in max-h-[90vh] overflow-y-auto">
        <div class="px-6 pt-6 pb-2"><h3 class="text-lg font-bold text-ink-900 dark:text-white">${title}</h3></div>
        <div class="px-6 py-3">${body}</div>
        <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-ink-100 dark:border-ink-800 mt-2">
          ${hideCancel ? '' : `<button id="modal-cancel" class="px-4 py-2 text-sm font-medium text-ink-600 dark:text-ink-300 hover:bg-ink-100 dark:hover:bg-ink-800 rounded-xl transition-colors">${cancelLabel}</button>`}
          <button id="modal-confirm" class="px-4 py-2 text-sm font-medium text-white ${confirmClass} rounded-xl transition-colors shadow-sm">${confirmLabel}</button>
        </div>
      </div>
    </div>`;
  refreshIcons();
  document.getElementById('modal-backdrop').addEventListener('click', closeModal);
  document.getElementById('modal-cancel')?.addEventListener('click', closeModal);
  document.getElementById('modal-confirm').addEventListener('click', () => onConfirm?.());
}
function closeModal() { const root = document.getElementById('modal-root'); root.classList.add('hidden'); root.innerHTML = ''; }

function openDrawer(title, bodyHtml, wide=false) {
  const root = document.getElementById('modal-root');
  root.classList.remove('hidden');
  root.innerHTML = `
    <div class="absolute inset-0 bg-ink-900/40 backdrop-blur-sm animate-fade-in" id="modal-backdrop"></div>
    <div class="absolute inset-y-0 right-0 w-full ${wide?'max-w-2xl':'max-w-md'} bg-white dark:bg-ink-900 shadow-popover flex flex-col animate-slide-up" style="animation-duration:.3s">
      <div class="flex items-center justify-between px-6 h-16 border-b border-ink-100 dark:border-ink-800 flex-shrink-0">
        <h3 class="text-base font-bold text-ink-900 dark:text-white">${title}</h3>
        <button id="drawer-close" class="p-2 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800">${icon('x','w-4.5 h-4.5')}</button>
      </div>
      <div class="flex-1 overflow-y-auto px-6 py-5">${bodyHtml}</div>
    </div>`;
  refreshIcons();
  document.getElementById('modal-backdrop').addEventListener('click', closeModal);
  document.getElementById('drawer-close').addEventListener('click', closeModal);
}

function kpiCard({ label, value, sub, icon: iconName, trend, trendUp=true, accent='brand' }) {
  const accents = {
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    violet: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
    rose: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    sky: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
  };
  return card(`<div class="p-4 lg:p-5">
    <div class="flex items-center justify-between mb-3">
      <div class="w-10 h-10 rounded-xl ${accents[accent]} flex items-center justify-center">${icon(iconName,'w-5 h-5')}</div>
      ${trend ? `<span class="inline-flex items-center gap-1 text-xs font-semibold ${trendUp?'text-emerald-600':'text-rose-600'}">${icon(trendUp?'trending-up':'trending-down','w-3.5 h-3.5')}${trend}</span>` : ''}
    </div>
    <p class="text-2xl font-bold text-ink-900 dark:text-white tnum tracking-tight">${value}</p>
    <p class="text-sm text-ink-500 dark:text-ink-400 mt-1">${label}</p>
    ${sub ? `<p class="text-xs text-ink-400 mt-2">${sub}</p>` : ''}
  </div>`);
}

function formField(label, type, placeholder, value='', id='') { return `<div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">${label}</label><input ${id?`id="${id}"`:''} type="${type}" placeholder="${placeholder}" value="${value}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none"></div>`; }
function formSelect(label, options, selectedIdx=0, id='') { return `<div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">${label}</label><select ${id?`id="${id}"`:''} class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none">${options.map((o,i)=>`<option ${i===selectedIdx?'selected':''}>${o}</option>`).join('')}</select></div>`; }
function formCategorySelect(label, categories, id='') {
  const topLevel = categories.filter(c => !c.parentId);
  const groups = topLevel.map(top => {
    const subs = categories.filter(c => c.parentId === top.id);
    if (!subs.length) return `<option value="${top.name}">${top.name}</option>`;
    return `<optgroup label="${top.name}"><option value="${top.name}">${top.name} (general)</option>${subs.map(s=>`<option value="${s.name}">${s.name}</option>`).join('')}</optgroup>`;
  }).join('');
  return `<div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">${label}</label><select ${id?`id="${id}"`:''} class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none">${groups}</select></div>`;
}
function formTextarea(label, placeholder, rows=3, id='') { return `<div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">${label}</label><textarea ${id?`id="${id}"`:''} rows="${rows}" placeholder="${placeholder}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none resize-none"></textarea></div>`; }
function toggleSwitch(checked=false, id='') {
  return `<button ${id?`id="${id}"`:''} data-checked="${checked}" onclick="window.__flipToggle(this)" class="toggle-track relative w-11 h-6 rounded-full ${checked?'bg-brand-600':'bg-ink-200 dark:bg-ink-700'} flex-shrink-0">
    <span class="toggle-dot absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow" style="transform:translateX(${checked?'20px':'0'})"></span></button>`;
}
window.__flipToggle = function(btn) {
  const checked = btn.dataset.checked === 'true';
  btn.dataset.checked = String(!checked);
  btn.classList.toggle('bg-brand-600', !checked);
  btn.classList.toggle('bg-ink-200', checked);
  btn.classList.toggle('dark:bg-ink-700', checked);
  btn.querySelector('.toggle-dot').style.transform = `translateX(${!checked?'20px':'0'})`;
};
function miniStat(label, value) { return `<div class="bg-ink-50 dark:bg-ink-800/60 rounded-xl p-3"><p class="text-[11px] text-ink-400 mb-0.5">${label}</p><p class="text-base font-bold text-ink-800 dark:text-ink-100 tnum">${value}</p></div>`; }
function drawerSection(title, content) { return `<div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-2">${title}</p><div class="bg-ink-50 dark:bg-ink-800/40 rounded-xl p-3.5 space-y-2">${content}</div></div>`; }
function drawerRow(label, value) { return `<div class="flex items-center justify-between text-sm"><span class="text-ink-500 dark:text-ink-400">${label}</span><span class="font-medium text-ink-800 dark:text-ink-100 text-right">${value}</span></div>`; }
function integrationCard({ name, logo, fields, sandboxToggle=true, enabled=true, testBtnId, saveBtnId, toggleId }) {
  return card(`<div class="p-5">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-ink-100 dark:bg-ink-800 flex items-center justify-center font-bold text-ink-600 dark:text-ink-300 text-sm">${logo}</div>
        <div><p class="font-semibold text-ink-900 dark:text-white">${name}</p><p class="text-xs text-ink-400">${enabled?'Connected':'Not connected'}</p></div>
      </div>
      ${toggleSwitch(enabled, toggleId)}
    </div>
    <div class="space-y-3">${fields.map(f => f.type==='select' ? formSelect(f.label, f.options, 0, f.id) : formField(f.label, f.type||'text', f.placeholder||'', f.value||'', f.id)).join('')}</div>
    ${sandboxToggle ? `<div class="flex items-center justify-between pt-3"><span class="text-sm text-ink-600 dark:text-ink-300">Sandbox Mode</span>${toggleSwitch(true)}</div>` : ''}
    <div class="flex gap-2.5 mt-5 pt-4 border-t border-ink-100 dark:border-ink-800">
      ${btnSecondary('Test Connection','plug', testBtnId)}
      ${btnPrimary('Save Changes','check', saveBtnId)}
    </div>
  </div>`);
}

/* ---------------- GENERIC DATA TABLE ---------------- */
function renderDataTable(containerId, config) {
  const state = { page: 1, search: '', filters: {} };
  config.filters?.forEach(f => state.filters[f.key] = 'All');
  const pageSize = config.pageSize || 8;

  function getFiltered() {
    let rows = config.data;
    if (state.search) { const q = state.search.toLowerCase(); rows = rows.filter(r => (config.searchKeys||[]).some(k => String(r[k]||'').toLowerCase().includes(q))); }
    config.filters?.forEach(f => { if (state.filters[f.key] !== 'All') rows = rows.filter(r => String(r[f.key]) === state.filters[f.key]); });
    return rows;
  }

  function renderTable() {
    const container = document.getElementById(containerId);
    if (!container) return;
    const filtered = getFiltered();
    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    state.page = Math.min(state.page, totalPages);
    const pageRows = filtered.slice((state.page-1)*pageSize, state.page*pageSize);

    container.innerHTML = `
      <div class="p-5 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div><h3 class="font-semibold text-ink-900 dark:text-white">${config.title}</h3>${config.subtitle ? `<p class="text-xs text-ink-400 mt-0.5">${config.subtitle}</p>` : ''}</div>
        <div class="flex items-center gap-2">${config.actions||''}</div>
      </div>
      <div class="px-5 pb-4 flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1 max-w-xs">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-400">${icon('search','w-4 h-4')}</span>
          <input type="text" id="${containerId}-search" placeholder="Search..." value="${state.search}" class="w-full pl-9 pr-3 py-2 text-sm rounded-xl bg-ink-100/70 dark:bg-ink-800/60 border border-transparent focus:bg-white dark:focus:bg-ink-800 focus:border-brand-400 outline-none focus:ring-2 focus:ring-brand-500/15 transition-all">
        </div>
        ${(config.filters||[]).map(f => `<select data-filter-key="${f.key}" class="px-3 py-2 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 text-ink-600 dark:text-ink-300 focus-ring"><option value="All">${f.label}: All</option>${f.options.map(o => `<option value="${o}" ${state.filters[f.key]===o?'selected':''}>${o}</option>`).join('')}</select>`).join('')}
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm data-table">
          <thead><tr class="text-left text-xs text-ink-400 border-t border-ink-100 dark:border-ink-800">${config.columns.map((c,i) => `<th class="font-medium py-2.5 ${i===0?'px-5':'px-3'} whitespace-nowrap">${c.label}</th>`).join('')}</tr></thead>
          <tbody>
            ${pageRows.length === 0 ? `<tr><td colspan="${config.columns.length}" class="py-12 text-center"><div class="flex flex-col items-center gap-2 text-ink-400">${icon('search-x','w-8 h-8')}<p class="text-sm">No results match your search or filters</p></div></td></tr>` :
              pageRows.map(row => `<tr class="border-t border-ink-100 dark:border-ink-800 hover:bg-ink-50/70 dark:hover:bg-ink-800/40 ${config.rowClick ? 'cursor-pointer' : ''}" data-row-id="${row.id}">${config.columns.map((c,i) => `<td class="py-3 ${i===0?'px-5':'px-3'}">${c.render ? c.render(row) : (row[c.key] ?? '')}</td>`).join('')}</tr>`).join('')}
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-5 py-3.5 border-t border-ink-100 dark:border-ink-800">
        <p class="text-xs text-ink-400">Showing ${pageRows.length ? ((state.page-1)*pageSize+1) : 0}–${Math.min(state.page*pageSize, filtered.length)} of ${filtered.length}</p>
        <div class="flex items-center gap-1.5">
          <button id="${containerId}-prev" class="p-1.5 rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 disabled:opacity-40 disabled:cursor-not-allowed" ${state.page===1?'disabled':''}>${icon('chevron-left','w-4 h-4')}</button>
          <span class="text-xs text-ink-500 px-2 tnum">Page ${state.page} of ${totalPages}</span>
          <button id="${containerId}-next" class="p-1.5 rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 disabled:opacity-40 disabled:cursor-not-allowed" ${state.page===totalPages?'disabled':''}>${icon('chevron-right','w-4 h-4')}</button>
        </div>
      </div>`;
    refreshIcons();
    document.getElementById(`${containerId}-search`).addEventListener('input', (e) => { state.search = e.target.value; state.page=1; renderTable(); });
    document.getElementById(`${containerId}-prev`).addEventListener('click', () => { state.page--; renderTable(); });
    document.getElementById(`${containerId}-next`).addEventListener('click', () => { state.page++; renderTable(); });
    container.querySelectorAll('[data-filter-key]').forEach(sel => sel.addEventListener('change', (e) => { state.filters[sel.dataset.filterKey] = e.target.value; state.page=1; renderTable(); }));
    if (config.rowClick) container.querySelectorAll('tr[data-row-id]').forEach(tr => tr.addEventListener('click', (e) => { if (e.target.closest('[data-stop-row-click]')) return; const row = config.data.find(r => String(r.id) === tr.dataset.rowId); config.rowClick(row); }));
    config.afterRender?.(container);
  }
  renderTable();
}

/* ---------------- 4. SECTION RENDERER (router) ---------------- */
const SECTION_RENDERERS = {};

async function renderSection(key) {
  const main = document.getElementById('main-content');
  const renderer = SECTION_RENDERERS[key];
  if (!renderer) { main.innerHTML = pageHeader(SECTION_TITLES[key]?.[0] || key, 'This section is coming soon.'); return; }
  main.innerHTML = `<div class="py-24 text-center text-ink-400 text-sm">Loading…</div>`;
  try {
    await renderer(main);
    main.classList.add('animate-fade-in');
    refreshIcons();
    setTimeout(()=>main.classList.remove('animate-fade-in'), 400);
  } catch (e) {
    main.innerHTML = pageHeader(SECTION_TITLES[key]?.[0] || key, 'Failed to load data.');
    toast(e.message || 'Something went wrong', 'error');
  }
}

function withErr(fn) { return async (...args) => { try { await fn(...args); } catch (e) { toast(e.message || 'Something went wrong', 'error'); } }; }

@include('admin.console-sections')

/* ---------------- 6. INIT ---------------- */
document.addEventListener('DOMContentLoaded', async () => {
  let failures = [];
  try {
    failures = await loadAll();
  } catch (e) {
    // Only a total failure (e.g. network unreachable) lands here -- loadAll()
    // itself catches per-section errors so one bad request can't block boot.
    document.getElementById('boot-loading').innerHTML = `<div class="text-center max-w-sm px-6"><p class="text-sm font-medium text-rose-600">Failed to load marketplace data</p><p class="text-xs text-ink-400 mt-2">${(e.message||'').replace(/</g,'&lt;')}</p></div>`;
    return;
  }
  document.getElementById('boot-loading').remove();
  buildAppShell();
  navigateTo('dashboard');
  if (failures.length) showLoadFailuresBanner(failures);
});
</script>
</body>
</html>
