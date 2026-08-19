{{-- Section renderers, included inline into the console's <script> block --}}
/* ===== DASHBOARD ===== */
SECTION_RENDERERS.dashboard = async function(main) {
  const d = DATA.dashboard;
  main.innerHTML = `
    ${pageHeader('Dashboard', 'Overview of your marketplace performance')}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Revenue', value: fmtINR(d.total_revenue,{compact:true}), icon:'indian-rupee', accent:'brand'})}
      ${kpiCard({label:"Today's Orders", value: fmtNum(d.todays_orders), icon:'shopping-bag', accent:'sky'})}
      ${kpiCard({label:'Pending Orders', value: fmtNum(d.pending_orders), icon:'clock', accent:'amber'})}
      ${kpiCard({label:'Active Sellers', value: fmtNum(d.active_sellers), icon:'store', accent:'emerald'})}
      ${kpiCard({label:'Customers', value: fmtNum(d.customers), icon:'users', accent:'violet'})}
      ${kpiCard({label:'Products', value: fmtNum(d.products), icon:'package', accent:'sky'})}
      ${kpiCard({label:'Marketplace Commission', value: fmtINR(d.commission,{compact:true}), icon:'percent', accent:'brand'})}
      ${kpiCard({label:'Pending Settlements', value: fmtNum(d.pending_settlements), sub: fmtINR(d.pending_settlements_amount,{compact:true})+' to be paid out', icon:'banknote', accent:'rose'})}
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
      <div class="lg:col-span-2">${card(`<div class="p-5 pb-0"><h3 class="font-semibold text-ink-900 dark:text-white">Revenue Trend</h3><p class="text-xs text-ink-400 mt-0.5">Last 12 months</p></div><div class="p-5 pt-3"><canvas id="chart-revenue" height="230"></canvas></div>`)}</div>
      <div>${card(`<div class="p-5 pb-0"><h3 class="font-semibold text-ink-900 dark:text-white">Order Status</h3></div><div class="p-5"><canvas id="chart-orderstatus" height="230"></canvas></div>`)}</div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
      ${card(`<div class="p-5 pb-0"><h3 class="font-semibold text-ink-900 dark:text-white">Orders — This Year vs Last</h3></div><div class="p-5"><canvas id="chart-sales" height="240"></canvas></div>`)}
      ${card(`<div class="p-5 pb-0"><h3 class="font-semibold text-ink-900 dark:text-white">Products by Category</h3></div><div class="p-5"><canvas id="chart-category" height="240"></canvas></div>`)}
    </div>
    ${card(`<div class="p-5 pb-3"><h3 class="font-semibold text-ink-900 dark:text-white">Recent Activity</h3></div>
      <div class="divide-y divide-ink-100 dark:divide-ink-800">
        ${(d.activities||[]).map(a=>`<div class="px-5 py-3 text-sm text-ink-600 dark:text-ink-300 flex items-center justify-between gap-3"><span>${a.text}</span><span class="text-xs text-ink-400 flex-shrink-0">${a.time}</span></div>`).join('') || '<p class="px-5 py-6 text-sm text-ink-400">No recent activity</p>'}
      </div>`)}
  `;
  renderDashboardCharts();
};

function chartColors() { const isDark = AppState.darkMode; return { grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(23,26,36,0.06)', text: isDark ? '#98a2b3' : '#717a8c' }; }
let chartInstances = {};
function destroyChart(id) { if (chartInstances[id]) { chartInstances[id].destroy(); delete chartInstances[id]; } }

function renderDashboardCharts() {
  const cc = chartColors();
  Chart.defaults.font.family = "'Inter', sans-serif";
  const monthly = DATA.monthlyRevenue || [];

  destroyChart('rev');
  const revCtx = document.getElementById('chart-revenue').getContext('2d');
  const gradient = revCtx.createLinearGradient(0,0,0,230);
  gradient.addColorStop(0, 'rgba(56,104,246,0.25)'); gradient.addColorStop(1, 'rgba(56,104,246,0)');
  chartInstances['rev'] = new Chart(revCtx, { type:'line',
    data: { labels: monthly.map(m=>m.month), datasets: [{ label:'Revenue', data: monthly.map(m=>m.revenue), borderColor:'#3868f6', backgroundColor:gradient, fill:true, tension:.4, pointRadius:0, pointHoverRadius:5, borderWidth:2.5 }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: c => fmtINR(c.raw) } } },
      scales: { y:{ grid:{color:cc.grid}, ticks:{ color:cc.text, callback:v=>fmtINR(v,{compact:true}) } }, x:{ grid:{display:false}, ticks:{color:cc.text} } } } });

  destroyChart('status');
  const statusCounts = {};
  (DATA.orders||[]).forEach(o => statusCounts[o.status] = (statusCounts[o.status]||0)+1);
  chartInstances['status'] = new Chart(document.getElementById('chart-orderstatus'), { type:'doughnut',
    data: { labels: Object.keys(statusCounts), datasets:[{ data: Object.values(statusCounts), backgroundColor:['#fbbf24','#38bdf8','#a78bfa','#6366f1','#3868f6','#22c55e','#f43f5e','#fb923c'], borderWidth:0 }] },
    options: { responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{ legend:{ position:'bottom', labels:{ color:cc.text, boxWidth:8, boxHeight:8, padding:10, font:{size:11} } } } } });

  destroyChart('sales');
  chartInstances['sales'] = new Chart(document.getElementById('chart-sales'), { type:'bar',
    data: { labels: monthly.map(m=>m.month), datasets:[
      { label:'This Year', data: monthly.map(m=>m.orders), backgroundColor:'#3868f6', borderRadius:6, maxBarThickness:18 },
      { label:'Last Year', data: monthly.map(m=>Math.round(m.orders*0.78)), backgroundColor:'#dde1e8', borderRadius:6, maxBarThickness:18 },
    ]},
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'top', align:'end', labels:{color:cc.text, boxWidth:8, boxHeight:8} } },
      scales: { y:{ grid:{color:cc.grid}, ticks:{color:cc.text} }, x:{ grid:{display:false}, ticks:{color:cc.text} } } } });

  destroyChart('category');
  const catSales = [...(DATA.categories||[])].sort((a,b)=>b.products-a.products).slice(0,6);
  chartInstances['category'] = new Chart(document.getElementById('chart-category'), { type:'bar', indexAxis:'y',
    data: { labels: catSales.map(c=>c.name), datasets:[{ data: catSales.map(c=>c.products), backgroundColor:'#5f8fff', borderRadius:6, maxBarThickness:16 }] },
    options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
      scales: { x:{ grid:{color:cc.grid}, ticks:{color:cc.text} }, y:{ grid:{display:false}, ticks:{color:cc.text, font:{size:11}} } } } });
}

/* ===== SELLER MANAGEMENT ===== */
SECTION_RENDERERS.sellers = async function(main) {
  const totalActive = DATA.sellers.filter(s=>s.status==='Active').length;
  const totalPending = DATA.sellers.filter(s=>s.approval==='Pending').length;
  const totalSuspended = DATA.sellers.filter(s=>s.status==='Suspended').length;
  main.innerHTML = `
    ${pageHeader('Seller Management', 'Onboard, verify and monitor vendors on your marketplace', btnPrimary('Add Seller','plus','add-seller-btn')+btnSecondary('Export CSV','download','export-sellers-btn'))}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Sellers', value: fmtNum(DATA.sellers.length), icon:'store', accent:'brand'})}
      ${kpiCard({label:'Active Sellers', value: fmtNum(totalActive), icon:'check-circle', accent:'emerald'})}
      ${kpiCard({label:'Pending KYC', value: fmtNum(totalPending), icon:'clock', accent:'amber'})}
      ${kpiCard({label:'Suspended', value: fmtNum(totalSuspended), icon:'ban', accent:'rose'})}
    </div>
    ${tableCard('sellers-table-inner')}
  `;
  document.getElementById('add-seller-btn').addEventListener('click', () => {
    openModal({ title:'Add Seller', wide:true, body:`<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ${formField('Store Name','text','e.g. Kumar Hardware','','ns-store')}
        ${formField('Owner Name','text','e.g. Rajesh Kumar','','ns-owner')}
        ${formField('Email','email','owner@store.in','','ns-email')}
        ${formField('Phone','tel','9876543210','','ns-phone')}
        ${formField('Login Password','password','At least 8 characters','','ns-password')}
        ${formField('City','text','','','ns-city')}
        ${formField('State','text','','','ns-state')}
        ${formField('GST Number (optional)','text','','','ns-gst')}
        ${formField('Commission %','number','10','10','ns-commission')}
        <div class="sm:col-span-2">${formTextarea('Warehouse Address (optional)','',2,'ns-warehouse')}</div>
      </div>`,
      confirmLabel:'Create Seller', onConfirm: withErr(async () => {
        await api('POST', '/sellers', {
          store_name: document.getElementById('ns-store').value,
          owner_name: document.getElementById('ns-owner').value,
          email: document.getElementById('ns-email').value,
          phone: document.getElementById('ns-phone').value,
          password: document.getElementById('ns-password').value,
          city: document.getElementById('ns-city').value,
          state: document.getElementById('ns-state').value,
          gst_number: document.getElementById('ns-gst').value || undefined,
          commission_pct: parseFloat(document.getElementById('ns-commission').value || 10),
          warehouse_address: document.getElementById('ns-warehouse').value || undefined,
        });
        closeModal(); toast('Seller account created'); await refreshSellers(); navigateTo('sellers');
      }) });
  });
  document.getElementById('export-sellers-btn').addEventListener('click', () => {
    downloadCsv('sellers.csv', DATA.sellers.map(s => ({
      seller_id: s.id, store: s.store, owner: s.name, email: s.email, phone: s.phone,
      city: s.city, state: s.state, gst: s.gst||'', products: s.products, revenue: s.revenue,
      commission_pct: s.commissionPct, kyc: s.approval, status: s.status,
    })));
  });
  renderDataTable('sellers-table-inner', {
    title: 'All Sellers', subtitle: `${DATA.sellers.length} sellers registered on the marketplace`,
    data: DATA.sellers, searchKeys: ['store','name','city','email'],
    filters: [{ key:'approval', label:'Status', options:['Approved','Pending','Rejected'] }, { key:'status', label:'Account', options:['Active','Inactive','Suspended'] }],
    columns: [
      { label:'Seller', render: s => `<div class="flex items-center gap-3">${avatarChip(s.store)}<div class="min-w-0"><p class="font-medium text-ink-800 dark:text-ink-100 truncate max-w-[160px]">${s.store}</p><p class="text-xs text-ink-400">${s.id}</p></div></div>` },
      { label:'Owner', key:'name' }, { label:'City', key:'city' },
      { label:'Products', render: s => fmtNum(s.products) },
      { label:'Revenue', render: s => fmtINR(s.revenue, {compact:true}) },
      { label:'Commission', render: s => s.commissionPct+'%' },
      { label:'KYC', render: s => statusBadge(s.approval) },
      { label:'Status', render: s => statusBadge(s.status) },
    ],
    rowClick: openSellerProfile,
  });
};

