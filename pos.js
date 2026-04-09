(function(){
  const API = '../orders.php';
  const els = {
    date: document.getElementById('reportDate'),
    refresh: document.getElementById('refresh'),
    printBtn: document.getElementById('printBtn'),
    k_total: document.getElementById('k_total'),
    k_completed: document.getElementById('k_completed'),
    k_cancelled: document.getElementById('k_cancelled'),
    k_sales: document.getElementById('k_sales'),
    payTable: document.getElementById('payTable'),
    typeTable: document.getElementById('typeTable'),
    ordersList: document.getElementById('ordersList'),
  };

  function money(n){ return (Number(n)||0).toFixed(2); }
  function toDateKey(iso){
    if(!iso) return '';
    const d = new Date(iso);
    if(Number.isNaN(d.getTime())) return '';
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${dd}`;
  }
  function setTodayDefault(){
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    els.date.value = els.date.value || `${y}-${m}-${dd}`;
  }

  function renderDay(orders){
    
    const total = orders.length;
    const completedOrders = orders.filter(o => (o.status||'').toLowerCase()==='completed');
    const cancelledOrders = orders.filter(o => (o.status||'').toLowerCase()==='cancelled');
    const sales = completedOrders.reduce((s,o)=> s + (o.totals?.payable||0), 0);
    els.k_total.textContent = String(total);
    els.k_completed.textContent = String(completedOrders.length);
    els.k_cancelled.textContent = String(cancelledOrders.length);
    els.k_sales.textContent = money(sales);

    
    const payMap = new Map();
    for(const o of completedOrders){
      const method = (o.payment?.method || 'cash').toUpperCase();
      const amt = (o.totals?.payable || 0);
      const entry = payMap.get(method) || { count: 0, amount: 0 };
      entry.count += 1; entry.amount += amt; payMap.set(method, entry);
    }
    const payRows = Array.from(payMap.entries()).sort((a,b)=> a[0]>b[0]?1:-1).map(([m, v])=>
      `<tr><td>${m}</td><td class="text-end">${v.count}</td><td class="text-end">${money(v.amount)}</td></tr>`
    ).join('');
    els.payTable.innerHTML = payRows || '<tr><td colspan="3" class="text-center text-muted">No data</td></tr>';

    
    const typeMap = new Map();
    for(const o of completedOrders){
      const t = (o.customer?.type || 'pickup').toUpperCase();
      const amt = (o.totals?.payable || 0);
      const entry = typeMap.get(t) || { count: 0, amount: 0 };
      entry.count += 1; entry.amount += amt; typeMap.set(t, entry);
    }
    const typeRows = Array.from(typeMap.entries()).sort((a,b)=> a[0]>b[0]?1:-1).map(([t, v])=>
      `<tr><td>${t}</td><td class="text-end">${v.count}</td><td class="text-end">${money(v.amount)}</td></tr>`
    ).join('');
    els.typeTable.innerHTML = typeRows || '<tr><td colspan="3" class="text-center text-muted">No data</td></tr>';

    
    els.ordersList.innerHTML = orders.map(o =>{
      const id = o.id || '';
      const time = (o.createdAt||'').split('T')[1]?.replace('Z','') || '';
      const status = (o.status||'').toUpperCase();
      const customer = `${o.customer?.name || ''} • ${o.customer?.phone || ''}`;
      const total = money(o.totals?.payable || 0);
      const method = (o.payment?.method || 'cash').toUpperCase();
      const paid = o.payment?.paid ? 'PAID' : 'UNPAID';
      return `
        <div class="d-flex align-items-center justify-content-between border rounded p-2">
          <div class="small">
            <div class="d-flex align-items-center gap-2"><span class="badge text-bg-light">${status}</span> <code>${id}</code></div>
            <div class="text-muted">${time}</div>
            <div>${customer}</div>
            <div class="text-muted small">${method} • ${paid}</div>
          </div>
          <div class="fw-bold">₱ ${total}</div>
        </div>`;
    }).join('');
  }

  async function load(){
    const selected = els.date.value || '';
    try{
      const res = await fetch(API);
      const body = await res.json();
      const all = body.orders || [];
      
      const dayOrders = all.filter(o => toDateKey(o.createdAt) === selected);
      renderDay(dayOrders);
    }catch{
      els.ordersList.innerHTML = '<div class="alert alert-danger">Failed to load report.</div>';
    }
  }



  els.refresh.addEventListener('click', load);
  els.printBtn.addEventListener('click', () => window.print());

  setTodayDefault();
  load();
})();
