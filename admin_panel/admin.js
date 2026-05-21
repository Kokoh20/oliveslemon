(function(){
  const els = {
    list: document.getElementById('ordersList'),
    search: document.getElementById('searchOrders'),
    totalOrders: document.getElementById('totalOrders'),
    completedOrders: document.getElementById('completedOrders'),
    preparingOrders: document.getElementById('preparingOrders'),
    todaySales: document.getElementById('todaySales')
  };

  let allOrders = [];
  let statusFilter = 'all';

  document.querySelectorAll('.chip').forEach(ch => ch.addEventListener('click', () =>{
    document.querySelectorAll('.chip').forEach(c=> c.classList.remove('active'));
    ch.classList.add('active');
    statusFilter = ch.getAttribute('data-filter');
    render();
  }));

  els.search.addEventListener('input', render);

  function updateDashboard() {
    const today = new Date().toISOString().split('T')[0];
    const todayOrders = allOrders.filter(o => (o.createdAt || '').startsWith(today));
    
    const total = allOrders.length;
    const completed = allOrders.filter(o => (o.status || '') === 'completed').length;
    const preparing = allOrders.filter(o => (o.status || '') === 'preparing').length;
    const sales = todayOrders
      .filter(o => (o.status || '') === 'completed')
      .reduce((sum, o) => sum + (o.totals?.payable || 0), 0);
    
    els.totalOrders.textContent = String(total);
    els.completedOrders.textContent = String(completed);
    els.preparingOrders.textContent = String(preparing);
    els.todaySales.textContent = `₱${fmtMoney(sales)}`;
  }

  function refreshDashboard() {
    fetchOrders();
  }

  function exportTodayOrders() {
    const today = new Date().toISOString().split('T')[0];
    const todayOrders = allOrders.filter(o => (o.createdAt || '').startsWith(today));
    
    if (todayOrders.length === 0) {
      alert('No orders to export for today.');
      return;
    }
    
    let csv = 'Order ID,Customer Name,Phone,Status,Items,Total,Payment Method,Created At\n';
    todayOrders.forEach(order => {
      const items = (order.items || []).map(item => 
        `${item.qty}x ${item.name}${item.variant ? ` (${item.variant.name})` : ''}`
      ).join('; ');
      
      csv += [
        order.id || '',
        order.customer?.name || '',
        order.customer?.phone || '',
        order.status || '',
        `"${items}"`,
        order.totals?.payable || 0,
        order.payment?.method || 'cash',
        order.createdAt || ''
      ].join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `orders_${today}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
  }

  function fetchOrders(){
    console.log('Fetching orders from ../orders.php');
    return fetch('../orders.php').then(r=> {
      console.log('Response status:', r.status);
      return r.json();
    }).then(data => {
      console.log('Received data:', data);
      allOrders = data.orders || [];
      console.log('Orders loaded:', allOrders.length);
      updateDashboard();
      render();
    }).catch(err =>{
      console.error('Error fetching orders:', err);
      alert('Failed to load orders');
    });
  }

  function fmtMoney(n){ return (n||0).toFixed(2); }

  function matchesFilter(o){
    if(statusFilter !== 'all' && (o.status||'') !== statusFilter) return false;
    const q = (els.search.value||'').toLowerCase();
    if(!q) return true;
    const hay = [o.id||'', o.customer?.name||'', o.customer?.phone||''].join(' ').toLowerCase();
    return hay.includes(q);
  }

  function render(){
    const list = allOrders.filter(matchesFilter);
    
    if (list.length === 0) {
      els.list.innerHTML = `
        <div class="text-center py-5">
          <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
          <h4 class="text-muted">No orders found</h4>
          <p class="text-muted">
            ${statusFilter !== 'all' ? 'Try changing the status filter or search term.' : 'No orders have been placed yet.'}
          </p>
          <button class="btn btn-primary" onclick="fetchOrders()">
            <i class="fa-solid fa-refresh"></i> Refresh Orders
          </button>
        </div>
      `;
      return;
    }
    
    els.list.innerHTML = list.map(o =>{
      const items = (o.items||[]).map(it =>{
        const extras = (it.extras||[]).map(e => e.qty>0? `<span class="badge text-bg-light me-1">${e.name} x${e.qty}</span>`:'').join(' ');
        return `<div class="order-item">${it.qty} x <strong>${it.name}</strong> - ₱ ${fmtMoney(it.price)}<div class="small-muted">${extras}</div></div>`;
      }).join('');
      return `
        <div class="card order-card ${o.status||'new'}">
          <div class="card-body">
            <div class="d-flex align-items-start gap-3">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2">
                  <div class="fw-bold">#${o.id}</div>
                  <span class="badge text-bg-secondary status-badge">${o.status||'new'}</span>
                  <div class="ms-auto">₱ ${fmtMoney(o.totals?.payable||0)}</div>
                </div>
                <div class="small-muted">${o.createdAt||''}</div>
                <div class="mt-2">
                  <div><i class="fa-regular fa-user"></i> ${o.customer?.name||''} — ${o.customer?.phone||''}</div>
                  ${o.customer?.address? `<div><i class="fa-solid fa-location-dot"></i> ${o.customer.address}</div>`:''}
                </div>
                <div class="mt-3">${items}</div>
              </div>
              <div class="order-actions vstack gap-2">
                ${renderActions(o)}
                <button class="btn btn-outline-secondary btn-sm" data-copy="${o.id}"><i class="fa-regular fa-copy"></i> Copy link</button>
                <button class="btn btn-outline-danger btn-sm" data-del="${o.id}"><i class="fa-regular fa-trash-can"></i> Delete</button>
              </div>
            </div>
          </div>
        </div>`;
    }).join('');

    els.list.querySelectorAll('[data-status]').forEach(btn=> btn.addEventListener('click', ()=> updateStatus(btn.getAttribute('data-status-id'), btn.getAttribute('data-status'))));
    els.list.querySelectorAll('[data-copy]').forEach(btn=> btn.addEventListener('click', ()=> copyLink(btn.getAttribute('data-copy'))));
    els.list.querySelectorAll('[data-del]').forEach(btn=> btn.addEventListener('click', ()=> delOrder(btn.getAttribute('data-del'))));
  }

  function renderActions(o){
    const map = [
      ['new','preparing','Start'],
      ['preparing','ready','Mark Ready'],
      ['ready','completed','Complete']
    ];
    const found = map.find(([cur]) => (o.status||'new') === cur);
    if(found){
      const [, next, label] = found;
      return `<button class="btn btn-primary btn-sm" data-status-id="${o.id}" data-status="${next}">${label}</button>`;
    }
    return `<button class="btn btn-outline-secondary btn-sm" disabled>No actions</button>`;
  }

  function updateStatus(id, status){
    fetch('../orders.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status })
    }).then(r => r.json()).then(data =>{
      if(data && data.ok){ fetchOrders(); }
      else alert(data && data.error ? data.error : 'Failed to update');
    }).catch(err=>{ console.error(err); alert('Network error'); });
  }

  function delOrder(id){
    if(!confirm('Delete this order?')) return;
    fetch('../orders.php?id='+encodeURIComponent(id), { method: 'DELETE' })
      .then(r => r.json()).then(data =>{
        if(data && data.ok){ fetchOrders(); }
        else alert(data && data.error ? data.error : 'Failed to delete');
      }).catch(err=>{ console.error(err); alert('Network error'); });
  }

  function copyLink(id){
    try {
      const url = new URL(location.href);
      url.pathname = url.pathname.replace(/admin_panel\/.*$/, 'track.html');
      url.search = '?id=' + encodeURIComponent(id);
      const link = url.toString();
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(()=> alert('Tracking link copied')); 
      } else {
        // Fallback prompt
        prompt('Copy tracking link:', link);
      }
    } catch (e) {
      console.error(e);
      alert('Failed to copy link');
    }
  }

  window.refreshDashboard = refreshDashboard;
  window.exportTodayOrders = exportTodayOrders;
  window.fetchOrders = fetchOrders;

  fetchOrders();
})();