function openSellerProfile(s) {
  openDrawer(s.store, `
    <div class="flex items-center gap-4 mb-5">${avatarChip(s.store, 'w-14 h-14 text-base')}
      <div><p class="font-bold text-ink-900 dark:text-white">${s.store}</p><p class="text-sm text-ink-500">${s.name} · ${s.id}</p>
        <div class="flex gap-1.5 mt-1.5">${statusBadge(s.status)}${statusBadge(s.approval)}</div></div>
    </div>
    <div class="grid grid-cols-2 gap-3 mb-5">
      ${miniStat('Revenue', fmtINR(s.revenue, {compact:true}))}
      ${miniStat('Products', fmtNum(s.products))}
      ${miniStat('Rating', s.rating+' ★')}
      ${miniStat('Commission', s.commissionPct+'%')}
    </div>
    <div class="space-y-4">
      ${drawerSection('Store Details', `${drawerRow('Email', s.email)}${drawerRow('Phone', s.phone)}${drawerRow('City', s.city+', '+s.state)}${drawerRow('GSTIN', s.gst||'—')}`)}
      ${drawerSection('KYC Status', `${drawerRow('Status', statusBadge(s.kyc))}`)}
      ${drawerSection('Payouts (Razorpay Route)', `
        ${drawerRow('PAN', s.pan||'—')}
        ${drawerRow('Bank Account', s.bankAccountMasked ? (s.bankAccountHolder+' · '+s.bankAccountMasked+' · '+(s.bankIfsc||'')) : 'Not submitted by seller yet')}
        ${drawerRow('Razorpay Status', statusBadge(s.razorpayAccountStatus||'Not Linked'))}
        ${s.razorpayAccountError ? `<p class="text-xs text-amber-600 dark:text-amber-400 mt-1.5">${s.razorpayAccountError}</p>` : ''}
        ${(s.razorpayAccountError||'').toLowerCase().includes('route feature not enabled') ? `<p class="text-xs text-ink-400 mt-1.5">This isn't fixable from here — Route (split payouts) has to be turned on for <b>our own</b> Razorpay account by Razorpay themselves, usually after their team reviews your business KYC/activation. Contact Razorpay support to request Route activation. Until it's on, this seller's payouts fall back to manual bank transfer below.</p>` : ''}
        <button id="link-razorpay-btn" ${!s.hasPayoutDetails ? 'disabled' : ''} class="mt-3 w-full py-2 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 disabled:opacity-50 disabled:cursor-not-allowed">
          ${s.hasPayoutDetails ? ((s.razorpayAccountStatus||'Not Linked') === 'Not Linked' ? 'Link Razorpay Account' : 'Re-submit Razorpay Account') : 'Waiting for seller to submit bank details'}
        </button>
      `)}
      ${drawerSection('Wallet', `${drawerRow('Wallet Balance', fmtINR(s.walletBalance))}`)}
      ${drawerSection('Base Commission Rate', `<input id="drawer-commission-input" type="number" step="0.1" value="${s.commissionPct}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none"><p class="text-[11px] text-ink-400 mt-1.5">Applies to every category unless a category override below is set.</p>`)}
      ${drawerSection('Category Commission Overrides', `
        <input id="drawer-category-filter" placeholder="Filter categories…" class="w-full mb-2 px-3 py-2 text-xs rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
        <div id="commission-overrides-list" class="max-h-72 overflow-y-auto space-y-3 pr-1"><p class="text-xs text-ink-400">Loading…</p></div>
      `)}
    </div>
    <div class="flex gap-2.5 mt-6 pt-5 border-t border-ink-100 dark:border-ink-800 flex-wrap">
      <button id="save-commission-btn" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl">Save Commission</button>
      ${s.approval==='Pending' ? `
        <button id="approve-seller-btn" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl">Approve Seller</button>
        <button id="reject-seller-btn" class="flex-1 px-4 py-2.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 text-sm font-medium rounded-xl hover:bg-rose-100">Reject</button>
      ` : s.status === 'Active' ? `<button id="suspend-seller-btn" class="flex-1 px-4 py-2.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 text-sm font-medium rounded-xl hover:bg-rose-100">Suspend Seller</button>`
      : `<button id="reactivate-seller-btn" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl">Reactivate Seller</button>`}
    </div>
  `, true);

  document.getElementById('save-commission-btn')?.addEventListener('click', withErr(async () => {
    const v = parseFloat(document.getElementById('drawer-commission-input').value);
    await api('PATCH', '/sellers/'+s.id, { commission_pct: v });
    closeModal(); toast('Commission rate updated'); await refreshSellers(); navigateTo('sellers');
  }));
  document.getElementById('approve-seller-btn')?.addEventListener('click', withErr(async () => {
    await api('PATCH', '/sellers/'+s.id, { approval_status:'Approved', account_status:'Active', kyc_status:'Verified' });
    closeModal(); toast(`${s.store} approved successfully`); await refreshSellers(); navigateTo('sellers');
  }));
  document.getElementById('reject-seller-btn')?.addEventListener('click', withErr(async () => {
    await api('PATCH', '/sellers/'+s.id, { approval_status:'Rejected', account_status:'Inactive' });
    closeModal(); toast(`${s.store} application rejected`,'warn'); await refreshSellers(); navigateTo('sellers');
  }));
  document.getElementById('suspend-seller-btn')?.addEventListener('click', withErr(async () => {
    await api('PATCH', '/sellers/'+s.id, { account_status:'Suspended' });
    closeModal(); toast(`${s.store} has been suspended`,'warn'); await refreshSellers(); navigateTo('sellers');
  }));
  document.getElementById('reactivate-seller-btn')?.addEventListener('click', withErr(async () => {
    await api('PATCH', '/sellers/'+s.id, { account_status:'Active' });
    closeModal(); toast(`${s.store} reactivated`); await refreshSellers(); navigateTo('sellers');
  }));
  document.getElementById('link-razorpay-btn')?.addEventListener('click', withErr(async () => {
    const btn = document.getElementById('link-razorpay-btn');
    btn.disabled = true; btn.textContent = 'Linking…';
    try {
      await api('POST', '/sellers/'+s.id+'/link-razorpay');
      toast('Razorpay account linked — awaiting their KYC review'); await refreshSellers(); navigateTo('sellers');
    } catch (e) {
      toast(e.message || 'Could not link Razorpay account', 'warn');
      await refreshSellers(); navigateTo('sellers');
    }
  }));

  loadCommissionOverrides(s);
  document.getElementById('drawer-category-filter')?.addEventListener('input', (e) => {
    const term = e.target.value.trim().toLowerCase();
    document.querySelectorAll('[data-cat-override-row]').forEach(row => {
      row.style.display = !term || row.dataset.catName.includes(term) ? '' : 'none';
    });
  });
}

async function loadCommissionOverrides(s) {
  const container = document.getElementById('commission-overrides-list');
  if (!container) return;
  let overrides;
  try {
    overrides = await api('GET', '/commission/sellers/'+s.id+'/categories');
  } catch (e) {
    container.innerHTML = '<p class="text-xs text-rose-600">Could not load overrides.</p>';
    return;
  }
  const overrideMap = {};
  overrides.forEach(o => { overrideMap[o.categoryId] = o.commissionPct; });

  const topLevel = DATA.categories.filter(c => !c.parentId);
  const rowHTML = (c, indent) => {
    const val = overrideMap[c.id];
    return `
      <div data-cat-override-row data-cat-name="${c.name.toLowerCase()}" class="flex items-center gap-2" style="padding-left:${indent}px;">
        <span class="text-xs text-ink-700 dark:text-ink-300 flex-1 truncate">${c.name}</span>
        <input type="number" step="0.1" placeholder="${s.commissionPct}" value="${val !== undefined ? val : ''}" data-override-input="${c.id}" class="w-20 px-2 py-1 text-xs rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
        <button data-save-override="${c.id}" class="px-2 py-1 text-[11px] font-medium rounded-lg border border-ink-200 dark:border-ink-700">Save</button>
        ${val !== undefined ? `<button data-clear-override="${c.id}" class="w-5 h-5 rounded-full bg-rose-600 text-white text-[10px] leading-none flex items-center justify-center flex-shrink-0">×</button>` : ''}
      </div>`;
  };

  container.innerHTML = topLevel.map(top => `
    ${rowHTML(top, 0)}
    ${DATA.categories.filter(c => c.parentId === top.id).map(sub => rowHTML(sub, 16)).join('')}
  `).join('');

  container.querySelectorAll('[data-save-override]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const catId = btn.dataset.saveOverride;
    const input = container.querySelector('[data-override-input="'+catId+'"]');
    const v = parseFloat(input.value);
    if (isNaN(v)) { toast('Enter a commission percentage', 'warn'); return; }
    await api('POST', '/commission/sellers/'+s.id+'/categories', { category_code: catId, commission_pct: v });
    toast('Category commission saved');
    await loadCommissionOverrides(s);
  })));
  container.querySelectorAll('[data-clear-override]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/commission/sellers/'+s.id+'/categories/'+btn.dataset.clearOverride);
    toast('Reverted to base rate');
    await loadCommissionOverrides(s);
  })));
}

/* ===== CUSTOMER MANAGEMENT ===== */
SECTION_RENDERERS.customers = async function(main) {
  const activeCount = DATA.customers.filter(c=>c.status==='Active').length;
  const avgSpend = DATA.customers.length ? Math.round(DATA.customers.reduce((a,c)=>a+c.totalSpend,0)/DATA.customers.length) : 0;
  const repeatCustomers = DATA.customers.filter(c=>c.orders>3).length;
  main.innerHTML = `
    ${pageHeader('Customer Management', 'View and manage customer accounts')}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Customers', value: fmtNum(DATA.customers.length), icon:'users', accent:'violet'})}
      ${kpiCard({label:'Active', value: fmtNum(activeCount), icon:'user-check', accent:'emerald'})}
      ${kpiCard({label:'Avg. Order Value', value: fmtINR(avgSpend), icon:'wallet', accent:'brand'})}
      ${kpiCard({label:'Repeat Customers', value: fmtNum(repeatCustomers), icon:'repeat', accent:'sky'})}
    </div>
    ${tableCard('customers-table-inner')}
  `;
  renderDataTable('customers-table-inner', {
    title: 'All Customers', subtitle: `${DATA.customers.length} registered customers`,
    data: DATA.customers, searchKeys: ['name','email','city'],
    filters: [{ key:'status', label:'Status', options:['Active','Blocked'] }],
    columns: [
      { label:'Customer', render: c => `<div class="flex items-center gap-3">${avatarChip(c.name)}<div><p class="font-medium text-ink-800 dark:text-ink-100">${c.name}</p><p class="text-xs text-ink-400">${c.id}</p></div></div>` },
      { label:'Email', key:'email' }, { label:'City', key:'city' },
      { label:'Orders', render: c => fmtNum(c.orders) },
      { label:'Total Spend', render: c => fmtINR(c.totalSpend) },
      { label:'Wishlist', render: c => fmtNum(c.wishlist)+' items' },
      { label:'Status', render: c => statusBadge(c.status) },
      { label:'', render: c => `<button data-stop-row-click data-toggle-customer="${c.id}" class="text-xs font-medium text-brand-600 hover:underline">${c.status==='Active'?'Block':'Unblock'}</button>` },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-toggle-customer]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        const id = btn.dataset.toggleCustomer;
        const c = DATA.customers.find(x=>x.id===id);
        const newStatus = c.status==='Active' ? 'Blocked' : 'Active';
        await api('PATCH', '/customers/'+id, { status:newStatus });
        toast(`${c.name} ${newStatus==='Blocked'?'blocked':'unblocked'}`);
        await refreshCustomers(); navigateTo('customers');
      })));
    }
  });
};

/* ===== PRODUCT MANAGEMENT ===== */
SECTION_RENDERERS.products = async function(main) {
  const featuredCount = DATA.products.filter(p=>p.featured).length;
  const lowStockCount = DATA.products.filter(p=>p.stockStatus==='Low Stock').length;
  const outOfStockCount = DATA.products.filter(p=>p.stockStatus==='Out of Stock').length;
  main.innerHTML = `
    ${pageHeader('Product Management', 'Manage your marketplace catalog', btnPrimary('Add Product','plus','add-product-btn'))}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Products', value: fmtNum(DATA.products.length), icon:'package', accent:'brand'})}
      ${kpiCard({label:'Featured', value: fmtNum(featuredCount), icon:'star', accent:'amber'})}
      ${kpiCard({label:'Low Stock', value: fmtNum(lowStockCount), icon:'alert-triangle', accent:'amber'})}
      ${kpiCard({label:'Out of Stock', value: fmtNum(outOfStockCount), icon:'package-x', accent:'rose'})}
    </div>
    ${tableCard('products-table-inner')}
  `;
  renderDataTable('products-table-inner', {
    title: 'All Products', subtitle: `${DATA.products.length} products across your marketplace`,
    data: DATA.products, searchKeys: ['name','category','sellerName'],
    filters: [
      { key:'category', label:'Category', options:[...new Set(DATA.products.map(p=>p.category))] },
      { key:'stockStatus', label:'Stock', options:['In Stock','Low Stock','Out of Stock'] },
      { key:'approval', label:'Approval', options:['Approved','Pending','Rejected'] },
    ],
    columns: [
      { label:'Product', render: p => `<span class="font-medium text-ink-800 dark:text-ink-100 truncate max-w-[180px] inline-block">${p.name}</span>` },
      { label:'Category', key:'category' },
      { label:'Seller', render: p => `<span class="truncate max-w-[140px] inline-block">${p.sellerName}</span>` },
      { label:'Price', render: p => fmtINR(p.price) },
      { label:'Stock', render: p => `${p.stock} <span class="text-ink-400">units</span>` },
      { label:'Status', render: p => statusBadge(p.stockStatus) },
      { label:'Approval', render: p => statusBadge(p.approval) },
      { label:'', render: p => `<button data-stop-row-click data-delete-product="${p.id}" class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg text-rose-500">${icon('trash-2','w-4 h-4')}</button>` },
    ],
    rowClick: openProductProfile,
    afterRender: (container) => {
      container.querySelectorAll('[data-delete-product]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        if (!confirm('Delete this product permanently?')) return;
        await api('DELETE', '/products/'+btn.dataset.deleteProduct);
        toast('Product deleted'); await refreshProducts(); navigateTo('products');
      })));
    }
  });

  document.getElementById('add-product-btn').addEventListener('click', () => {
    openModal({
      title:'Add New Product', wide:true,
      body:`<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ${formField('Product Name','text','e.g. Cordless Drill 18V','','np-name')}
        ${formCategorySelect('Category', DATA.categories, 'np-category')}
        ${formSelect('Brand', DATA.brands.map(b=>b.name), 0, 'np-brand')}
        ${formSelect('Seller', DATA.sellers.map(s=>s.store), 0, 'np-seller')}
        ${formField('Price (₹)','number','e.g. 2499','','np-price')}
        ${formField('MRP (₹)','number','e.g. 2999','','np-mrp')}
        ${formField('Stock','number','e.g. 100','0','np-stock')}
        ${formSelect('Unit', ['piece','box','pair','coil','bag','litre','tub'], 0, 'np-unit')}
        <div class="sm:col-span-2">${formTextarea('Description', 'What makes this product worth buying', 3, 'np-description')}</div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">Product Photos (up to 8)</label>
          <input type="file" id="np-images" multiple accept="image/*" class="w-full text-sm text-ink-600 dark:text-ink-300 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 dark:file:bg-ink-700 file:text-ink-700 dark:file:text-ink-200 file:text-xs file:font-medium">
        </div>
        ${formField('Video Link (optional)','url','https://youtube.com/watch?v=...','','np-video')}
      </div>`,
      confirmLabel:'Create Product',
      onConfirm: withErr(async () => {
        const category = DATA.categories.find(c=>c.name===document.getElementById('np-category').value);
        const brand = DATA.brands.find(b=>b.name===document.getElementById('np-brand').value);
        const seller = DATA.sellers.find(s=>s.store===document.getElementById('np-seller').value);
        const created = await api('POST', '/products', {
          name: document.getElementById('np-name').value,
          category_code: category ? category.id : null,
          brand_code: brand ? brand.id : null,
          seller_code: seller ? seller.id : null,
          price: parseFloat(document.getElementById('np-price').value||0),
          mrp: parseFloat(document.getElementById('np-mrp').value||0),
          stock: parseInt(document.getElementById('np-stock').value||0),
          unit: document.getElementById('np-unit').value,
          description: document.getElementById('np-description').value || undefined,
          video_url: document.getElementById('np-video').value || undefined,
        });
        const files = document.getElementById('np-images').files;
        if (files && files.length) {
          const fd = new FormData();
          Array.from(files).forEach(f => fd.append('images[]', f));
          const res = await fetch('/admin/api/products/'+created.id+'/images', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }, body: fd,
          });
          if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Photo upload failed'); }
        }
        closeModal(); toast('Product created successfully'); await refreshProducts(); navigateTo('products');
      })
    });
  });
};

