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
    ${pageHeader('Seller Management', 'Onboard, verify and monitor vendors on your marketplace', btnSecondary('Export CSV','download'))}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      ${kpiCard({label:'Total Sellers', value: fmtNum(DATA.sellers.length), icon:'store', accent:'brand'})}
      ${kpiCard({label:'Active Sellers', value: fmtNum(totalActive), icon:'check-circle', accent:'emerald'})}
      ${kpiCard({label:'Pending KYC', value: fmtNum(totalPending), icon:'clock', accent:'amber'})}
      ${kpiCard({label:'Suspended', value: fmtNum(totalSuspended), icon:'ban', accent:'rose'})}
    </div>
    ${tableCard('sellers-table-inner')}
  `;
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
      ${drawerSection('Wallet', `${drawerRow('Wallet Balance', fmtINR(s.walletBalance))}`)}
      ${drawerSection('Commission Rate', `<input id="drawer-commission-input" type="number" step="0.1" value="${s.commissionPct}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-ink-200 dark:border-ink-700 bg-white dark:bg-ink-800 focus-ring outline-none">`)}
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
        ${formSelect('Category', DATA.categories.map(c=>c.name), 0, 'np-category')}
        ${formSelect('Brand', DATA.brands.map(b=>b.name), 0, 'np-brand')}
        ${formSelect('Seller', DATA.sellers.map(s=>s.store), 0, 'np-seller')}
        ${formField('Price (₹)','number','e.g. 2499','','np-price')}
        ${formField('MRP (₹)','number','e.g. 2999','','np-mrp')}
        ${formField('Stock','number','e.g. 100','0','np-stock')}
        ${formSelect('Unit', ['piece','box','pair','coil','bag','litre','tub'], 0, 'np-unit')}
      </div>`,
      confirmLabel:'Create Product',
      onConfirm: withErr(async () => {
        const category = DATA.categories.find(c=>c.name===document.getElementById('np-category').value);
        const brand = DATA.brands.find(b=>b.name===document.getElementById('np-brand').value);
        const seller = DATA.sellers.find(s=>s.store===document.getElementById('np-seller').value);
        await api('POST', '/products', {
          name: document.getElementById('np-name').value,
          category_code: category ? category.id : null,
          brand_code: brand ? brand.id : null,
          seller_code: seller ? seller.id : null,
          price: parseFloat(document.getElementById('np-price').value||0),
          mrp: parseFloat(document.getElementById('np-mrp').value||0),
          stock: parseInt(document.getElementById('np-stock').value||0),
          unit: document.getElementById('np-unit').value,
        });
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
    <div class="flex gap-2.5 mt-6 pt-5 border-t border-ink-100 dark:border-ink-800">
      ${p.approval!=='Approved' ? `<button id="approve-product-btn" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl">Approve</button>` : ''}
      <button id="toggle-featured-btn" class="flex-1 px-4 py-2.5 bg-white dark:bg-ink-800 border border-ink-200 dark:border-ink-700 text-sm font-medium rounded-xl">${p.featured?'Unfeature':'Feature'} Product</button>
    </div>
  `, true);
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
  main.innerHTML = `
    ${pageHeader('Categories', 'Organize products into categories', btnPrimary('Add Category','plus','add-category-btn'))}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      ${DATA.categories.map(c => `
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200/70 dark:border-ink-800 shadow-card p-5">
          <div class="flex items-start justify-between mb-3"><div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">${icon('layout-grid','w-5 h-5')}</div>${statusBadge(c.status)}</div>
          <p class="font-semibold text-ink-900 dark:text-white">${c.name}</p>
          <p class="text-xs text-ink-400 mt-1">${fmtNum(c.products)} products</p>
          <div class="flex gap-2 mt-4">
            <button data-toggle-cat="${c.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800">${c.status==='Active'?'Deactivate':'Activate'}</button>
            <button data-delete-cat="${c.id}" class="flex-1 py-1.5 text-xs font-medium rounded-lg border border-ink-200 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800 text-rose-600">Remove</button>
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
      { label:'Brand', render: b => `<div class="flex items-center gap-3"><div class="w-9 h-9 rounded-lg bg-ink-100 dark:bg-ink-800 flex items-center justify-center">${icon('tag','w-4 h-4 text-ink-400')}</div><span class="font-medium text-ink-800 dark:text-ink-100">${b.name}</span></div>` },
      { label:'Products', render: b => fmtNum(b.products) },
      { label:'Featured', render: b => b.featured ? `<span class="text-amber-500">${icon('star','w-4 h-4 inline fill-current')}</span>` : '—' },
      { label:'Status', render: b => statusBadge(b.status) },
      { label:'', render: b => `<button data-stop-row-click data-toggle-brand="${b.id}" class="text-xs font-medium text-brand-600 hover:underline">${b.status==='Active'?'Deactivate':'Activate'}</button>` },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-toggle-brand]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        const b = DATA.brands.find(x=>x.id===btn.dataset.toggleBrand);
        await api('PATCH', '/brands/'+b.id, { status: b.status==='Active'?'Inactive':'Active' });
        toast('Brand updated'); await refreshBrands(); navigateTo('brands');
      })));
    }
  });
  document.getElementById('add-brand-btn').addEventListener('click', () => {
    openModal({ title:'Add New Brand', body:`<div class="space-y-4">${formField('Brand Name','text','e.g. ToughEdge','','nb-name')}</div>`,
      confirmLabel:'Create Brand', onConfirm: withErr(async () => {
        await api('POST', '/brands', { name: document.getElementById('nb-name').value });
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
    ${drawerSection('Details', `${drawerRow('Customer', o.customerName)}${drawerRow('Payment', statusBadge(o.payment))}${drawerRow('Placed', new Date(o.date).toLocaleDateString('en-IN'))}`)}
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
    filters:[{key:'status', label:'Status', options:['Paid','Pending','Failed']}, {key:'mode', label:'Gateway', options:['Razorpay Route','Cashfree Easy Split']}],
    columns: [
      { label:'Settlement ID', key:'id' },
      { label:'Seller', render: s => `<span class="truncate max-w-[160px] inline-block">${s.sellerName}</span>` },
      { label:'Amount', render: s => fmtINR(s.amount) }, { label:'Gateway', key:'mode' },
      { label:'Date', render: s => new Date(s.date).toLocaleDateString('en-IN',{day:'numeric',month:'short'}) },
      { label:'UTR', render: s => `<span class="font-mono text-xs text-ink-400">${s.utr||'—'}</span>` },
      { label:'Status', render: s => statusBadge(s.status) },
      { label:'', render: s => s.status==='Failed' ? `<button data-stop-row-click data-retry="${s.id}" class="text-xs font-medium text-brand-600 hover:underline">Retry</button>` : '' },
    ],
    afterRender: (container) => {
      container.querySelectorAll('[data-retry]').forEach(btn => btn.addEventListener('click', withErr(async () => {
        await api('POST', '/settlements/'+btn.dataset.retry+'/retry');
        toast('Settlement retried successfully'); await refreshSettlements(); navigateTo('settlements');
      })));
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
          <div class="h-32 flex items-center justify-center" style="background:${b.bg_color}">${icon('image','w-8 h-8 text-white/70')}</div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-1"><p class="font-medium text-sm text-ink-800 dark:text-ink-100 truncate">${b.title}</p>${statusBadge(b.status)}</div>
            <p class="text-xs text-ink-400">${b.type}</p>
            <div class="flex gap-2 mt-3">
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
    </div>`, confirmLabel:'Create Banner', onConfirm: withErr(async () => {
      await api('POST', '/banners', { title: document.getElementById('nba-title').value, subtitle: document.getElementById('nba-subtitle').value, type: document.getElementById('nba-type').value });
      closeModal(); toast('Banner created successfully'); await refreshBanners(); navigateTo('banners');
    }) });
  });
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
  main.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => toast(`${b.dataset.export} exported`)));

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
    ${card(`<div class="p-5 pb-3 flex items-center justify-between"><div><h3 class="font-semibold text-ink-900 dark:text-white">Shipping Zones & Weight Rules</h3><p class="text-xs text-ink-400 mt-0.5">Charges vary by delivery zone</p></div></div>
      <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-ink-400 border-t border-ink-100 dark:border-ink-800"><th class="font-medium py-2.5 px-5">Zone</th><th class="font-medium py-2.5 px-3">Weight Slab</th><th class="font-medium py-2.5 px-3">Charge</th><th class="font-medium py-2.5 px-3">Free Above</th></tr></thead>
        <tbody>${s.zones.map(z => `<tr class="border-t border-ink-100 dark:border-ink-800"><td class="py-3 px-5 font-medium text-ink-800 dark:text-ink-100">${z.zone}</td><td class="py-3 px-3 text-ink-500">${z.weight_slab}</td><td class="py-3 px-3 tnum">${fmtINR(z.charge)}</td><td class="py-3 px-3 tnum">${fmtINR(z.free_above)}</td></tr>`).join('')}</tbody>
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
  document.getElementById('test-shiprocket-btn').addEventListener('click', withErr(async () => { const r = await api('POST', '/shiprocket/test'); toast(r.message); }));
  document.getElementById('sync-orders-btn').addEventListener('click', withErr(async () => { const r = await api('POST', '/shiprocket/sync'); toast(r.message, 'info'); }));
};

/* ===== NOTIFICATION MANAGEMENT ===== */
SECTION_RENDERERS['notifications-config'] = async function(main) {
  const n = DATA.notifications;
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
    ${card(`<div class="p-5 pb-3"><h3 class="font-semibold text-ink-900 dark:text-white">Message Templates</h3><p class="text-xs text-ink-400 mt-0.5">Customize automated messages</p></div>
      <div class="divide-y divide-ink-100 dark:divide-ink-800">${n.templates.map(t => `<div class="flex items-center justify-between px-5 py-3.5"><span class="text-sm font-medium text-ink-700 dark:text-ink-200">${t.name}</span><button data-edit-template="${t.key}" class="text-xs font-medium text-brand-600 hover:underline">Edit Template</button></div>`).join('')}</div>`)}
  `;
  n.channels.forEach(c => document.getElementById('chan-'+c.key).addEventListener('click', withErr(async () => {
    await api('PATCH', '/notifications/channels/'+c.key, { enabled: !c.enabled });
    toast('Channel updated'); await refreshNotifications(); navigateTo('notifications-config');
  })));
  main.querySelectorAll('[data-edit-template]').forEach(btn => btn.addEventListener('click', () => {
    const t = n.templates.find(x=>x.key===btn.dataset.editTemplate);
    openModal({ title:`Edit Template — ${t.name}`, wide:true,
      body: `<div>${formTextarea('Message Body','', 5, 'template-body')}</div><p class="text-xs text-ink-400 mt-2">Variables: @{{customer_name}}, @{{order_id}}, @{{tracking_link}}</p>`,
      confirmLabel:'Save Template', onConfirm: withErr(async () => {
        document.getElementById('template-body').value = document.getElementById('template-body').value || t.body;
        await api('PATCH', '/notifications/templates/'+t.key, { body: document.getElementById('template-body').value || t.body });
        closeModal(); toast('Template updated successfully'); await refreshNotifications(); navigateTo('notifications-config');
      }) });
    document.getElementById('template-body').value = t.body;
  }));
  document.getElementById('broadcast-btn').addEventListener('click', () => {
    openModal({ title:'New Broadcast Message', wide:true,
      body:`<div class="space-y-4">${formSelect('Send To',['All Customers','All Sellers','Active Sellers Only'], 0, 'bc-audience')}${formField('Subject','text','e.g. Monsoon Sale starts tomorrow!','','bc-subject')}${formTextarea('Message','Write your broadcast message...', 4, 'bc-message')}</div>`,
      confirmLabel:'Send Broadcast', onConfirm: withErr(async () => {
        await api('POST', '/notifications/broadcast', { audience: document.getElementById('bc-audience').value, subject: document.getElementById('bc-subject').value, message: document.getElementById('bc-message').value });
        closeModal(); toast('Broadcast sent to selected audience');
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
        await api('POST', '/roles/invite', { name: document.getElementById('tm-name').value, email: document.getElementById('tm-email').value, role: document.getElementById('tm-role').value });
        closeModal(); toast('Invitation sent successfully'); await refreshRoles(); navigateTo('roles');
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
            ${formField('From Name','text','Hardware Centre', s.smtp_from_name||'', 'st-smtp-from-name')}
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
          </div></div>`)}
        </div>
        <div class="flex justify-end mt-5">${btnPrimary('Save Changes','check','save-otp-btn')}</div>`;
      document.getElementById('save-otp-btn').addEventListener('click', withErr(async () => {
        await api('PATCH', '/settings', {
          otp_channel: document.querySelector('input[name="otp-channel"]:checked').value,
          fast2sms_api_key: document.getElementById('st-fast2sms-key').value || undefined,
        });
        toast('OTP settings saved'); await refreshSettings(); navigateTo('settings');
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