function openProductProfile(p) {
  openDrawer(p.name, `
    <div class="grid grid-cols-2 gap-3 mb-5">
      ${miniStat('Price', fmtINR(p.price))}
      ${miniStat('Stock', fmtNum(p.stock))}
      ${miniStat('Rating', p.rating+' ★')}
      ${miniStat('Sold', fmtNum(p.sold))}
    </div>
    ${drawerSection('Details', `${drawerRow('Category', p.category)}${drawerRow('Seller', p.sellerName)}${drawerRow('MRP', fmtINR(p.mrp))}${drawerRow('Approval', statusBadge(p.approval))}`)}
    ${drawerSection('Photos', `
      <div class="flex flex-wrap gap-2 mb-3">
        ${(p.images||[]).map(img => `
          <div class="relative w-16 h-16">
            <img src="${img.url}" class="w-16 h-16 rounded-lg object-cover border border-ink-200 dark:border-ink-700">
            <button data-remove-image="${img.id}" class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-600 text-white text-xs leading-none flex items-center justify-center">×</button>
          </div>`).join('') || `<p class="text-xs text-ink-400">No photos uploaded yet</p>`}
      </div>
      <input type="file" id="drawer-add-images" multiple accept="image/*" class="w-full text-sm text-ink-600 dark:text-ink-300 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 dark:file:bg-ink-700 file:text-ink-700 dark:file:text-ink-200 file:text-xs file:font-medium">
      <button id="drawer-upload-images-btn" class="mt-2 w-full py-2 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700">Upload Photos</button>
      ${p.videoUrl ? `<a href="${p.videoUrl}" target="_blank" class="block mt-3 text-sm text-brand-600 font-medium">▶ Watch product video</a>` : ''}
    `)}
    ${drawerSection('Options (Color, Size, Units, etc.)', `
      <div class="space-y-2 mb-3">
        ${(p.variants||[]).map(v => `
          <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-ink-200 dark:border-ink-700">
            <div class="min-w-0">
              <div class="text-sm font-medium text-ink-900 dark:text-white truncate">${v.label || v.sku}</div>
              <div class="text-xs text-ink-400">${v.sku} · ${v.price !== null ? fmtINR(v.price) : 'Base price'} · Stock ${v.stock} · ${v.status}</div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <button data-edit-variant="${v.id}" class="px-2 py-1 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700">Edit</button>
              <button data-toggle-variant="${v.id}" data-variant-status="${v.status}" class="px-2 py-1 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700">${v.status==='Active'?'Deactivate':'Activate'}</button>
              <button data-remove-variant="${v.id}" class="w-6 h-6 rounded-full bg-rose-600 text-white text-xs leading-none flex items-center justify-center">×</button>
            </div>
          </div>`).join('') || `<p class="text-xs text-ink-400">No options yet — customers buy the base product.</p>`}
      </div>
      ${(() => {
        const existingKeys = Array.from(new Set((p.variants||[]).flatMap(v => Object.keys(v.attributes||{}))));
        if (existingKeys.length) {
          return `
            <p class="text-[11px] text-ink-400 mb-2">This product's options already use: <b>${existingKeys.join(', ')}</b>. Fill in a value for each below — every option on the same product needs the same attribute types, or customers won't be able to pick a combination.</p>
            <div id="drawer-variant-attrs" class="space-y-2 mb-2">
              ${existingKeys.map(k => `
                <div class="grid grid-cols-2 gap-2 drawer-variant-attr-row" data-locked-key="${k}">
                  <div class="px-2.5 py-1.5 text-sm rounded-lg bg-ink-50 dark:bg-ink-800/60 text-ink-500 dark:text-ink-400 border border-ink-200 dark:border-ink-700">${k}</div>
                  <input placeholder="Value for ${k}" class="variant-attr-value px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
                </div>`).join('')}
            </div>
            <button id="drawer-add-attr-row-btn" type="button" class="mb-2 text-xs font-medium text-brand-600 dark:text-brand-400">+ Add a new attribute type</button>
          `;
        }
        return `
          <div id="drawer-variant-attrs" class="space-y-2 mb-2">
            <div class="grid grid-cols-2 gap-2 drawer-variant-attr-row">
              <input placeholder="Attribute (e.g. Color, Size, Units)" class="variant-attr-key px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
              <input placeholder="Value (e.g. Red, XL, 12mm)" class="variant-attr-value px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
            </div>
          </div>
          <button id="drawer-add-attr-row-btn" type="button" class="mb-2 text-xs font-medium text-brand-600 dark:text-brand-400">+ Add another attribute</button>
        `;
      })()}
      <div class="grid grid-cols-2 gap-2">
        <input id="drawer-variant-price" type="number" step="0.01" placeholder="Price override (₹)" class="px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
        <input id="drawer-variant-stock" type="number" placeholder="Stock" class="px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
      </div>
      <button id="drawer-add-variant-btn" class="mt-2 w-full py-2 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700">Add Option</button>
    `)}
    ${drawerSection('Bulk / Quantity Pricing', `
      <p class="text-[11px] text-ink-400 mb-3">Offer a lower unit price at higher quantities, e.g. ₹500/pc under 100 pcs, ₹450/pc from 100 pcs onward. Applies to the base product, not individual options.</p>
      <div class="space-y-2 mb-3">
        ${(p.priceTiers||[]).slice().sort((a,b)=>a.minQty-b.minQty).map(t => `
          <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-ink-200 dark:border-ink-700">
            <div class="text-sm font-medium text-ink-900 dark:text-white">${t.minQty}+ pcs — ${fmtINR(t.price)}</div>
            <button data-remove-price-tier="${t.id}" class="w-6 h-6 rounded-full bg-rose-600 text-white text-xs leading-none flex items-center justify-center">×</button>
          </div>`).join('') || `<p class="text-xs text-ink-400">No quantity tiers yet — every order pays the base price.</p>`}
      </div>
      <div class="grid grid-cols-2 gap-2">
        <input id="drawer-tier-minqty" type="number" min="1" placeholder="Minimum quantity" class="px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
        <input id="drawer-tier-price" type="number" step="0.01" min="0" placeholder="Price per unit (₹)" class="px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
      </div>
      <button id="drawer-add-price-tier-btn" class="mt-2 w-full py-2 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700">Add Tier</button>
    `)}
    <div class="flex gap-2.5 mt-6 pt-5 border-t border-ink-100 dark:border-ink-800">
      <button id="edit-product-btn" class="flex-1 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl">Edit Details</button>
      ${p.approval!=='Approved' ? `<button id="approve-product-btn" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl">Approve</button>` : ''}
      <button id="toggle-featured-btn" class="flex-1 px-4 py-2.5 bg-white dark:bg-ink-800 border border-ink-200 dark:border-ink-700 text-sm font-medium rounded-xl">${p.featured?'Unfeature':'Feature'} Product</button>
    </div>
  `, true);
  document.getElementById('edit-product-btn')?.addEventListener('click', () => {
    openModal({
      title:'Edit Product', wide:true,
      body:`<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ${formField('Product Name','text','e.g. Cordless Drill 18V',p.name,'ep-name')}
        ${formCategorySelect('Category', DATA.categories, 'ep-category')}
        ${formSelect('Brand', DATA.brands.map(b=>b.name), DATA.brands.findIndex(b=>b.id===p.brandCode), 'ep-brand')}
        ${formSelect('Seller', DATA.sellers.map(s=>s.store), DATA.sellers.findIndex(s=>s.id===p.sellerCode), 'ep-seller')}
        ${formField('Price (₹)','number','e.g. 2499',p.price,'ep-price')}
        ${formField('MRP (₹)','number','e.g. 2999',p.mrp,'ep-mrp')}
        ${formField('Stock','number','e.g. 100',p.stock,'ep-stock')}
        ${formSelect('Unit', ['piece','box','pair','coil','bag','litre','tub'], Math.max(0, ['piece','box','pair','coil','bag','litre','tub'].indexOf(p.unit)), 'ep-unit')}
        <div class="sm:col-span-2">${formTextarea('Description', 'What makes this product worth buying', 3, 'ep-description')}</div>
        ${formField('Video Link (optional)','url','https://youtube.com/watch?v=...',p.videoUrl||'','ep-video')}
      </div>`,
      confirmLabel:'Save Changes',
      onConfirm: withErr(async () => {
        const category = DATA.categories.find(c=>c.name===document.getElementById('ep-category').value);
        const brand = DATA.brands.find(b=>b.name===document.getElementById('ep-brand').value);
        const seller = DATA.sellers.find(s=>s.store===document.getElementById('ep-seller').value);
        await api('PATCH', '/products/'+p.id, {
          name: document.getElementById('ep-name').value,
          category_code: category ? category.id : undefined,
          brand_code: brand ? brand.id : undefined,
          seller_code: seller ? seller.id : undefined,
          price: parseFloat(document.getElementById('ep-price').value||0),
          mrp: parseFloat(document.getElementById('ep-mrp').value||0),
          stock: parseInt(document.getElementById('ep-stock').value||0),
          unit: document.getElementById('ep-unit').value,
          description: document.getElementById('ep-description').value || null,
          video_url: document.getElementById('ep-video').value || null,
        });
        closeModal(); toast('Product updated'); await refreshProducts(); navigateTo('products');
      })
    });
    document.getElementById('ep-category').value = p.category;
    document.getElementById('ep-description').value = p.description || '';
  });
  document.getElementById('drawer-upload-images-btn')?.addEventListener('click', withErr(async () => {
    const files = document.getElementById('drawer-add-images').files;
    if (!files || !files.length) { toast('Choose at least one photo', 'warn'); return; }
    const fd = new FormData();
    Array.from(files).forEach(f => fd.append('images[]', f));
    const res = await fetch('/admin/api/products/'+p.id+'/images', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
    if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Photo upload failed'); }
    toast('Photos uploaded'); await refreshProducts(); navigateTo('products');
  }));
  document.querySelectorAll('[data-remove-image]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/products/'+p.id+'/images/'+btn.dataset.removeImage);
    toast('Photo removed'); await refreshProducts(); navigateTo('products');
  })));
  document.getElementById('drawer-add-attr-row-btn')?.addEventListener('click', () => {
    const wrap = document.getElementById('drawer-variant-attrs');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-2 gap-2 drawer-variant-attr-row';
    row.innerHTML = `
      <input placeholder="Attribute (e.g. Color, Size, Units)" class="variant-attr-key px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
      <input placeholder="Value (e.g. Red, XL, 12mm)" class="variant-attr-value px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
    `;
    wrap.appendChild(row);
  });
  document.getElementById('drawer-add-variant-btn')?.addEventListener('click', withErr(async () => {
    const options = {};
    let missingLocked = false;
    document.querySelectorAll('.drawer-variant-attr-row').forEach(row => {
      const key = row.dataset.lockedKey || (row.querySelector('.variant-attr-key')?.value.trim() || '');
      const value = row.querySelector('.variant-attr-value').value.trim();
      if (key && value) options[key] = value;
      else if (row.dataset.lockedKey) missingLocked = true;
    });
    if (missingLocked) { toast('Fill in a value for every existing attribute (e.g. Color, Size) so this option matches the others', 'warn'); return; }
    if (!Object.keys(options).length) { toast('Enter at least one attribute (e.g. Color, Size, Units)', 'warn'); return; }
    const priceVal = document.getElementById('drawer-variant-price').value;
    await api('POST', '/products/'+p.id+'/variants', {
      options,
      price: priceVal ? parseFloat(priceVal) : undefined,
      stock: parseInt(document.getElementById('drawer-variant-stock').value || 0),
    });
    toast('Option added'); await refreshProducts(); navigateTo('products');
  }));
  document.querySelectorAll('[data-edit-variant]').forEach(btn => btn.addEventListener('click', () => {
    const v = (p.variants||[]).find(x => x.id === btn.dataset.editVariant);
    if (!v) return;
    const attrs = Object.entries(v.attributes || {});
    openModal({
      title: 'Edit Option',
      body: `
        <div id="edit-variant-attrs" class="space-y-2 mb-2">
          ${(attrs.length ? attrs : [['','']]).map(([k,val]) => `
            <div class="grid grid-cols-2 gap-2 edit-variant-attr-row">
              <input placeholder="Attribute (e.g. Color)" value="${k}" class="variant-attr-key w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800">
              <input placeholder="Value (e.g. Red)" value="${val}" class="variant-attr-value w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800">
            </div>`).join('')}
        </div>
        <button type="button" id="edit-variant-add-row-btn" class="mb-3 text-xs font-medium text-brand-600 dark:text-brand-400">+ Add another attribute</button>
        <div class="grid grid-cols-2 gap-3">
          ${formField('Price override (₹)','number','Same as base price', v.price !== null ? v.price : '', 'ev-price')}
          ${formField('Stock','number','e.g. 25', v.stock, 'ev-stock')}
        </div>
        <div class="mt-3">${formField('SKU','text','SKU', v.sku, 'ev-sku')}</div>
      `,
      confirmLabel: 'Save Option',
      onConfirm: withErr(async () => {
        const options = {};
        document.querySelectorAll('.edit-variant-attr-row').forEach(row => {
          const key = row.querySelector('.variant-attr-key').value.trim();
          const value = row.querySelector('.variant-attr-value').value.trim();
          if (key && value) options[key] = value;
        });
        if (!Object.keys(options).length) { toast('Enter at least one attribute (e.g. Color, Size, Units)', 'warn'); return; }
        const priceVal = document.getElementById('ev-price').value;
        await api('PATCH', '/products/'+p.id+'/variants/'+v.id, {
          options,
          price: priceVal ? parseFloat(priceVal) : null,
          stock: parseInt(document.getElementById('ev-stock').value || 0),
          sku: document.getElementById('ev-sku').value || undefined,
        });
        closeModal(); toast('Option updated'); await refreshProducts(); navigateTo('products');
      })
    });
    document.getElementById('edit-variant-add-row-btn')?.addEventListener('click', () => {
      const wrap = document.getElementById('edit-variant-attrs');
      const row = document.createElement('div');
      row.className = 'grid grid-cols-2 gap-2 edit-variant-attr-row';
      row.innerHTML = `
        <input placeholder="Attribute (e.g. Size)" class="variant-attr-key w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800">
        <input placeholder="Value (e.g. XL)" class="variant-attr-value w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800">
      `;
      wrap.appendChild(row);
    });
  }));
  document.querySelectorAll('[data-toggle-variant]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const nextStatus = btn.dataset.variantStatus === 'Active' ? 'Inactive' : 'Active';
    await api('PATCH', '/products/'+p.id+'/variants/'+btn.dataset.toggleVariant, { status: nextStatus });
    toast('Option updated'); await refreshProducts(); navigateTo('products');
  })));
  document.querySelectorAll('[data-remove-variant]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/products/'+p.id+'/variants/'+btn.dataset.removeVariant);
    toast('Option removed'); await refreshProducts(); navigateTo('products');
  })));
  document.getElementById('drawer-add-price-tier-btn')?.addEventListener('click', withErr(async () => {
    const minQty = parseInt(document.getElementById('drawer-tier-minqty').value || 0);
    const price = document.getElementById('drawer-tier-price').value;
    if (!minQty || !price) { toast('Enter a minimum quantity and price', 'warn'); return; }
    await api('POST', '/products/'+p.id+'/price-tiers', { min_qty: minQty, price: parseFloat(price) });
    toast('Price tier added'); await refreshProducts(); navigateTo('products');
  }));
  document.querySelectorAll('[data-remove-price-tier]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/products/'+p.id+'/price-tiers/'+btn.dataset.removePriceTier);
    toast('Price tier removed'); await refreshProducts(); navigateTo('products');
  })));
  document.getElementById('approve-product-btn')?.addEventListener('click', withErr(async () => {
    await api('PATCH', '/products/'+p.id, { approval_status:'Approved' });
    closeModal(); toast('Product approved'); await refreshProducts(); navigateTo('products');
  }));
  document.getElementById('toggle-featured-btn')?.addEventListener('click', withErr(async () => {
    await api('PATCH', '/products/'+p.id, { featured: !p.featured });
    closeModal(); toast('Product updated'); await refreshProducts(); navigateTo('products');
  }));
}

/* ===== CATEGORIES ===== */
SECTION_RENDERERS.categories = async function(main) {
  const topLevel = DATA.categories.filter(c => !c.parentId);
  const subsOf = (id) => DATA.categories.filter(c => c.parentId === id);
  main.innerHTML = `
    ${pageHeader('Categories', 'Organize products into categories and subcategories', btnPrimary('Add Category','plus','add-category-btn'))}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      ${topLevel.map(c => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center overflow-hidden">
              ${c.iconUrl ? `<img src="${c.iconUrl}" alt="" class="w-full h-full object-contain">`
                : c.iconClass ? `<i class="${c.iconClass}" style="font-size:18px"></i>`
                : icon('layout-grid','w-5 h-5')}
            </div>
            ${statusBadge(c.status)}
          </div>
          <p class="font-semibold text-ink-900 dark:text-white">${c.name}</p>
          <p class="text-xs text-ink-400 mt-1">${fmtNum(c.products)} products directly</p>
          <div class="mt-4 pt-3 border-t border-ink-100 dark:border-ink-800 space-y-2">
            <label class="text-xs font-medium text-ink-500 block">Custom Icon</label>
            <input type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-cat-icon-input="${c.id}" class="w-full text-xs text-ink-600 dark:text-ink-300 file:mr-2 file:px-2.5 file:py-1 file:rounded-lg file:border-0 file:bg-ink-100 dark:file:bg-ink-800 file:text-ink-700 dark:file:text-ink-200 file:text-xs file:font-medium">
            <div class="flex items-center gap-2">
              <input type="text" placeholder="or a Font Awesome class, e.g. fa-solid fa-bolt" value="${c.iconClass && !c.iconUrl ? c.iconClass : ''}" data-cat-iconclass-input="${c.id}" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
              <button data-set-cat-iconclass="${c.id}" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 flex-shrink-0">Set</button>
            </div>
            <p class="text-[10px] text-ink-400">JPG/PNG image, or a Font Awesome (free) icon class</p>
            ${(c.iconUrl || c.iconClass) ? `<button data-remove-cat-icon="${c.id}" class="text-xs font-medium text-rose-600 hover:underline">Remove custom icon</button>` : ''}
          </div>
          <div class="flex gap-2 mt-3">
            <button data-toggle-cat="${c.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${c.status==='Active'?'Deactivate':'Activate'}</button>
            <button data-delete-cat="${c.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">Remove</button>
          </div>
          <div class="mt-4 pt-4 border-t border-ink-100 dark:border-ink-800">
            <p class="text-xs font-semibold text-ink-500 mb-2">Subcategories (${subsOf(c.id).length})</p>
            <div class="space-y-1.5 mb-2">
              ${subsOf(c.id).map(s => `
                <div class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg border border-ink-200 dark:border-ink-700">
                  <div class="min-w-0"><span class="text-sm text-ink-800 dark:text-ink-200">${s.name}</span> <span class="text-xs text-ink-400">· ${fmtNum(s.products)} products</span></div>
                  <div class="flex items-center gap-1 flex-shrink-0">
                    <button data-toggle-cat="${s.id}" class="px-2 py-1 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700">${s.status==='Active'?'Deactivate':'Activate'}</button>
                    <button data-delete-cat="${s.id}" class="w-6 h-6 rounded-full bg-rose-600 text-white text-xs leading-none flex items-center justify-center">×</button>
                  </div>
                </div>`).join('') || `<p class="text-xs text-ink-400">No subcategories yet.</p>`}
            </div>
            <div class="flex items-center gap-2">
              <input placeholder="New subcategory name" data-new-subcat="${c.id}" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-ink-200 dark:border-ink-700 dark:bg-ink-800 dark:text-white">
              <button data-add-subcat="${c.id}" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 flex-shrink-0">Add</button>
            </div>
          </div>
        </div>`).join('')}
    </div>`;
  document.getElementById('add-category-btn').addEventListener('click', () => {
    openModal({ title:'Add New Category', body:`<div class="space-y-4">${formField('Category Name','text','e.g. Power Tools','','nc-name')}</div>`,
      confirmLabel:'Create Category', onConfirm: withErr(async () => {
        await api('POST', '/categories', { name: document.getElementById('nc-name').value });
        closeModal(); toast('Category created'); await refreshCategories(); navigateTo('categories');
      }) });
  });
  main.querySelectorAll('[data-add-subcat]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const parent = DATA.categories.find(x=>x.id===btn.dataset.addSubcat);
    const input = main.querySelector('[data-new-subcat="'+btn.dataset.addSubcat+'"]');
    const name = input.value.trim();
    if (!name) { toast('Enter a subcategory name', 'warn'); return; }
    await api('POST', '/categories', { name, parent_code: parent.id });
    toast('Subcategory added'); await refreshCategories(); navigateTo('categories');
  })));
  main.querySelectorAll('[data-toggle-cat]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const c = DATA.categories.find(x=>x.id===btn.dataset.toggleCat);
    await api('PATCH', '/categories/'+c.id, { status: c.status==='Active'?'Inactive':'Active' });
    toast('Category updated'); await refreshCategories(); navigateTo('categories');
  })));
  main.querySelectorAll('[data-delete-cat]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    if (!confirm('Delete this category?')) return;
    await api('DELETE', '/categories/'+btn.dataset.deleteCat);
    toast('Category removed'); await refreshCategories(); navigateTo('categories');
  })));
  main.querySelectorAll('[data-cat-icon-input]').forEach(input => input.addEventListener('change', withErr(async () => {
    if (!input.files.length) return;
    const fd = new FormData();
    fd.append('icon', input.files[0]);
    const res = await fetch('/admin/api/categories/'+input.dataset.catIconInput+'/icon', {
      method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }, body: fd,
    });
    if (!res.ok) throw new Error((await res.json().catch(()=>({}))).message || 'Upload failed');
    toast('Icon uploaded'); await refreshCategories(); navigateTo('categories');
  })));
  main.querySelectorAll('[data-remove-cat-icon]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/categories/'+btn.dataset.removeCatIcon+'/icon');
    toast('Icon removed'); await refreshCategories(); navigateTo('categories');
  })));
  main.querySelectorAll('[data-set-cat-iconclass]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const id = btn.dataset.setCatIconclass;
    const value = main.querySelector('[data-cat-iconclass-input="'+id+'"]').value.trim();
    if (!value) { toast('Enter a Font Awesome class first'); return; }
    await api('POST', '/categories/'+id+'/icon-class', { icon_class: value });
    toast('Icon updated'); await refreshCategories(); navigateTo('categories');
  })));
};

/* ===== BRANDS ===== */
SECTION_RENDERERS.brands = async function(main) {
  main.innerHTML = `
    ${pageHeader('Brands', 'Manage brand listings shown on the storefront', btnPrimary('Add Brand','plus','add-brand-btn'))}
    ${tableCard('brands-table-inner')}
  `;
  renderDataTable('brands-table-inner', {
    title: 'All Brands', subtitle: `${DATA.brands.length} brands`, data: DATA.brands, searchKeys:['name'],
    filters:[{key:'status', label:'Status', options:['Active','Inactive']}],
    columns: [
      { label:'Brand', render: b => `<div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-ink-100 dark:bg-ink-800 flex items-center justify-center overflow-hidden flex-shrink-0">
            ${b.logoUrl ? `<img src="${b.logoUrl}" class="w-full h-full object-contain" alt="">` : icon('tag','w-4 h-4 text-ink-400')}
          </div>
          <span class="font-medium text-ink-800 dark:text-ink-100">${b.name}</span>
        </div>` },
      { label:'Products', render: b => fmtNum(b.products) },
      { label:'Featured', render: b => b.featured ? `<span class="text-amber-500">${icon('star','w-4 h-4 inline fill-current')}</span>` : '—' },
      { label:'Status', render: b => statusBadge(b.status) },
      { label:'', render: b => `
          <input type="file" accept="image/*" data-brand-logo-input="${b.id}" class="hidden">
          <button data-stop-row-click data-upload-brand-logo="${b.id}" class="text-xs font-medium text-brand-600 hover:underline mr-3">${b.logoUrl ? 'Change Logo' : 'Add Logo'}</button>
          <button data-stop-row-click data-toggle-brand="${b.id}" class="text-xs font-medium text-brand-600 hover:underline">${b.status==='Active'?'Deactivate':'Activate'}</button>
        ` },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-toggle-brand]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        const b = DATA.brands.find(x=>x.id===btn.dataset.toggleBrand);
        await api('PATCH', '/brands/'+b.id, { status: b.status==='Active'?'Inactive':'Active' });
        toast('Brand updated'); await refreshBrands(); navigateTo('brands');
      })));
      container.querySelectorAll('[data-upload-brand-logo]').forEach(btn => btn.addEventListener('click', () => {
        container.querySelector(`[data-brand-logo-input="${btn.dataset.uploadBrandLogo}"]`).click();
      }));
      container.querySelectorAll('[data-brand-logo-input]').forEach(input => input.addEventListener('change', withErr(async () => {
        const file = input.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('logo', file);
        const res = await fetch('/admin/api/brands/'+input.dataset.brandLogoInput+'/logo', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
        if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Logo upload failed'); }
        toast('Brand logo updated'); await refreshBrands(); navigateTo('brands');
      })));
    }
  });
  document.getElementById('add-brand-btn').addEventListener('click', () => {
    openModal({ title:'Add New Brand', body:`<div class="space-y-4">
        ${formField('Brand Name','text','e.g. ToughEdge','','nb-name')}
        <div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">Logo (optional)</label>
          <input type="file" id="nb-logo" accept="image/*" class="w-full text-sm text-ink-600 dark:text-ink-300 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 dark:file:bg-ink-700 file:text-ink-700 dark:file:text-ink-200 file:text-xs file:font-medium">
        </div>
      </div>`,
      confirmLabel:'Create Brand', onConfirm: withErr(async () => {
        const created = await api('POST', '/brands', { name: document.getElementById('nb-name').value });
        const file = document.getElementById('nb-logo').files[0];
        if (file) {
          const fd = new FormData();
          fd.append('logo', file);
          const res = await fetch('/admin/api/brands/'+created.id+'/logo', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
          if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Logo upload failed'); }
        }
        closeModal(); toast('Brand created'); await refreshBrands(); navigateTo('brands');
      }) });
  });
};

/* ===== ORDERS ===== */
SECTION_RENDERERS.orders = async function(main) {
  main.innerHTML = `
    ${pageHeader('Orders', 'Track and fulfil orders across all sellers')}
    ${tableCard('orders-table-inner')}
  `;
  renderDataTable('orders-table-inner', {
    title:'All Orders', subtitle:`${DATA.orders.length} orders`, data: DATA.orders, searchKeys:['id','customerName'],
    filters: [
      { key:'status', label:'Status', options:['Pending','Confirmed','Packed','Shipped','Out for Delivery','Delivered','Cancelled','Returned'] },
      { key:'payment', label:'Payment', options:['Paid','Pending','Unpaid','Refunded'] },
    ],
    columns: [
      { label:'Order ID', render: o => `<span class="font-medium text-ink-800 dark:text-ink-100">${o.id}</span>` },
      { label:'Date', render: o => new Date(o.date).toLocaleDateString('en-IN',{day:'numeric',month:'short'}) },
      { label:'Customer', key:'customerName' },
      { label:'Items', render: o => o.items },
      { label:'Amount', render: o => fmtINR(o.amount) },
      { label:'Payment', render: o => statusBadge(o.payment) },
      { label:'Status', render: o => statusBadge(o.status) },
    ],
    rowClick: openOrderProfile,
  });
};

const ORDER_STATUSES = ['Pending','Confirmed','Packed','Shipped','Out for Delivery','Delivered','Cancelled','Returned'];
function openOrderProfile(o) {
  openDrawer(o.id, `
    <div class="grid grid-cols-2 gap-3 mb-5">${miniStat('Amount', fmtINR(o.amount))}${miniStat('Items', o.items)}</div>
    ${drawerSection('Details', `${drawerRow('Customer', o.customerName)}<div id="order-customer-phone"></div>${drawerRow('Payment', statusBadge(o.payment))}${drawerRow('Placed', new Date(o.date).toLocaleDateString('en-IN'))}`)}
    ${drawerSection('Delivery Address', `<div id="order-address-loading" class="text-xs text-ink-400">Loading address…</div><div id="order-address"></div>`)}
    ${drawerSection('Products & Sellers', `<div id="order-items-loading" class="text-xs text-ink-400">Loading order items…</div><div id="order-items-list" class="space-y-2"></div>`)}
    <div class="mt-4">${formSelect('Update Status', ORDER_STATUSES, ORDER_STATUSES.indexOf(o.status), 'order-status-select')}</div>
    <div class="flex gap-2.5 mt-6 pt-5 border-t border-ink-100 dark:border-ink-800">
      <button id="save-order-status-btn" class="flex-1 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl">Save Status</button>
    </div>
  `, true);
  document.getElementById('save-order-status-btn').addEventListener('click', withErr(async () => {
    const status = document.getElementById('order-status-select').value;
    await api('PATCH', '/orders/'+o.id, { status });
    closeModal(); toast('Order status updated'); await refreshOrders(); navigateTo('orders');
  }));
  (async () => {
    try {
      const full = await api('GET', '/orders/'+o.id);
      const phoneEl = document.getElementById('order-customer-phone');
      if (phoneEl && full.customerPhone) phoneEl.innerHTML = drawerRow('Phone', full.customerPhone);

      const addrLoading = document.getElementById('order-address-loading');
      const addrEl = document.getElementById('order-address');
      if (addrLoading) addrLoading.remove();
      if (addrEl) {
        addrEl.innerHTML = full.address ? `
          <div class="px-3 py-2 rounded-lg border border-ink-200 dark:border-ink-700 text-sm text-ink-700 dark:text-ink-200">
            <div class="font-medium text-ink-900 dark:text-white">${full.address.contactName || o.customerName}</div>
            <div class="mt-0.5">${full.address.line}</div>
            <div>${full.address.city}, ${full.address.state} - ${full.address.pincode}</div>
          </div>` : `<p class="text-xs text-ink-400">No address on file for this order.</p>`;
      }

      const list = document.getElementById('order-items-list');
      const loading = document.getElementById('order-items-loading');
      if (loading) loading.remove();
      if (list) {
        list.innerHTML = (full.items || []).map(it => `
          <div class="px-3 py-2 rounded-lg border border-ink-200 dark:border-ink-700">
            <div class="text-sm font-medium text-ink-900 dark:text-white">${it.product}</div>
            <div class="text-xs text-ink-400">Sold by ${it.seller} · Qty ${it.quantity} · ${fmtINR(it.unit_price)} each · ${fmtINR(it.line_total)} total</div>
          </div>`).join('') || `<p class="text-xs text-ink-400">No items found.</p>`;
      }
    } catch (e) {
      const loading = document.getElementById('order-items-loading');
      if (loading) loading.textContent = 'Could not load order items.';
      const addrLoading = document.getElementById('order-address-loading');
      if (addrLoading) addrLoading.textContent = 'Could not load address.';
    }
  })();
}

/* ===== RETURNS & REFUNDS ===== */
SECTION_RENDERERS.returns = async function(main) {
  const pending = DATA.returns.filter(r=>r.status==='Pending').length;
  main.innerHTML = `
    ${pageHeader('Returns & Refunds', 'Review and action return and refund requests')}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Requests', value: fmtNum(DATA.returns.length), icon:'undo-2', accent:'brand'})}
      ${kpiCard({label:'Pending Review', value: fmtNum(pending), icon:'clock', accent:'amber'})}
      ${kpiCard({label:'Approved', value: fmtNum(DATA.returns.filter(r=>r.status==='Approved').length), icon:'check-circle', accent:'emerald'})}
      ${kpiCard({label:'Refund Value', value: fmtINR(DATA.returns.reduce((a,r)=>a+r.amount,0),{compact:true}), icon:'indian-rupee', accent:'rose'})}
    </div>
    ${tableCard('returns-table-inner')}
  `;
  renderDataTable('returns-table-inner', {
    title:'Return / Refund Requests', subtitle:`${DATA.returns.length} requests`, data: DATA.returns, searchKeys:['id','orderId','customerName'],
    filters: [{key:'type', label:'Type', options:['Return','Refund']}, {key:'status', label:'Status', options:['Pending','Approved','Rejected']}],
    columns: [
      { label:'Request ID', key:'id' }, { label:'Order', key:'orderId' }, { label:'Customer', key:'customerName' },
      { label:'Amount', render: r => fmtINR(r.amount) }, { label:'Reason', key:'reason' }, { label:'Type', key:'type' },
      { label:'Status', render: r => statusBadge(r.status) },
      { label:'', render: r => r.status==='Pending' ? `<div class="flex gap-1.5"><button data-stop-row-click data-approve-return="${r.id}" class="text-xs font-medium text-emerald-600 hover:underline">Approve</button><button data-stop-row-click data-reject-return="${r.id}" class="text-xs font-medium text-rose-600 hover:underline">Reject</button></div>` : '' },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-approve-return]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        await api('PATCH', '/returns/'+btn.dataset.approveReturn, { status:'Approved' });
        toast('Return approved'); await refreshReturns(); navigateTo('returns');
      })));
      container.querySelectorAll('[data-reject-return]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        await api('PATCH', '/returns/'+btn.dataset.rejectReturn, { status:'Rejected' });
        toast('Return rejected','warn'); await refreshReturns(); navigateTo('returns');
      })));
    }
  });
};

/* ===== COMMISSION MANAGEMENT ===== */
SECTION_RENDERERS.commission = async function(main) {
  const sellers = unwrap(DATA.commission.sellers);
  main.innerHTML = `
    ${pageHeader('Commission Management', 'Configure commission rates and track marketplace earnings')}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      ${kpiCard({label:'Marketplace Earnings', value: fmtINR(DATA.commission.total_earnings,{compact:true}), icon:'indian-rupee', accent:'brand'})}
      ${kpiCard({label:'Avg. Commission Rate', value: DATA.commission.avg_commission_rate+'%', icon:'percent', accent:'sky'})}
      ${kpiCard({label:'Commission Tiers', value: DATA.commission.tiers.length, icon:'layers', accent:'emerald'})}
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
      <div class="lg:col-span-3">${card(`<div class="p-5"><h3 class="font-semibold text-ink-900 dark:text-white mb-4">Commission Tiers</h3>
        <div class="space-y-3">${DATA.commission.tiers.map(t => `
          <div class="flex items-center justify-between">
            <span class="text-sm text-ink-600 dark:text-ink-300">${t.name}</span>
            <div class="flex items-center gap-2"><input data-tier-input="${t.id}" type="number" step="0.1" value="${t.percentage}" class="w-20 px-2 py-1 text-sm rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 text-right"><span class="text-sm text-ink-400">%</span><button data-tier-save="${t.id}" class="text-xs font-medium text-brand-600 hover:underline">Save</button></div>
          </div>`).join('')}</div>
      </div>`)}</div>
    </div>
    ${tableCard('commission-table-inner')}
  `;
  main.querySelectorAll('[data-tier-save]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const id = btn.dataset.tierSave;
    const input = main.querySelector(`[data-tier-input="${id}"]`);
    await api('PATCH', '/commission/tiers/'+id, { percentage: parseFloat(input.value) });
    toast('Tier updated'); await refreshCommission(); navigateTo('commission');
  })));
  renderDataTable('commission-table-inner', {
    title:'Seller Commission Rates', subtitle:'Commission percentage configured per seller', data: sellers, searchKeys:['store'],
    columns: [
      { label:'Seller', render: s => `<div class="flex items-center gap-3">${avatarChip(s.store)}<span class="font-medium">${s.store}</span></div>` },
      { label:'Revenue', render: s => fmtINR(s.revenue,{compact:true}) },
      { label:'Commission %', render: s => `<input data-comm-input="${s.id}" type="number" step="0.1" value="${s.commissionPct}" class="w-20 px-2 py-1 text-sm rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800">` },
      { label:'Marketplace Earning', render: s => fmtINR(s.marketplaceEarning,{compact:true}) },
      { label:'', render: s => `<button data-stop-row-click data-comm-save="${s.id}" class="text-xs font-medium text-brand-600 hover:underline">Save</button>` },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-comm-save]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        const id = btn.dataset.commSave;
        const input = container.querySelector(`[data-comm-input="${id}"]`);
        await api('PATCH', '/commission/sellers/'+id, { commission_pct: parseFloat(input.value) });
        toast('Commission rate saved'); await refreshCommission(); await refreshSellers(); navigateTo('commission');
      })));
    }
  });
};

/* ===== SETTLEMENT MANAGEMENT ===== */
SECTION_RENDERERS.settlements = async function(main) {
  const paid = DATA.settlements.filter(s=>s.status==='Paid');
  const pending = DATA.settlements.filter(s=>s.status==='Pending');
  const failed = DATA.settlements.filter(s=>s.status==='Failed');
  main.innerHTML = `
    ${pageHeader('Settlement Management', 'Track payouts to sellers and resolve failed settlements')}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Paid', value: fmtINR(paid.reduce((a,s)=>a+s.amount,0),{compact:true}), icon:'check-circle', accent:'emerald'})}
      ${kpiCard({label:'Pending', value: fmtNum(pending.length), sub: fmtINR(pending.reduce((a,s)=>a+s.amount,0),{compact:true})+' total', icon:'clock', accent:'amber'})}
      ${kpiCard({label:'Failed', value: fmtNum(failed.length), icon:'x-circle', accent:'rose'})}
      ${kpiCard({label:'Total Sellers Paid', value: fmtNum(paid.length), icon:'users', accent:'brand'})}
    </div>
    ${tableCard('settlements-table-inner')}
  `;
  renderDataTable('settlements-table-inner', {
    title:'Settlement Requests', subtitle:`${DATA.settlements.length} settlement records`, data: DATA.settlements, searchKeys:['sellerName','id'],
    filters:[{key:'status', label:'Status', options:['Paid','Pending','Failed']}, {key:'mode', label:'Gateway', options:['Razorpay Route','Cashfree Easy Split','Manual Bank Transfer']}],
    columns: [
      { label:'Settlement ID', key:'id' },
      { label:'Seller', render: s => `<span class="truncate max-w-[160px] inline-block">${s.sellerName}</span>` },
      { label:'Order', render: s => s.orderId || '—' },
      { label:'Amount', render: s => fmtINR(s.amount) }, { label:'Gateway', key:'mode' },
      { label:'Date', render: s => new Date(s.date).toLocaleDateString('en-IN',{day:'numeric',month:'short'}) },
      { label:'UTR / Transfer ID', render: s => `<span class="font-mono text-xs text-ink-400">${s.utr||s.razorpayTransferId||'—'}</span>` },
      { label:'Status', render: s => statusBadge(s.status) },
      { label:'', render: s => s.status!=='Paid' ? `<button data-stop-row-click data-mark-paid="${s.id}" class="text-xs font-medium text-brand-600 hover:underline">Mark Paid</button>` : '' },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-mark-paid]').forEach(btn => btn.addEventListener('click', () => {
        const id = btn.dataset.markPaid;
        openModal({
          title: 'Mark Settlement Paid',
          body: `<p class="text-xs text-ink-400 mb-3">Enter the bank UTR / reference number for the transfer you made to this seller.</p>${formField('UTR / Reference Number','text','e.g. UTR2026081800123','', 'settlement-utr')}`,
          confirmLabel: 'Mark Paid',
          onConfirm: withErr(async () => {
            const utr = document.getElementById('settlement-utr').value.trim();
            if (!utr) { toast('Enter a UTR / reference number', 'warn'); return; }
            await api('POST', '/settlements/'+id+'/mark-paid', { utr });
            closeModal(); toast('Settlement marked as paid'); await refreshSettlements(); navigateTo('settlements');
          })
        });
      }));
    }
  });
};

/* ===== COUPONS & OFFERS ===== */
SECTION_RENDERERS.coupons = async function(main) {
  main.innerHTML = `
    ${pageHeader('Coupons & Offers', 'Create and manage promotional discount codes', btnPrimary('Create Coupon','plus','add-coupon-btn'))}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      ${DATA.coupons.map(c => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="px-3 py-1.5 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-mono font-bold text-sm">${c.code}</div>
            ${statusBadge(c.status)}
          </div>
          <p class="text-2xl font-bold text-ink-900 dark:text-white">${c.type==='Percentage' ? c.value+'%' : '₹'+c.value} <span class="text-sm font-normal text-ink-400">off</span></p>
          <p class="text-xs text-ink-400 mt-1">Min. order ${fmtINR(c.min_order)}</p>
          <div class="mt-3">${progressBar(c.usage_limit ? Math.round(c.used_count/c.usage_limit*100) : 0, (c.used_count/(c.usage_limit||1)) > 0.8 ? 'amber' : 'brand')}<p class="text-xs text-ink-400 mt-1.5">${fmtNum(c.used_count)} / ${fmtNum(c.usage_limit)} used</p></div>
          <p class="text-xs text-ink-400 mt-3 flex items-center gap-1.5">${icon('calendar','w-3.5 h-3.5')}${c.expires_at ? 'Expires '+new Date(c.expires_at).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}) : 'No expiry'}</p>
          <div class="flex gap-2 mt-4">
            <button data-edit-coupon="${c.code}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">Edit</button>
            <button data-toggle-coupon="${c.code}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${c.status==='Active'?'Deactivate':'Activate'}</button>
            <button data-delete-coupon="${c.code}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">Delete</button>
          </div>
        </div>`).join('')}
    </div>`;
  document.getElementById('add-coupon-btn').addEventListener('click', () => {
    openModal({
      title:'Create New Coupon', wide:true,
      body:`<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ${formField('Coupon Code','text','e.g. SUMMER25','','nco-code')}
        ${formSelect('Discount Type',['Percentage','Flat'], 0, 'nco-type')}
        ${formField('Discount Value','number','e.g. 10','','nco-value')}
        ${formField('Minimum Order Value (₹)','number','e.g. 500','','nco-min')}
        ${formField('Usage Limit','number','e.g. 1000','1000','nco-limit')}
        ${formField('Expiry Date','date','','','nco-expiry')}
      </div>`,
      confirmLabel:'Create Coupon', onConfirm: withErr(async () => {
        await api('POST', '/coupons', {
          code: document.getElementById('nco-code').value.toUpperCase(),
          type: document.getElementById('nco-type').value,
          value: parseFloat(document.getElementById('nco-value').value||0),
          min_order: parseFloat(document.getElementById('nco-min').value||0),
          usage_limit: parseInt(document.getElementById('nco-limit').value||0),
          expires_at: document.getElementById('nco-expiry').value || null,
        });
        closeModal(); toast('Coupon created successfully'); await refreshCoupons(); navigateTo('coupons');
      })
    });
  });
  main.querySelectorAll('[data-edit-coupon]').forEach(btn => btn.addEventListener('click', () => {
    const c = DATA.coupons.find(x=>x.code===btn.dataset.editCoupon);
    openModal({
      title:'Edit Coupon', wide:true,
      body:`<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ${formField('Coupon Code','text','e.g. SUMMER25',c.code,'eco-code')}
        ${formSelect('Discount Type',['Percentage','Flat'], c.type==='Flat'?1:0, 'eco-type')}
        ${formField('Discount Value','number','e.g. 10',c.value,'eco-value')}
        ${formField('Minimum Order Value (₹)','number','e.g. 500',c.min_order,'eco-min')}
        ${formField('Usage Limit','number','e.g. 1000',c.usage_limit,'eco-limit')}
        ${formField('Expiry Date','date','',c.expires_at ? c.expires_at.substring(0,10) : '','eco-expiry')}
      </div>`,
      confirmLabel:'Save Changes', onConfirm: withErr(async () => {
        await api('PATCH', '/coupons/'+c.code, {
          code: document.getElementById('eco-code').value.toUpperCase(),
          type: document.getElementById('eco-type').value,
          value: parseFloat(document.getElementById('eco-value').value||0),
          min_order: parseFloat(document.getElementById('eco-min').value||0),
          usage_limit: parseInt(document.getElementById('eco-limit').value||0),
          expires_at: document.getElementById('eco-expiry').value || null,
          status: c.status,
        });
        closeModal(); toast('Coupon updated'); await refreshCoupons(); navigateTo('coupons');
      })
    });
  }));
  main.querySelectorAll('[data-toggle-coupon]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const c = DATA.coupons.find(x=>x.code===btn.dataset.toggleCoupon);
    await api('PATCH', '/coupons/'+c.code, { code:c.code, type:c.type, value:c.value, min_order:c.min_order, usage_limit:c.usage_limit, status: c.status==='Active'?'Inactive':'Active' });
    toast('Coupon updated'); await refreshCoupons(); navigateTo('coupons');
  })));
  main.querySelectorAll('[data-delete-coupon]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    if (!confirm('Delete this coupon?')) return;
    await api('DELETE', '/coupons/'+btn.dataset.deleteCoupon);
    toast('Coupon deleted'); await refreshCoupons(); navigateTo('coupons');
  })));
};

/* ===== BANNER MANAGEMENT ===== */
SECTION_RENDERERS.banners = async function(main) {
  main.innerHTML = `
    ${pageHeader('Banner Management', 'Manage homepage banners, offers and slider images', btnPrimary('Add Banner','plus','add-banner-btn'))}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      ${DATA.banners.map(b => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card overflow-hidden">
          <div class="h-32 flex items-center justify-center relative" style="background:${b.bg_color}">
            ${b.image_url ? `<img src="${b.image_url}" class="absolute inset-0 w-full h-full object-cover">` : icon('image','w-8 h-8 text-white/70')}
          </div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-1"><p class="font-medium text-sm ${b.title ? 'text-ink-800 dark:text-ink-100' : 'text-ink-400 italic'} truncate">${b.title || 'Untitled banner'}</p>${statusBadge(b.status)}</div>
            <p class="text-xs text-ink-400">${b.type}${b.subtitle ? ' · '+b.subtitle : ''}</p>
            <input type="file" accept="image/*" data-banner-image-input="${b.id}" class="hidden">
            <div class="flex gap-2 mt-3">
              <button data-upload-banner-image="${b.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${b.image_url?'Change Image':'Upload Image'}</button>
              ${b.image_url ? `<button data-remove-banner-image="${b.id}" class="py-1.5 px-2.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">${icon('trash-2','w-3.5 h-3.5')}</button>` : ''}
            </div>
            <div class="flex gap-2 mt-2">
              <button data-toggle-banner="${b.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${b.status==='Active'?'Deactivate':'Activate'}</button>
              <button data-delete-banner="${b.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">Remove</button>
            </div>
          </div>
        </div>`).join('')}
    </div>`;
  document.getElementById('add-banner-btn').addEventListener('click', () => {
    openModal({ title:'Add New Banner', body:`<div class="space-y-4">
      ${formField('Banner Title','text','e.g. Monsoon Tool Sale','','nba-title')}
      ${formField('Subtitle','text','e.g. Up to 30% off','','nba-subtitle')}
      ${formSelect('Banner Type',['Homepage Banner','Offer Banner','Slider Image'], 0, 'nba-type')}
      <div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">Banner Image (optional)</label>
        <input type="file" id="nba-image" accept="image/*" class="w-full text-sm text-ink-600 dark:text-ink-300 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 dark:file:bg-ink-700 file:text-ink-700 dark:file:text-ink-200 file:text-xs file:font-medium">
      </div>
    </div>`, confirmLabel:'Create Banner', onConfirm: withErr(async () => {
      const created = await api('POST', '/banners', { title: document.getElementById('nba-title').value, subtitle: document.getElementById('nba-subtitle').value, type: document.getElementById('nba-type').value });
      const file = document.getElementById('nba-image').files[0];
      if (file) {
        const fd = new FormData();
        fd.append('image', file);
        const res = await fetch('/admin/api/banners/'+created.id+'/image', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
        if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Image upload failed'); }
      }
      closeModal(); toast('Banner created successfully'); await refreshBanners(); navigateTo('banners');
    }) });
  });
  main.querySelectorAll('[data-upload-banner-image]').forEach(btn => btn.addEventListener('click', () => {
    main.querySelector(`[data-banner-image-input="${btn.dataset.uploadBannerImage}"]`).click();
  }));
  main.querySelectorAll('[data-banner-image-input]').forEach(input => input.addEventListener('change', withErr(async () => {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('image', file);
    const res = await fetch('/admin/api/banners/'+input.dataset.bannerImageInput+'/image', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
    if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Image upload failed'); }
    toast('Banner image updated'); await refreshBanners(); navigateTo('banners');
  })));
  main.querySelectorAll('[data-remove-banner-image]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/banners/'+btn.dataset.removeBannerImage+'/image');
    toast('Banner image removed'); await refreshBanners(); navigateTo('banners');
  })));
  main.querySelectorAll('[data-toggle-banner]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const b = DATA.banners.find(x=>String(x.id)===btn.dataset.toggleBanner);
    await api('PATCH', '/banners/'+b.id, { title:b.title, type:b.type, status: b.status==='Active'?'Inactive':'Active' });
    toast('Banner updated'); await refreshBanners(); navigateTo('banners');
  })));
  main.querySelectorAll('[data-delete-banner]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    if (!confirm('Remove this banner?')) return;
    await api('DELETE', '/banners/'+btn.dataset.deleteBanner);
    toast('Banner removed'); await refreshBanners(); navigateTo('banners');
  })));
};

/* ===== ONBOARDING SCREENS ===== */
SECTION_RENDERERS.onboarding = async function(main) {
  const slidesHtml = DATA.onboardingSlides.length === 0
    ? card('<div class="p-8 text-center text-sm text-ink-400">No onboarding slides yet — the app falls back to its built-in default slides until you add some here.</div>')
    : `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">${DATA.onboardingSlides.map((s, i) => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card overflow-hidden">
          <div class="h-32 flex items-center justify-center relative bg-ink-800">
            ${s.image_url ? `<img src="${s.image_url}" class="absolute inset-0 w-full h-full object-cover">` : icon('image','w-8 h-8 text-white/70')}
            <span class="absolute top-2 left-2 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-ink-900/70 text-white">#${i+1}</span>
          </div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-1"><p class="font-medium text-sm text-ink-800 dark:text-ink-100 truncate">${s.title}</p>${statusBadge(s.status)}</div>
            <p class="text-xs text-ink-400 truncate">${s.subtitle||''}</p>
            <input type="file" accept="image/*" data-onboarding-image-input="${s.id}" class="hidden">
            <div class="flex gap-2 mt-3">
              <button data-upload-onboarding-image="${s.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${s.image_url?'Change Image':'Upload Image'}</button>
              ${s.image_url ? `<button data-remove-onboarding-image="${s.id}" class="py-1.5 px-2.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">${icon('trash-2','w-3.5 h-3.5')}</button>` : ''}
            </div>
            <div class="flex gap-2 mt-2">
              <button data-toggle-onboarding="${s.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${s.status==='Active'?'Deactivate':'Activate'}</button>
              <button data-delete-onboarding="${s.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">Remove</button>
            </div>
          </div>
        </div>`).join('')}</div>`;
  main.innerHTML = `
    ${pageHeader('Onboarding Screens', 'Manage the welcome slides shown the first time a customer opens the app', btnPrimary('Add Slide','plus','add-onboarding-btn'))}
    ${slidesHtml}`;
  document.getElementById('add-onboarding-btn').addEventListener('click', () => {
    openModal({ title:'Add Onboarding Slide', body:`<div class="space-y-4">
      ${formField('Title','text','e.g. Everything Hardware in One Place','','nos-title')}
      ${formField('Subtitle','text','e.g. Browse thousands of products from verified suppliers','','nos-subtitle')}
      <div><label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">Slide Image (optional)</label>
        <input type="file" id="nos-image" accept="image/*" class="w-full text-sm text-ink-600 dark:text-ink-300 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-ink-100 dark:file:bg-ink-700 file:text-ink-700 dark:file:text-ink-200 file:text-xs file:font-medium">
      </div>
    </div>`, confirmLabel:'Create Slide', onConfirm: withErr(async () => {
      const title = document.getElementById('nos-title').value.trim();
      if (!title) throw new Error('Title is required');
      const created = await api('POST', '/onboarding-slides', { title, subtitle: document.getElementById('nos-subtitle').value });
      const file = document.getElementById('nos-image').files[0];
      if (file) {
        const fd = new FormData();
        fd.append('image', file);
        const res = await fetch('/admin/api/onboarding-slides/'+created.id+'/image', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
        if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Image upload failed'); }
      }
      closeModal(); toast('Onboarding slide created'); await refreshOnboardingSlides(); navigateTo('onboarding');
    }) });
  });
  main.querySelectorAll('[data-upload-onboarding-image]').forEach(btn => btn.addEventListener('click', () => {
    main.querySelector(`[data-onboarding-image-input="${btn.dataset.uploadOnboardingImage}"]`).click();
  }));
  main.querySelectorAll('[data-onboarding-image-input]').forEach(input => input.addEventListener('change', withErr(async () => {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('image', file);
    const res = await fetch('/admin/api/onboarding-slides/'+input.dataset.onboardingImageInput+'/image', { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken()}, body: fd });
    if (!res.ok) { const d = await res.json().catch(()=>({})); throw new Error(d.message || 'Image upload failed'); }
    toast('Slide image updated'); await refreshOnboardingSlides(); navigateTo('onboarding');
  })));
  main.querySelectorAll('[data-remove-onboarding-image]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    await api('DELETE', '/onboarding-slides/'+btn.dataset.removeOnboardingImage+'/image');
    toast('Slide image removed'); await refreshOnboardingSlides(); navigateTo('onboarding');
  })));
  main.querySelectorAll('[data-toggle-onboarding]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    const s = DATA.onboardingSlides.find(x=>String(x.id)===btn.dataset.toggleOnboarding);
    await api('PATCH', '/onboarding-slides/'+s.id, { title:s.title, status: s.status==='Active'?'Inactive':'Active' });
    toast('Slide updated'); await refreshOnboardingSlides(); navigateTo('onboarding');
  })));
  main.querySelectorAll('[data-delete-onboarding]').forEach(btn => btn.addEventListener('click', withErr(async () => {
    if (!confirm('Remove this onboarding slide?')) return;
    await api('DELETE', '/onboarding-slides/'+btn.dataset.deleteOnboarding);
    toast('Slide removed'); await refreshOnboardingSlides(); navigateTo('onboarding');
  })));
};

/* ===== REPORTS & ANALYTICS ===== */
SECTION_RENDERERS.reports = async function(main) {
  const reportTypes = [
    { name:'Revenue Report', icon:'indian-rupee', desc:'GMV, net revenue and growth trends' },
    { name:'Sales Report', icon:'trending-up', desc:'Order volume and conversion metrics' },
    { name:'Seller Report', icon:'store', desc:'Seller performance and rankings' },
    { name:'Customer Report', icon:'users', desc:'Acquisition, retention and LTV' },
    { name:'Commission Report', icon:'percent', desc:'Marketplace earnings breakdown' },
    { name:'Settlement Report', icon:'banknote', desc:'Payout history and pending dues' },
  ];
  main.innerHTML = `
    ${pageHeader('Reports & Analytics', 'Deep dive into marketplace performance')}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      ${reportTypes.map(r => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card p-5">
          <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-3">${icon(r.icon,'w-5 h-5')}</div>
          <p class="font-semibold text-ink-900 dark:text-white">${r.name}</p><p class="text-xs text-ink-400 mt-1">${r.desc}</p>
          <div class="flex gap-2 mt-4"><button data-export="${r.name}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${icon('file-text','w-3.5 h-3.5 inline mr-1')}Export</button></div>
        </div>`).join('')}
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      ${card(`<div class="p-5 pb-0"><h3 class="font-semibold text-ink-900 dark:text-white">Revenue vs Commission</h3></div><div class="p-5"><canvas id="chart-rep1" height="240"></canvas></div>`)}
      ${card(`<div class="p-5 pb-0"><h3 class="font-semibold text-ink-900 dark:text-white">Top Sellers by Revenue</h3></div><div class="p-5"><canvas id="chart-rep2" height="240"></canvas></div>`)}
    </div>`;
  const REPORT_EXPORTERS = {
    'Revenue Report': () => downloadCsv('revenue-report.csv', (DATA.monthlyRevenue||[]).map(m => ({ month: m.month, revenue: m.revenue, commission: m.commission }))),
    'Sales Report': () => downloadCsv('sales-report.csv', DATA.orders.map(o => ({ order_id: o.id, date: o.date, customer: o.customerName, items: o.items, amount: o.amount, payment: o.payment, status: o.status }))),
    'Seller Report': () => downloadCsv('seller-report.csv', DATA.sellers.map(s => ({ seller_id: s.id, store: s.store, owner: s.name, city: s.city, revenue: s.revenue, commission_pct: s.commissionPct, rating: s.rating, status: s.status }))),
    'Customer Report': () => downloadCsv('customer-report.csv', DATA.customers.map(c => ({ customer_id: c.id, name: c.name, email: c.email, city: c.city, orders: c.orders, total_spend: c.totalSpend, status: c.status }))),
    'Commission Report': () => downloadCsv('commission-report.csv', unwrap(DATA.commission.sellers).map(s => ({ seller: s.store, revenue: s.revenue, commission_pct: s.commissionPct, marketplace_earning: s.marketplaceEarning }))),
    'Settlement Report': () => downloadCsv('settlement-report.csv', DATA.settlements.map(s => ({ settlement_id: s.id, seller: s.sellerName, amount: s.amount, mode: s.mode, status: s.status, utr: s.utr||'', date: s.date }))),
  };
  main.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => REPORT_EXPORTERS[b.dataset.export]?.()));

  const [revCommission, topSellers] = await Promise.all([
    api('GET', '/reports/revenue-vs-commission'), api('GET', '/reports/top-sellers'),
  ]);
  const cc = chartColors();
  destroyChart('rep1');
  chartInstances['rep1'] = new Chart(document.getElementById('chart-rep1'), { type:'line',
    data: { labels: revCommission.map(m=>m.month), datasets:[
      { label:'Revenue', data: revCommission.map(m=>m.revenue), borderColor:'#3868f6', backgroundColor:'transparent', tension:.4, pointRadius:0, borderWidth:2.5 },
      { label:'Commission', data: revCommission.map(m=>m.commission), borderColor:'#f59e0b', backgroundColor:'transparent', tension:.4, pointRadius:0, borderWidth:2.5 },
    ]}, options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top', align:'end', labels:{color:cc.text}}},
      scales: { y:{ grid:{color:cc.grid}, ticks:{color:cc.text, callback:v=>fmtINR(v,{compact:true})} }, x:{ grid:{display:false}, ticks:{color:cc.text} } } } });
  destroyChart('rep2');
  chartInstances['rep2'] = new Chart(document.getElementById('chart-rep2'), { type:'bar',
    data: { labels: topSellers.map(s=>s.store.length>16?s.store.slice(0,16)+'…':s.store), datasets:[{ data: topSellers.map(s=>s.revenue), backgroundColor:'#5f8fff', borderRadius:6, maxBarThickness:24 }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{callbacks:{label:c=>fmtINR(c.raw,{compact:true})}}},
      scales: { y:{ grid:{color:cc.grid}, ticks:{color:cc.text, callback:v=>fmtINR(v,{compact:true})} }, x:{ grid:{display:false}, ticks:{color:cc.text, font:{size:10}} } } } });
};

/* ===== GST & TAX SETTINGS ===== */
SECTION_RENDERERS.gst = async function(main) {
  const g = DATA.gst;
  main.innerHTML = `
    ${pageHeader('GST & Tax Settings', 'Configure tax rules, HSN codes and invoice settings', btnPrimary('Add HSN Code','plus','add-hsn-btn'))}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
      ${card(`<div class="p-5"><p class="text-sm font-medium text-ink-500 dark:text-ink-400 mb-3">Default GST Rate</p>
        <select id="gst-default-rate" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring">
          ${[5,12,18,28].map(r=>`<option value="${r}" ${g.default_gst_rate==r?'selected':''}>${r}%</option>`).join('')}
        </select></div>`)}
      ${card(`<div class="p-5"><p class="text-sm font-medium text-ink-500 dark:text-ink-400 mb-3">Invoice Prefix</p>
        <input id="gst-invoice-prefix" type="text" value="${g.invoice_prefix}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring"></div>`)}
      ${card(`<div class="p-5"><p class="text-sm font-medium text-ink-500 dark:text-ink-400 mb-3">Tax Calculation</p>
        <div class="flex items-center justify-between"><span class="text-sm">Inclusive of GST</span>${toggleSwitch(g.gst_inclusive, 'gst-inclusive-toggle')}</div></div>`)}
    </div>
    <div class="flex justify-end mb-6"><button id="save-gst-settings-btn" class="${'inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl'}">Save Tax Settings</button></div>
    ${tableCard('hsn-table-inner')}
  `;
  document.getElementById('save-gst-settings-btn').addEventListener('click', withErr(async () => {
    await api('PATCH', '/gst/settings', {
      default_gst_rate: parseFloat(document.getElementById('gst-default-rate').value),
      invoice_prefix: document.getElementById('gst-invoice-prefix').value,
      gst_inclusive: document.getElementById('gst-inclusive-toggle').dataset.checked === 'true',
    });
    toast('GST settings saved'); await refreshGst(); navigateTo('gst');
  }));
  document.getElementById('add-hsn-btn').addEventListener('click', () => {
    openModal({ title:'Add HSN Code', body:`<div class="space-y-4">${formField('HSN Code','text','e.g. 8467','','hsn-code')}${formField('Description','text','e.g. Power-operated hand tools','','hsn-desc')}${formSelect('GST Rate',['5%','12%','18%','28%'],2,'hsn-rate')}</div>`,
      confirmLabel:'Add HSN Code', onConfirm: withErr(async () => {
        await api('POST', '/gst/hsn', { code: document.getElementById('hsn-code').value, description: document.getElementById('hsn-desc').value, gst_rate: parseFloat(document.getElementById('hsn-rate').value) });
        closeModal(); toast('HSN code added successfully'); await refreshGst(); navigateTo('gst');
      }) });
  });
  renderDataTable('hsn-table-inner', {
    title:'HSN Codes & Tax Rules', subtitle:'Tax rates applied per product classification', data: g.hsn_codes.map(h=>({...h})), searchKeys:['code','description'],
    columns: [
      { label:'HSN Code', render: h => `<span class="font-mono font-medium">${h.code}</span>` },
      { label:'Description', key:'description' },
      { label:'GST Rate', render: h => `<span class="px-2.5 py-1 rounded-lg bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 text-xs font-medium">${h.gst_rate}%</span>` },
    ],
  });
};

/* ===== SHIPPING CONFIGURATION ===== */
SECTION_RENDERERS.shipping = async function(main) {
  const s = DATA.shipping;
  main.innerHTML = `
    ${pageHeader('Shipping Configuration', 'Set up shipping zones, charges and weight rules')}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
      ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">COD Charges</p><div class="space-y-3">
        ${formField('COD Handling Fee (₹)','number', '', s.config.cod_fee, 'cod-fee')}
        <div class="flex items-center justify-between"><span class="text-sm">Enable COD</span>${toggleSwitch(s.config.cod_enabled, 'cod-enabled-toggle')}</div>
      </div></div>`)}
      ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Packaging Charges</p><div class="space-y-3">
        ${formField('Packaging Fee (₹)','number','', s.config.packaging_fee, 'packaging-fee')}
        <div class="flex items-center justify-between"><span class="text-sm">Apply on all orders</span>${toggleSwitch(s.config.packaging_apply_all, 'packaging-apply-toggle')}</div>
      </div></div>`)}
      ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Free Shipping Rule</p><div class="space-y-3">
        ${formField('Minimum Order Value (₹)','number','', s.config.free_shipping_min, 'free-ship-min')}
        <div class="flex items-center justify-between"><span class="text-sm">Enable Free Shipping</span>${toggleSwitch(s.config.free_shipping_enabled, 'free-ship-toggle')}</div>
      </div></div>`)}
    </div>
    <div class="flex justify-end mb-6">${btnPrimary('Save Shipping Config','check','save-shipping-btn')}</div>
    ${card(`<div class="p-5 pb-3 flex items-center justify-between"><div><h3 class="font-semibold text-ink-900 dark:text-white">Shipping Zones & Weight Rules</h3><p class="text-xs text-ink-400 mt-0.5">Charges vary by delivery zone</p></div>${btnPrimary('Add Zone','plus','add-zone-btn')}</div>
      <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-ink-400 border-t border-ink-100 dark:border-ink-800"><th class="font-medium py-2.5 px-5">Zone</th><th class="font-medium py-2.5 px-3">Weight Slab</th><th class="font-medium py-2.5 px-3">Charge</th><th class="font-medium py-2.5 px-3">Free Above</th><th class="font-medium py-2.5 px-3"></th></tr></thead>
        <tbody>${s.zones.map(z => `<tr class="border-t border-ink-100 dark:border-ink-800"><td class="py-3 px-5 font-medium text-ink-800 dark:text-ink-100">${z.zone}</td><td class="py-3 px-3 text-ink-500">${z.weight_slab}</td><td class="py-3 px-3 tnum">${fmtINR(z.charge)}</td><td class="py-3 px-3 tnum">${fmtINR(z.free_above)}</td><td class="py-3 px-3 text-right"><button data-edit-zone="${z.id}" class="text-xs font-medium text-brand-600 dark:text-brand-400">Edit</button></td></tr>`).join('') || `<tr><td colspan="5" class="py-6 px-5 text-center text-ink-400">No zones yet — add one to charge by delivery area.</td></tr>`}</tbody>
      </table></div>`)}
  `;
  document.getElementById('save-shipping-btn').addEventListener('click', withErr(async () => {
    await api('PATCH', '/shipping/config', {
      cod_enabled: document.getElementById('cod-enabled-toggle').dataset.checked === 'true',
      cod_fee: parseFloat(document.getElementById('cod-fee').value||0),
      packaging_fee: parseFloat(document.getElementById('packaging-fee').value||0),
      packaging_apply_all: document.getElementById('packaging-apply-toggle').dataset.checked === 'true',
      free_shipping_enabled: document.getElementById('free-ship-toggle').dataset.checked === 'true',
      free_shipping_min: parseFloat(document.getElementById('free-ship-min').value||0),
    });
    toast('Shipping configuration saved'); await refreshShipping(); navigateTo('shipping');
  }));

  const openZoneModal = (zone) => {
    openModal({
      title: zone ? 'Edit Zone' : 'Add Zone',
      body: `<div class="grid grid-cols-1 gap-4">
        ${formField('Zone Name','text','e.g. Zone A — Metro Cities', zone?.zone||'', 'zone-name')}
        ${formField('Weight Slab','text','e.g. Up to 5kg', zone?.weight_slab||'', 'zone-weight')}
        ${formField('Charge (₹)','number','', zone?.charge??'', 'zone-charge')}
        ${formField('Free Above (₹)','number','', zone?.free_above??'', 'zone-free-above')}
      </div>`,
      confirmLabel: zone ? 'Save Zone' : 'Add Zone',
      onConfirm: withErr(async () => {
        const payload = {
          zone: document.getElementById('zone-name').value,
          weight_slab: document.getElementById('zone-weight').value,
          charge: parseFloat(document.getElementById('zone-charge').value||0),
          free_above: parseFloat(document.getElementById('zone-free-above').value||0),
        };
        if (zone) {
          await api('PATCH', '/shipping/zones/'+zone.id, payload);
        } else {
          await api('POST', '/shipping/zones', payload);
        }
        closeModal(); toast(zone ? 'Zone updated' : 'Zone added'); await refreshShipping(); navigateTo('shipping');
      }),
    });
  };
  document.getElementById('add-zone-btn')?.addEventListener('click', () => openZoneModal(null));
  document.querySelectorAll('[data-edit-zone]').forEach(btn => btn.addEventListener('click', () => {
    const zone = s.zones.find(z => String(z.id) === btn.dataset.editZone);
    if (zone) openZoneModal(zone);
  }));
};

/* ===== PAYMENT GATEWAY CONFIGURATION ===== */
SECTION_RENDERERS.payments = async function(main) {
  const configs = { razorpay: null, cashfree: null };
  DATA.payments.forEach(c => configs[c.provider] = c);
  main.innerHTML = `
    ${pageHeader('Payment Gateway Configuration', 'Connect and manage payment gateways for checkout')}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
      ${integrationCard({ name:'Razorpay', logo:'RP', enabled: configs.razorpay?.enabled, testBtnId:'test-razorpay', saveBtnId:'save-razorpay',
        fields: [{label:'Key ID', placeholder:'rzp_live_••••••••', value: configs.razorpay?.key_id||'', id:'razorpay-key'},{label:'Secret Key', type:'password', placeholder: configs.razorpay?.has_secret ? '•••• (saved)' : '••••••••••••', id:'razorpay-secret'}] })}
      ${integrationCard({ name:'Cashfree', logo:'CF', enabled: configs.cashfree?.enabled, testBtnId:'test-cashfree', saveBtnId:'save-cashfree',
        fields: [{label:'Client ID', placeholder:'CF••••••••', value: configs.cashfree?.key_id||'', id:'cashfree-key'},{label:'Client Secret', type:'password', placeholder: configs.cashfree?.has_secret ? '•••• (saved)' : '••••••••••••', id:'cashfree-secret'}] })}
    </div>`;
  document.getElementById('save-razorpay').addEventListener('click', withErr(async () => {
    await api('PATCH', '/payments/razorpay', { key_id: document.getElementById('razorpay-key').value, secret: document.getElementById('razorpay-secret').value || undefined, enabled: true });
    toast('Razorpay settings saved'); await refreshPayments(); navigateTo('payments');
  }));
  document.getElementById('save-cashfree').addEventListener('click', withErr(async () => {
    await api('PATCH', '/payments/cashfree', { key_id: document.getElementById('cashfree-key').value, secret: document.getElementById('cashfree-secret').value || undefined, enabled: true });
    toast('Cashfree settings saved'); await refreshPayments(); navigateTo('payments');
  }));
  document.getElementById('test-razorpay').addEventListener('click', withErr(async () => { const r = await api('POST', '/payments/razorpay/test'); toast(r.message); }));
  document.getElementById('test-cashfree').addEventListener('click', withErr(async () => { const r = await api('POST', '/payments/cashfree/test'); toast(r.message); }));
};

/* ===== SHIPROCKET API INTEGRATION ===== */
SECTION_RENDERERS.shiprocket = async function(main) {
  const sr = DATA.shiprocket;
  main.innerHTML = `
    ${pageHeader('Shiprocket API Integration', 'Automate courier assignment, AWB generation and tracking', sr.connected ? `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-xs font-medium">${icon('check-circle','w-3.5 h-3.5')}Connected</span>` : `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-ink-100 dark:bg-ink-800 text-ink-500 text-xs font-medium">Not connected</span>`)}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2">${card(`<div class="p-5">
        <p class="font-semibold text-ink-900 dark:text-white mb-4">API Credentials</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          ${formField('API Email','email','warehouse@yourstore.com', sr.api_email||'', 'sr-email')}
          ${formField('Password','password', sr.has_password ? '•••• (saved)' : '••••••••••', '', 'sr-password')}
          ${formSelect('Default Courier',['Auto Recommend (Best Rate)','Delhivery','Bluedart','Ekart','XpressBees'], ['Auto Recommend (Best Rate)','Delhivery','Bluedart','Ekart','XpressBees'].indexOf(sr.default_courier), 'sr-courier')}
        </div>
        <p class="font-semibold text-ink-900 dark:text-white mt-6 mb-3">Pickup Location</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          ${formField('Warehouse','text','Primary Warehouse', sr.warehouse_name||'', 'sr-warehouse')}
          ${formField('Pickup Address','text','Industrial Area', sr.pickup_address||'', 'sr-address')}
        </div>
      </div>`)}</div>
      <div class="space-y-4">
        ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Automation</p><div class="space-y-3.5">
          <div class="flex items-center justify-between"><span class="text-sm text-ink-600 dark:text-ink-300">Auto Assign Courier</span>${toggleSwitch(sr.auto_assign_courier, 'sr-auto-assign')}</div>
          <div class="flex items-center justify-between"><span class="text-sm text-ink-600 dark:text-ink-300">Auto Generate AWB</span>${toggleSwitch(sr.auto_generate_awb, 'sr-auto-awb')}</div>
          <div class="flex items-center justify-between"><span class="text-sm text-ink-600 dark:text-ink-300">Generate Labels</span>${toggleSwitch(sr.generate_labels, 'sr-labels')}</div>
          <div class="flex items-center justify-between"><span class="text-sm text-ink-600 dark:text-ink-300">Live Tracking</span>${toggleSwitch(sr.live_tracking, 'sr-tracking')}</div>
        </div></div>`)}
        ${card(`<div class="p-5 space-y-2.5">
          <button id="sync-orders-btn" class="w-full flex justify-center items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-ink-800 border border-ink-200 dark:border-ink-700 text-sm font-medium rounded-xl">${icon('refresh-cw','w-4 h-4')}Sync Orders Now</button>
          <button id="test-shiprocket-btn" class="w-full flex justify-center items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-ink-800 border border-ink-200 dark:border-ink-700 text-sm font-medium rounded-xl">${icon('plug','w-4 h-4')}Test Connection</button>
          ${btnPrimary('Save Changes','check','save-shiprocket-btn').replace('inline-flex','flex w-full justify-center')}
        </div>`)}
      </div>
    </div>`;
  document.getElementById('save-shiprocket-btn').addEventListener('click', withErr(async () => {
    await api('PATCH', '/shiprocket', {
      api_email: document.getElementById('sr-email').value,
      api_password: document.getElementById('sr-password').value || undefined,
      default_courier: document.getElementById('sr-courier').value,
      warehouse_name: document.getElementById('sr-warehouse').value,
      pickup_address: document.getElementById('sr-address').value,
      auto_assign_courier: document.getElementById('sr-auto-assign').dataset.checked === 'true',
      auto_generate_awb: document.getElementById('sr-auto-awb').dataset.checked === 'true',
      generate_labels: document.getElementById('sr-labels').dataset.checked === 'true',
      live_tracking: document.getElementById('sr-tracking').dataset.checked === 'true',
    });
    toast('Shiprocket settings saved'); await refreshShiprocket(); navigateTo('shiprocket');
  }));
  document.getElementById('test-shiprocket-btn').addEventListener('click', withErr(async () => { const r = await api('POST', '/shiprocket/test'); toast(r.message, r.message.includes('verified successfully') ? 'success' : 'error'); await refreshShiprocket(); navigateTo('shiprocket'); }));
  document.getElementById('sync-orders-btn').addEventListener('click', withErr(async () => { const r = await api('POST', '/shiprocket/sync'); toast(r.message, 'info'); }));
};

/* ===== NOTIFICATION MANAGEMENT ===== */
SECTION_RENDERERS['notifications-config'] = async function(main) {
  const n = DATA.notifications;
  const s = DATA.settings;
  const iconFor = { email:'mail', sms:'message-square', whatsapp:'message-circle', push:'bell' };
  main.innerHTML = `
    ${pageHeader('Notification Management', 'Configure communication channels and message templates', btnPrimary('New Broadcast','send','broadcast-btn'))}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
      ${n.channels.map(c => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card p-5 flex items-center justify-between">
          <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">${icon(iconFor[c.key]||'bell','w-5 h-5')}</div><div><p class="font-medium text-ink-800 dark:text-ink-100">${c.name}</p><p class="text-xs text-ink-400">${c.description}</p></div></div>
          ${toggleSwitch(c.enabled, 'chan-'+c.key)}
        </div>`).join('')}
    </div>
    ${card(`<div class="p-5">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-ink-100 dark:bg-ink-800 flex items-center justify-center font-bold text-ink-600 dark:text-ink-300 text-sm">OS</div>
        <div><p class="font-semibold text-ink-900 dark:text-white">OneSignal (Push Notifications)</p><p class="text-xs text-ink-400">${s.onesignal_app_id ? 'App ID configured' : 'Not configured yet'}</p></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        ${formField('OneSignal App ID','text','xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', s.onesignal_app_id||'', 'os-app-id')}
        ${formField('REST API Key','password','••••••••••••','', 'os-api-key')}
      </div>
      <p class="text-xs text-ink-400 mt-2">From your OneSignal app's Settings &rarr; Keys &amp; IDs. Leave the API key blank to keep the current one.</p>
      <div class="mt-4 pt-4 border-t border-ink-100 dark:border-ink-800">
        <p class="text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">Send a test push (paste a subscription ID from a logged-in device)</p>
        <div class="flex gap-2">
          <input id="os-test-id" placeholder="Subscription ID" class="flex-1 px-3 py-2 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 dark:text-white focus-ring outline-none">
          <button id="os-test-btn" class="px-3.5 py-2 text-sm font-medium rounded-xl border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 flex-shrink-0">Send Test</button>
        </div>
      </div>
      <div class="flex justify-end mt-4">${btnPrimary('Save Changes','check','save-onesignal-btn')}</div>
    </div>`)}
    <div class="mt-6">
    ${card(`<div class="p-5 pb-3"><h3 class="font-semibold text-ink-900 dark:text-white">Message Templates</h3><p class="text-xs text-ink-400 mt-0.5">Customize automated messages</p></div>
      <div class="divide-y divide-ink-100 dark:divide-ink-800">${n.templates.map(t => `<div class="flex items-center justify-between px-5 py-3.5"><span class="text-sm font-medium text-ink-700 dark:text-ink-200 flex items-center gap-2">${t.name}${t.sms_template_id ? '<span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">SMS ON</span>' : ''}</span><button data-edit-template="${t.key}" class="text-xs font-medium text-brand-600 hover:underline">Edit Template</button></div>`).join('')}</div>`)}
    </div>
  `;
  n.channels.forEach(c => document.getElementById('chan-'+c.key).addEventListener('click', withErr(async () => {
    await api('PATCH', '/notifications/channels/'+c.key, { enabled: !c.enabled });
    toast('Channel updated'); await refreshNotifications(); navigateTo('notifications-config');
  })));
  document.getElementById('save-onesignal-btn').addEventListener('click', withErr(async () => {
    await api('PATCH', '/settings', {
      onesignal_app_id: document.getElementById('os-app-id').value,
      onesignal_api_key: document.getElementById('os-api-key').value || undefined,
    });
    toast('OneSignal settings saved'); await refreshSettings(); navigateTo('notifications-config');
  }));
  document.getElementById('os-test-btn').addEventListener('click', withErr(async () => {
    const id = document.getElementById('os-test-id').value.trim();
    if (!id) { toast('Paste a subscription ID first', 'warn'); return; }
    const r = await api('POST', '/notifications/test-push', { subscription_id: id });
    toast(r.message);
  }));
  main.querySelectorAll('[data-edit-template]').forEach(btn => btn.addEventListener('click', () => {
    const t = n.templates.find(x=>x.key===btn.dataset.editTemplate);
    const varList = (t.variables||[]).map(v=>'{#var#} &rarr; '+v).join(', ') || 'none';
    openModal({ title:`Edit Template — ${t.name}`, wide:true,
      body: `<div>${formTextarea('Message Body (Email/Push)','', 5, 'template-body')}</div>
        <p class="text-xs text-ink-400 mt-2">Variables: ${(t.variables||[]).map(v=>'@{{'+v+'}}').join(', ') || 'none'}</p>
        <div class="mt-4 pt-4 border-t border-ink-100 dark:border-ink-800">
          ${formField('SMS DLT Template ID (optional)','text','e.g. 220782', t.sms_template_id||'', 'template-sms-id')}
          <p class="text-xs text-ink-400 mt-1.5">The Message ID from your Fast2SMS DLT Manager for this event's approved template. Leave blank to skip SMS for this notification. Your DLT template's {#var#} placeholders, in order, receive: ${varList}.</p>
        </div>`,
      confirmLabel:'Save Template', onConfirm: withErr(async () => {
        document.getElementById('template-body').value = document.getElementById('template-body').value || t.body;
        await api('PATCH', '/notifications/templates/'+t.key, {
          body: document.getElementById('template-body').value || t.body,
          sms_template_id: document.getElementById('template-sms-id').value || null,
        });
        closeModal(); toast('Template updated successfully'); await refreshNotifications(); navigateTo('notifications-config');
      }) });
    document.getElementById('template-body').value = t.body;
  }));
  document.getElementById('broadcast-btn').addEventListener('click', () => {
    openModal({ title:'New Broadcast Message', wide:true,
      body:`<div class="space-y-4">${formSelect('Send To',['All Customers','All Sellers','Active Sellers Only'], 0, 'bc-audience')}${formField('Subject','text','e.g. Monsoon Sale starts tomorrow!','','bc-subject')}${formTextarea('Message','Write your broadcast message...', 4, 'bc-message')}</div>`,
      confirmLabel:'Send Broadcast', onConfirm: withErr(async () => {
        const r = await api('POST', '/notifications/broadcast', { audience: document.getElementById('bc-audience').value, subject: document.getElementById('bc-subject').value, message: document.getElementById('bc-message').value });
        closeModal(); toast(r.message, 'info');
      }) });
  });
};

/* ===== ROLES & PERMISSIONS ===== */
SECTION_RENDERERS.roles = async function(main) {
  const r = DATA.rolesData;
  const roleIcons = ['shield','user-cog','headset','calculator','store'];
  main.innerHTML = `
    ${pageHeader('Roles & Permissions', 'Control what each admin role can access', btnPrimary('Add Team Member','plus','add-role-btn'))}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      ${r.roles.map((role,i) => `<div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card p-4 text-center">
        <div class="w-11 h-11 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto mb-2.5">${icon(roleIcons[i]||'shield','w-5 h-5')}</div>
        <p class="font-semibold text-sm text-ink-800 dark:text-ink-100">${role.name}</p><p class="text-xs text-ink-400 mt-0.5">${role.members} members</p>
      </div>`).join('')}
    </div>
    ${card(`<div class="p-5 pb-3"><h3 class="font-semibold text-ink-900 dark:text-white">Permission Matrix</h3><p class="text-xs text-ink-400 mt-0.5">Toggle module access per role</p></div>
      <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
        <thead><tr class="text-left text-xs text-ink-400 border-t border-ink-100 dark:border-ink-800"><th class="font-medium py-2.5 px-5">Permission</th>${r.roles.map(role=>`<th class="font-medium py-2.5 px-3 text-center">${role.name}</th>`).join('')}</tr></thead>
        <tbody>${r.permission_modules.map(perm => `<tr class="border-t border-ink-100 dark:border-ink-800"><td class="py-3 px-5 font-medium text-ink-700 dark:text-ink-200">${perm}</td>
          ${r.roles.map(role=>`<td class="py-3 px-3 text-center"><input type="checkbox" data-perm-cell data-role-id="${role.id}" data-perm="${perm}" ${role.permissions.includes(perm)?'checked':''} class="rounded w-4 h-4 accent-brand-600 cursor-pointer"></td>`).join('')}
        </tr>`).join('')}</tbody>
      </table></div>
      <div class="px-5 py-4 border-t border-ink-100 dark:border-ink-800 flex justify-end">${btnPrimary('Save Permissions','check','save-permissions-btn')}</div>`)}
  `;
  document.getElementById('save-permissions-btn').addEventListener('click', withErr(async () => {
    for (const role of r.roles) {
      const perms = Array.from(main.querySelectorAll(`[data-role-id="${role.id}"]:checked`)).map(el => el.dataset.perm);
      await api('PATCH', '/roles/'+role.id+'/permissions', { permissions: perms });
    }
    toast('Permissions saved'); await refreshRoles(); navigateTo('roles');
  }));
  document.getElementById('add-role-btn').addEventListener('click', () => {
    openModal({ title:'Add Team Member', body:`<div class="space-y-4">${formField('Full Name','text','e.g. Anita Kapoor','','tm-name')}${formField('Email','email','team@hardwarecentre.in','','tm-email')}${formSelect('Role', r.roles.map(x=>x.name), 0, 'tm-role')}</div>`,
      confirmLabel:'Send Invite', onConfirm: withErr(async () => {
        const r = await api('POST', '/roles/invite', { name: document.getElementById('tm-name').value, email: document.getElementById('tm-email').value, role: document.getElementById('tm-role').value });
        closeModal(); toast(r.message, r.message.includes('temporary password') ? 'warn' : 'success'); await refreshRoles(); navigateTo('roles');
      }) });
  });
};

/* ===== SETTINGS ===== */
SECTION_RENDERERS.settings = async function(main) {
  const s = DATA.settings;
  const tabs = ['Company Information','Security & SMTP','OTP Verification'];
  let activeTab = tabs[0];
  main.innerHTML = `
    ${pageHeader('Settings', 'Company, security and system configuration')}
    <div class="flex gap-1.5 mb-5 overflow-x-auto pb-1 scrollbar-none" id="settings-tabs">
      ${tabs.map((t,i) => `<button data-tab="${t}" class="settings-tab px-3.5 py-2 text-sm font-medium rounded-xl whitespace-nowrap transition-colors ${i===0?'bg-brand-600 text-white':'bg-white dark:bg-ink-800 text-ink-600 dark:text-ink-300 border border-ink-200 dark:border-ink-700'}">${t}</button>`).join('')}
    </div>
    <div id="settings-content"></div>`;

  function renderTab() {
    const content = document.getElementById('settings-content');
    if (activeTab === 'Company Information') {
      content.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Business Details</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              ${formField('Business Name','text','', s.business_name||'', 'st-business-name')}
              ${formField('Contact Number','tel','', s.contact_number||'', 'st-contact')}
              ${formField('Support Email','email','', s.support_email||'', 'st-email')}
              ${formField('GST Number','text','', s.gst_number||'', 'st-gst')}
              ${formField('Invoice Prefix','text','', s.invoice_prefix||'', 'st-prefix')}
              ${formField('Currency','text','', s.currency||'', 'st-currency')}
            </div>
            <div class="mt-4">${formTextarea('Registered Address','', 3, 'st-address')}</div>
          </div>`)}
          ${card(`<div class="p-5"><div class="flex items-center justify-between"><span class="text-sm font-medium">Maintenance Mode</span>${toggleSwitch(s.maintenance_mode, 'st-maintenance')}</div><p class="text-xs text-ink-400 mt-2">Temporarily disable storefront for maintenance</p></div>`)}
        </div>
        <div class="flex justify-end mt-5">${btnPrimary('Save Changes','check','save-company-btn')}</div>`;
      document.getElementById('st-address').value = s.registered_address || '';
      document.getElementById('save-company-btn').addEventListener('click', withErr(async () => {
        await api('PATCH', '/settings', {
          business_name: document.getElementById('st-business-name').value,
          contact_number: document.getElementById('st-contact').value,
          support_email: document.getElementById('st-email').value,
          gst_number: document.getElementById('st-gst').value,
          invoice_prefix: document.getElementById('st-prefix').value,
          currency: document.getElementById('st-currency').value,
          registered_address: document.getElementById('st-address').value,
          maintenance_mode: document.getElementById('st-maintenance').dataset.checked === 'true',
        });
        toast('Company settings saved'); await refreshSettings(); navigateTo('settings');
      }));
    } else if (activeTab === 'Security & SMTP') {
      content.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">SMTP Settings</p><div class="space-y-3">
            ${formField('SMTP Host','text','smtp.yourmailserver.com', s.smtp_host||'', 'st-smtp-host')}
            ${formField('SMTP Port','number','587', s.smtp_port||'', 'st-smtp-port')}
            ${formSelect('Encryption', ['TLS','SSL','None'], ['tls','ssl','none'].indexOf(s.smtp_encryption||'tls'), 'st-smtp-encryption')}
            ${formField('SMTP Username','text','', s.smtp_username||'', 'st-smtp-user')}
            ${formField('SMTP Password','password','••••••••••','', 'st-smtp-pass')}
            ${formField('From Address','email','no-reply@yourdomain.com', s.smtp_from_address||'', 'st-smtp-from-address')}
            ${formField('From Name','text','Hardware Centers', s.smtp_from_name||'', 'st-smtp-from-name')}
          </div></div>`)}
          ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Password Policy</p><div class="space-y-3">
            ${formField('Minimum Password Length','number','', s.password_min_length||8, 'st-pw-len')}
            <div class="flex items-center justify-between"><span class="text-sm">Require special character</span>${toggleSwitch(s.password_require_special, 'st-pw-special')}</div>
            <div class="flex items-center justify-between"><span class="text-sm">Require 2FA for Admins</span>${toggleSwitch(s.require_2fa_admins, 'st-2fa')}</div>
            ${formField('Session Timeout (minutes)','number','', s.session_timeout_minutes||60, 'st-session-timeout')}
          </div></div>`)}
        </div>
        <div class="flex justify-end mt-5">${btnPrimary('Save Changes','check','save-security-btn')}</div>`;
      document.getElementById('save-security-btn').addEventListener('click', withErr(async () => {
        await api('PATCH', '/settings', {
          smtp_host: document.getElementById('st-smtp-host').value,
          smtp_port: parseInt(document.getElementById('st-smtp-port').value||0) || null,
          smtp_encryption: ['tls','ssl','none'][document.getElementById('st-smtp-encryption').selectedIndex],
          smtp_username: document.getElementById('st-smtp-user').value,
          smtp_password: document.getElementById('st-smtp-pass').value || undefined,
          smtp_from_address: document.getElementById('st-smtp-from-address').value,
          smtp_from_name: document.getElementById('st-smtp-from-name').value,
          password_min_length: parseInt(document.getElementById('st-pw-len').value||8),
          password_require_special: document.getElementById('st-pw-special').dataset.checked === 'true',
          require_2fa_admins: document.getElementById('st-2fa').dataset.checked === 'true',
          session_timeout_minutes: parseInt(document.getElementById('st-session-timeout').value||60),
        });
        toast('Security settings saved'); await refreshSettings(); navigateTo('settings');
      }));
    } else {
      const channel = s.otp_channel === 'sms' ? 'sms' : 'email';
      content.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Active Channel</p>
            <p class="text-xs text-ink-400 mb-4">Only one channel can be active at a time. Switching to SMS disables Email OTP, and vice versa.</p>
            <div class="space-y-2">
              <label class="flex items-center gap-2.5 p-3 rounded-xl border ${channel==='email'?'border-brand-600 bg-brand-50 dark:bg-brand-600/10':'border-ink-200 dark:border-ink-700'} cursor-pointer">
                <input type="radio" name="otp-channel" value="email" id="otp-ch-email" ${channel==='email'?'checked':''}>
                <div><p class="text-sm font-medium text-ink-900 dark:text-white">Email OTP</p><p class="text-xs text-ink-400">Sent via the SMTP settings configured in Security &amp; SMTP</p></div>
              </label>
              <label class="flex items-center gap-2.5 p-3 rounded-xl border ${channel==='sms'?'border-brand-600 bg-brand-50 dark:bg-brand-600/10':'border-ink-200 dark:border-ink-700'} cursor-pointer">
                <input type="radio" name="otp-channel" value="sms" id="otp-ch-sms" ${channel==='sms'?'checked':''}>
                <div><p class="text-sm font-medium text-ink-900 dark:text-white">SMS OTP</p><p class="text-xs text-ink-400">Sent via Fast2SMS using the API key below</p></div>
              </label>
            </div>
          </div>`)}
          ${card(`<div class="p-5"><p class="font-semibold text-ink-900 dark:text-white mb-4">Fast2SMS</p><div class="space-y-3">
            ${formField('Fast2SMS API Key','password','••••••••••','', 'st-fast2sms-key')}
            <p class="text-xs text-ink-400">From fast2sms.com &rarr; Dev API &rarr; API Key. Leave blank to keep the current key.</p>
            <div class="pt-3 mt-1 border-t border-ink-100 dark:border-ink-800">
              <p class="text-xs font-semibold text-ink-600 dark:text-ink-300 mb-2">DLT SMS (recommended)</p>
              ${formField('DLT Sender ID','text','e.g. HDWCTR', s.fast2sms_sender_id || '', 'st-fast2sms-sender')}
              <div class="mt-3">${formField('DLT Content Template ID','text','The message_id / Template ID from Fast2SMS DLT Manager', s.fast2sms_dlt_template_id || '', 'st-fast2sms-template')}</div>
              <p class="text-xs text-ink-400 mt-1.5">When both are set, OTPs send via the DLT route instead of the OTP route (which needs separate Fast2SMS website verification). Your DLT template must contain exactly one {#var#} variable for the OTP code.</p>
            </div>
            <div class="pt-3 mt-1 border-t border-ink-100 dark:border-ink-800">
              <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1.5">Send Test SMS</label>
              <div class="flex gap-2">
                <input id="st-test-sms-phone" type="tel" placeholder="10-digit mobile number" class="flex-1 px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none">
                <button id="st-test-sms-btn" class="px-4 py-2.5 text-sm font-medium rounded-xl border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 flex-shrink-0">Send Test</button>
              </div>
              <p class="text-xs text-ink-400 mt-1.5">Save changes above first, then send a real OTP to your own phone to confirm delivery.</p>
            </div>
          </div></div>`)}
        </div>
        <div class="flex justify-end mt-5">${btnPrimary('Save Changes','check','save-otp-btn')}</div>`;
      document.getElementById('save-otp-btn').addEventListener('click', withErr(async () => {
        await api('PATCH', '/settings', {
          otp_channel: document.querySelector('input[name="otp-channel"]:checked').value,
          fast2sms_api_key: document.getElementById('st-fast2sms-key').value || undefined,
          fast2sms_sender_id: document.getElementById('st-fast2sms-sender').value,
          fast2sms_dlt_template_id: document.getElementById('st-fast2sms-template').value,
        });
        toast('OTP settings saved'); await refreshSettings(); navigateTo('settings');
      }));
      document.getElementById('st-test-sms-btn').addEventListener('click', withErr(async () => {
        const phone = document.getElementById('st-test-sms-phone').value.trim();
        if (!phone) { toast('Enter a phone number first', 'warn'); return; }
        const r = await api('POST', '/settings/test-sms', { phone });
        toast(r.message);
      }));
    }
    refreshIcons();
  }
  main.querySelectorAll('.settings-tab').forEach(btn => btn.addEventListener('click', () => {
    activeTab = btn.dataset.tab;
    main.querySelectorAll('.settings-tab').forEach(b => {
      b.classList.toggle('bg-brand-600', b===btn); b.classList.toggle('text-white', b===btn);
      b.classList.toggle('bg-white', b!==btn); b.classList.toggle('dark:bg-ink-800', b!==btn);
      b.classList.toggle('text-ink-600', b!==btn); b.classList.toggle('dark:text-ink-300', b!==btn);
      b.classList.toggle('border', b!==btn); b.classList.toggle('border-ink-200', b!==btn); b.classList.toggle('dark:border-ink-700', b!==btn);
    });
    renderTab();
  }));
  renderTab();
};

/* ===== WAREHOUSE & FBA ===== */
SECTION_RENDERERS.warehouse = async function(main) {
  const tabs = ['Inventory', 'Inbound Shipments', 'Dispatch Queue', 'Warehouses'];
  let activeTab = tabs[0];
  const overview = DATA.warehouseOverview || { warehouses: [], dispatch_pending: 0 };

  main.innerHTML = `
    ${pageHeader('Warehouse & FBA', 'Manage warehouse stock, inbound shipments and order dispatch')}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Warehouses', value: fmtNum(overview.warehouses.length), icon:'warehouse', accent:'brand'})}
      ${kpiCard({label:'SKUs Tracked', value: fmtNum(overview.warehouses.reduce((a,w)=>a+w.skuCount,0)), icon:'package', accent:'emerald'})}
      ${kpiCard({label:'Low Stock Items', value: fmtNum(overview.warehouses.reduce((a,w)=>a+w.lowStockCount,0)), icon:'alert-triangle', accent:'amber'})}
      ${kpiCard({label:'Pending Dispatch', value: fmtNum(overview.dispatch_pending), icon:'truck', accent:'rose'})}
    </div>
    <div class="flex gap-1.5 mb-5 overflow-x-auto pb-1 scrollbar-none" id="warehouse-tabs">
      ${tabs.map((t,i) => `<button data-tab="${t}" class="warehouse-tab px-3.5 py-2 text-sm font-medium rounded-xl whitespace-nowrap transition-colors ${i===0?'bg-brand-600 text-white':'bg-white dark:bg-ink-800 text-ink-600 dark:text-ink-300 border border-ink-200 dark:border-ink-700'}">${t}</button>`).join('')}
    </div>
    <div id="warehouse-content"></div>`;

  function renderTab() {
    const content = document.getElementById('warehouse-content');

    if (activeTab === 'Inventory') {
      content.innerHTML = tableCard('warehouse-inventory-table');
      renderDataTable('warehouse-inventory-table', {
        title: 'Warehouse Inventory', subtitle: `${DATA.warehouseInventory.length} SKUs across all warehouses`,
        data: DATA.warehouseInventory, searchKeys: ['product','seller','warehouse'],
        filters: [{ key: 'warehouse', label: 'Warehouse', options: [...new Set(DATA.warehouseInventory.map(i=>i.warehouse))] }],
        columns: [
          { label:'Product', render: i => `<div><p class="font-medium text-ink-800 dark:text-ink-100">${i.product}</p>${i.variant?`<p class="text-xs text-ink-400">${i.variant}</p>`:''}</div>` },
          { label:'Seller', key:'seller' },
          { label:'Warehouse', key:'warehouse' },
          { label:'Available', render: i => `<span class="font-semibold ${i.lowStock?'text-amber-600':'text-ink-800 dark:text-ink-100'}">${fmtNum(i.quantityAvailable)}</span>` },
          { label:'Reserved', render: i => fmtNum(i.quantityReserved) },
        ],
        rowClick: openInventoryAdjustModal,
      });
    }

    if (activeTab === 'Inbound Shipments') {
      content.innerHTML = tableCard('warehouse-shipments-table');
      renderDataTable('warehouse-shipments-table', {
        title: 'Inbound Shipments', subtitle: `${DATA.warehouseShipments.length} shipments from sellers`,
        data: DATA.warehouseShipments, searchKeys: ['id','seller'],
        filters: [{ key: 'status', label: 'Status', options: ['Pending','In Transit','Received','Partially Received','Rejected'] }],
        columns: [
          { label:'Shipment', render: s => `<span class="font-medium text-ink-800 dark:text-ink-100">${s.id}</span>` },
          { label:'Seller', key:'seller' },
          { label:'Warehouse', key:'warehouse' },
          { label:'Items', render: s => `${s.items.reduce((a,it)=>a+it.quantityExpected,0)} units (${s.items.length} products)` },
          { label:'Status', render: s => statusBadge(s.status) },
          { label:'Created', render: s => new Date(s.createdAt).toLocaleDateString('en-IN',{day:'numeric',month:'short'}) },
        ],
        rowClick: openShipmentDrawer,
      });
    }

    if (activeTab === 'Dispatch Queue') {
      content.innerHTML = tableCard('dispatch-queue-table');
      renderDataTable('dispatch-queue-table', {
        title: 'Dispatch Queue', subtitle: `${DATA.dispatchQueue.length} warehouse-fulfilled items awaiting pack/dispatch`,
        data: DATA.dispatchQueue, searchKeys: ['orderId','customerName','product'],
        filters: [{ key: 'dispatchStatus', label: 'Status', options: ['Pending','Packed'] }],
        columns: [
          { label:'Order', key:'orderId' },
          { label:'Customer', key:'customerName' },
          { label:'Product', render: i => `<div><p class="font-medium text-ink-800 dark:text-ink-100">${i.product}</p>${i.variant?`<p class="text-xs text-ink-400">${i.variant}</p>`:''}</div>` },
          { label:'Qty', key:'quantity' },
          { label:'Seller', key:'seller' },
          { label:'Status', render: i => statusBadge(i.dispatchStatus) },
          { label:'Action', render: i => i.dispatchStatus === 'Pending'
              ? `<button data-stop-row-click data-action="pack" data-id="${i.id}" class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-brand-600 hover:bg-brand-700 text-white">Mark Packed</button>`
              : `<button data-stop-row-click data-action="dispatch" data-id="${i.id}" class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Dispatch</button>`
          },
        ],
        afterRender: (container) => {
          container.querySelectorAll('[data-action]').forEach(btn => btn.addEventListener('click', withErr(async () => {
            const action = btn.dataset.action;
            await api('POST', `/warehouses/dispatch-queue/${btn.dataset.id}/${action}`, {});
            toast(action === 'pack' ? 'Marked as packed' : 'Marked as dispatched');
            await refreshDispatchQueue(); navigateTo('warehouse');
          })));
        },
      });
    }

    if (activeTab === 'Warehouses') {
      content.innerHTML = `
        <div class="flex justify-end mb-3">${btnPrimary('Add Warehouse','plus','add-warehouse-btn')}</div>
        ${tableCard('warehouses-table')}
      `;
      renderDataTable('warehouses-table', {
        title: 'Warehouses', subtitle: `${overview.warehouses.length} warehouses`,
        data: overview.warehouses, searchKeys: ['name','city'],
        columns: [
          { label:'Name', render: w => `<div><p class="font-medium text-ink-800 dark:text-ink-100">${w.name}</p><p class="text-xs text-ink-400">${w.id}</p></div>` },
          { label:'City', render: w => w.city ? `${w.city}, ${w.state||''}` : '—' },
          { label:'SKUs', render: w => fmtNum(w.skuCount) },
          { label:'Low Stock', render: w => fmtNum(w.lowStockCount) },
          { label:'Pending Shipments', render: w => fmtNum(w.pendingShipments) },
          { label:'Status', render: w => statusBadge(w.status) },
        ],
        rowClick: openWarehouseEditModal,
      });
      document.getElementById('add-warehouse-btn').addEventListener('click', () => {
        openModal({
          title: 'Add Warehouse',
          body: `<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            ${formField('Warehouse Name','text','e.g. Chennai Fulfillment Center','','nw-name')}
            ${formField('Phone','tel','','','nw-phone')}
            ${formField('City','text','','','nw-city')}
            ${formField('State','text','','','nw-state')}
            <div class="sm:col-span-2">${formTextarea('Address','',2,'nw-address')}</div>
          </div>`,
          confirmLabel: 'Create Warehouse', onConfirm: withErr(async () => {
            await api('POST', '/warehouses', {
              name: document.getElementById('nw-name').value,
              phone: document.getElementById('nw-phone').value || undefined,
              city: document.getElementById('nw-city').value || undefined,
              state: document.getElementById('nw-state').value || undefined,
              address: document.getElementById('nw-address').value || undefined,
            });
            closeModal(); toast('Warehouse created'); await refreshWarehouseOverview(); navigateTo('warehouse');
          }),
        });
      });
    }

    refreshIcons();
  }

  main.querySelectorAll('.warehouse-tab').forEach(btn => btn.addEventListener('click', () => {
    activeTab = btn.dataset.tab;
    main.querySelectorAll('.warehouse-tab').forEach(b => {
      b.classList.toggle('bg-brand-600', b===btn); b.classList.toggle('text-white', b===btn);
      b.classList.toggle('bg-white', b!==btn); b.classList.toggle('dark:bg-ink-800', b!==btn);
      b.classList.toggle('text-ink-600', b!==btn); b.classList.toggle('dark:text-ink-300', b!==btn);
      b.classList.toggle('border', b!==btn); b.classList.toggle('border-ink-200', b!==btn); b.classList.toggle('dark:border-ink-700', b!==btn);
    });
    renderTab();
  }));
  renderTab();
};

function openInventoryAdjustModal(i) {
  openModal({
    title: 'Adjust Stock — ' + i.product + (i.variant ? ' ('+i.variant+')' : ''),
    body: `
      <div class="grid grid-cols-2 gap-3 mb-4">${miniStat('Available', fmtNum(i.quantityAvailable))}${miniStat('Reserved', fmtNum(i.quantityReserved))}</div>
      ${formField('Adjustment (+/-)','number','e.g. -5 or 20','','wi-delta')}
      ${formTextarea('Reason / Note','e.g. damaged stock, recount',2,'wi-note')}
    `,
    confirmLabel: 'Apply Adjustment', onConfirm: withErr(async () => {
      const delta = parseInt(document.getElementById('wi-delta').value || '0', 10);
      if (!delta) { throw new Error('Enter a non-zero adjustment'); }
      await api('PATCH', '/warehouses/inventory/'+i.id, { quantity_delta: delta, note: document.getElementById('wi-note').value || undefined });
      closeModal(); toast('Inventory adjusted'); await refreshWarehouseInventory(); navigateTo('warehouse');
    }),
  });
}

function openShipmentDrawer(s) {
  openDrawer(s.id, `
    <div class="grid grid-cols-2 gap-3 mb-5">${miniStat('Status', s.status)}${miniStat('Warehouse', s.warehouse)}</div>
    ${drawerSection('Shipment Details', `${drawerRow('Seller', s.seller)}${drawerRow('Expected', s.expectedAt ? new Date(s.expectedAt).toLocaleDateString('en-IN') : '—')}${drawerRow('Notes', s.notes || '—')}`)}
    <div class="mt-4">
      <p class="text-xs font-semibold uppercase tracking-wide text-ink-400 mb-2">Items Received</p>
      <div class="space-y-2.5">
        ${s.items.map(item => `
          <div class="bg-ink-50 dark:bg-ink-800/40 rounded-xl p-3 flex items-center justify-between gap-3">
            <div class="min-w-0"><p class="text-sm font-medium text-ink-800 dark:text-ink-100 truncate">${item.product}${item.variant?` <span class="text-ink-400">(${item.variant})</span>`:''}</p><p class="text-xs text-ink-400">Expected ${item.quantityExpected}</p></div>
            <input type="number" min="0" max="${item.quantityExpected}" value="${item.quantityReceived}" data-shipment-item="${item.id}" class="w-20 px-2.5 py-1.5 text-sm rounded-lg border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 text-right">
          </div>
        `).join('')}
      </div>
    </div>
    <div class="flex gap-2.5 mt-6 pt-5 border-t border-ink-100 dark:border-ink-800">
      <button id="save-shipment-receipt-btn" class="flex-1 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl">Save Received Quantities</button>
    </div>
  `, true);
  document.getElementById('save-shipment-receipt-btn').addEventListener('click', withErr(async () => {
    const items = s.items.map(item => ({
      item_id: item.id,
      quantity_received: parseInt(document.querySelector(`[data-shipment-item="${item.id}"]`).value || '0', 10),
    }));
    await api('POST', '/warehouses/shipments/'+s.id+'/receive', { items });
    closeModal(); toast('Shipment updated'); await refreshWarehouseInventory(); await refreshWarehouseShipments(); navigateTo('warehouse');
  }));
}

function openWarehouseEditModal(w) {
  openModal({
    title: 'Edit Warehouse — ' + w.name,
    body: `<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      ${formField('Warehouse Name','text','',w.name,'ew-name')}
      ${formField('Phone','tel','',w.phone||'','ew-phone')}
      ${formField('City','text','',w.city||'','ew-city')}
      ${formField('State','text','',w.state||'','ew-state')}
      ${formSelect('Status',['Active','Inactive'],w.status==='Active'?0:1,'ew-status')}
    </div>`,
    confirmLabel: 'Save Changes', onConfirm: withErr(async () => {
      await api('PATCH', '/warehouses/'+w.id, {
        name: document.getElementById('ew-name').value,
        phone: document.getElementById('ew-phone').value || undefined,
        city: document.getElementById('ew-city').value || undefined,
        state: document.getElementById('ew-state').value || undefined,
        status: document.getElementById('ew-status').value,
      });
      closeModal(); toast('Warehouse updated'); await refreshWarehouseOverview(); navigateTo('warehouse');
    }),
  });
}
