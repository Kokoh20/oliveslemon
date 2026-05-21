(function(){
  const CART_KEY = 'olives-lemon-cart-v1';
  const loadCart = () => { try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch { return []; } };
  const saveCart = (items) => localStorage.setItem(CART_KEY, JSON.stringify(items));
  const money = (n) => (n||0).toFixed(2);

  const els = {
    orderList: document.getElementById('orderList'),
    subtotal: document.getElementById('subtotal'),
    discount: document.getElementById('discount'),
    payable: document.getElementById('payable'),
    cartCount: document.getElementById('cartCount'),
    cartTotal: document.getElementById('cartTotal'),
    couponInput: document.getElementById('couponInput'),
    applyCoupon: document.getElementById('applyCoupon'),
    placeOrder: document.getElementById('placeOrder'),
    toast: document.getElementById('orderToast'),
    toastOrderId: document.getElementById('toastOrderId'),
    toastTrackBtn: document.getElementById('toastTrackBtn'),
    toastCloseBtn: document.getElementById('toastCloseBtn'),
    payCash: document.getElementById('pay_cash'),
    payPaypal: document.getElementById('pay_paypal'),
    payGCash: document.getElementById('pay_gcash'),
    payCard: document.getElementById('pay_card'),
    onlineBox: document.getElementById('onlineBox'),
    paypalButtons: document.getElementById('paypalButtons'),
    gcashBox: document.getElementById('gcashBox'),
    cardBox: document.getElementById('cardBox'),
    gcashPay: document.getElementById('gcashPay'),
    cardPay: document.getElementById('cardPay'),
    payStatus: document.getElementById('payStatus'),
    payRef: document.getElementById('payRef')
  };

  let couponValue = 0;
  let paymentMethod = 'cash'; // cash | paypal | gcash | card
  let paymentPaid = false;
  let paymentRef = '';

  function calc(cart){
    let sub = 0; let count = 0;
    for(const item of cart){
      const extrasTotal = (item.extras||[]).reduce((s,e)=> s + (e.price * e.qty), 0);
      sub += (item.price + extrasTotal) * item.qty;
      count += item.qty;
    }
    const discount = Math.min(couponValue, sub);
    const total = sub - discount;
    els.subtotal.textContent = money(sub);
    els.discount.textContent = money(discount);
    els.payable.textContent = money(total);
    els.cartCount.textContent = count;
    els.cartTotal.textContent = money(total);
  }

  function render(){
    const cart = loadCart();
    els.orderList.innerHTML = cart.map((item, idx)=>{
      const extras = (item.extras||[]).map(e=> e.qty>0 ? `<span class="badge text-bg-light me-1">${e.name}</span>` : '').join(' ');
      return `
        <div class="card">
          <div class="card-body d-flex align-items-start gap-3">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="btn-group btn-group-sm" role="group" aria-label="qty">
                  <button type="button" class="btn btn-outline-secondary" data-minus="${idx}">-</button>
                  <button type="button" class="btn btn-outline-secondary" disabled>${item.qty}</button>
                  <button type="button" class="btn btn-outline-secondary" data-plus="${idx}">+</button>
                </div>
                <div class="ms-2 fw-bold">${item.name}</div>
                <div class="ms-auto">₱ ${(item.price).toFixed(2)}</div>
              </div>
              <div class="text-muted small">${extras || ''}</div>
              ${item.notes? `<div class="small mt-1">Notes: ${item.notes}</div>`:''}
            </div>
            <button class="btn btn-sm btn-link text-danger" data-remove="${idx}"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>`;
    }).join('');

    els.orderList.querySelectorAll('[data-minus]').forEach(btn=> btn.addEventListener('click', ()=> changeQty(+btn.getAttribute('data-minus'), -1)));
    els.orderList.querySelectorAll('[data-plus]').forEach(btn=> btn.addEventListener('click', ()=> changeQty(+btn.getAttribute('data-plus'), +1)));
    els.orderList.querySelectorAll('[data-remove]').forEach(btn=> btn.addEventListener('click', ()=> removeItem(+btn.getAttribute('data-remove'))));

    calc(cart);
  }

  // Payment UI logic
  function setPaymentMethod(method){
    paymentMethod = method;
    const show = (method !== 'cash');
    if(show) els.onlineBox?.classList.remove('d-none'); else els.onlineBox?.classList.add('d-none');
    // reset sections
    els.paypalButtons?.classList.add('d-none');
    els.gcashBox?.classList.add('d-none');
    els.cardBox?.classList.add('d-none');
    if(method === 'paypal'){
      els.paypalButtons?.classList.remove('d-none');
      renderPayPal();
    } else if(method === 'gcash'){
      els.gcashBox?.classList.remove('d-none');
    } else if(method === 'card'){
      els.cardBox?.classList.remove('d-none');
    }
  }
  els.payCash?.addEventListener('change', ()=> setPaymentMethod('cash'));
  els.payPaypal?.addEventListener('change', ()=> setPaymentMethod('paypal'));
  els.payGCash?.addEventListener('change', ()=> setPaymentMethod('gcash'));
  els.payCard?.addEventListener('change', ()=> setPaymentMethod('card'));
  // Initialize based on which option is pre-checked (default to GCash if selected)
  if (els.payGCash?.checked) setPaymentMethod('gcash');
  else if (els.payPaypal?.checked) setPaymentMethod('paypal');
  else if (els.payCard?.checked) setPaymentMethod('card');
  else setPaymentMethod('cash');

  function simulatePayment(){
    const d = new Date();
    const ref = 'PAY-' + d.getFullYear().toString() + String(d.getMonth()+1).padStart(2,'0') + String(d.getDate()).padStart(2,'0') + '-' + Math.random().toString(36).slice(2,8).toUpperCase();
    paymentPaid = true; paymentRef = ref;
    if(els.payRef) els.payRef.textContent = ref;
    els.payStatus?.classList.remove('d-none');
  }
  els.gcashPay?.addEventListener('click', simulatePayment);
  els.cardPay?.addEventListener('click', simulatePayment);

  // PayPal Integration (Sandbox client-id loaded in HTML)
  let paypalRendered = false;
  function renderPayPal(){
    if(paypalRendered) return;
    if(!(window.paypal && els.paypalButtons)) return;
    const amountEl = els.payable;
    paypalRendered = true;
    window.paypal.Buttons({
      style: { layout: 'horizontal', color: 'gold', shape: 'pill', label: 'paypal' },
      createOrder: (data, actions) => {
        const amount = (parseFloat(amountEl?.textContent || '0') || 0).toFixed(2);
        return actions.order.create({
          purchase_units: [{ amount: { currency_code: 'PHP', value: amount } }]
        });
      },
      onApprove: (data, actions) => actions.order.capture().then(details =>{
        paymentPaid = true;
        paymentRef = details.id || data.orderID || 'PAYPAL';
        if(els.payRef) els.payRef.textContent = paymentRef;
        els.payStatus?.classList.remove('d-none');
      }),
      onError: (err) => {
        console.error(err);
        alert('PayPal error. Please try again or use another method.');
      }
    }).render(els.paypalButtons);
  }

  function changeQty(index, delta){
    const cart = loadCart();
    if(!cart[index]) return;
    cart[index].qty = Math.max(1, cart[index].qty + delta);
    saveCart(cart); render();
  }

  function removeItem(index){
    const cart = loadCart();
    cart.splice(index,1);
    saveCart(cart); render();
  }

  els.applyCoupon.addEventListener('click', ()=>{
    const code = (els.couponInput.value || '').trim().toUpperCase();
    
    if(code === 'PROMO10') couponValue = 10; else if(code === 'PROMO20') couponValue = 20; else couponValue = 0;
    render();
  });

  els.placeOrder.addEventListener('click', ()=>{
    const cart = loadCart();
    if (cart.length === 0) {
      alert('Your cart is empty.');
      return;
    }
    const name = document.getElementById('customerName')?.value.trim() || '';
    const phone = document.getElementById('phoneNumber')?.value.trim() || '';
    const address = document.getElementById('deliveryAddress')?.value.trim() || '';
    const type = document.querySelector('input[name="otype"]:checked')?.value || 'pickup';

    if (!name || !phone) {
      alert('Please provide your name and phone number.');
      return;
    }

    const subtotal = parseFloat(els.subtotal.textContent) || 0;
    const discount = parseFloat(els.discount.textContent) || 0;
    const payable = parseFloat(els.payable.textContent) || 0;

    if(paymentMethod !== 'cash' && !paymentPaid){
      alert('Please complete the online payment first by clicking Pay Now.');
      return;
    }

    const payload = {
      customer: { name, phone, address, type },
      items: cart,
      totals: { subtotal, discount, payable },
      payment: { method: paymentMethod, paid: paymentPaid, ref: paymentRef }
    };

    fetch('orders.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(r => r.json()).then(data =>{
      console.log('Sending payload:', JSON.stringify(payload, null, 2));
      if (data && data.ok) {
        // Clear cart and show success toast with Order ID
        localStorage.removeItem(CART_KEY);
        const id = data.order && data.order.id;
        // Update cart bar to 0
        els.cartCount.textContent = '0';
        els.cartTotal.textContent = '0.00';
        // reset payment state
        paymentPaid = false; paymentRef = ''; setPaymentMethod('cash');
        // Show toast
        if (els.toast && id) {
          els.toastOrderId.textContent = id;
          els.toast.classList.remove('d-none');
          // Also log and alert for guaranteed visibility
          try { console.log('Order placed. ID:', id); } catch {}
          alert('Order placed!\nOrder ID: ' + id);
          // Wire buttons
          if (els.toastTrackBtn) {
            els.toastTrackBtn.onclick = ()=> window.location.href = 'track.html?id=' + encodeURIComponent(id);
          }
          const receipt = document.getElementById('toastReceiptBtn');
          if(receipt){ receipt.href = 'receipt.html?id=' + encodeURIComponent(id); }
          const receiptBtn = document.getElementById('receiptBtn');
          if(receiptBtn){
            receiptBtn.classList.remove('d-none');
            receiptBtn.href = 'receipt.html?id=' + encodeURIComponent(id);
          }
          if (els.toastCloseBtn) {
            els.toastCloseBtn.onclick = ()=> els.toast.classList.add('d-none');
          }
        } else {
          // Fallback: redirect to store
          window.location.href = 'index.html';
        }
      } else {
        alert(data && data.error ? 'Failed: ' + data.error : 'Failed to place order');
      }
    }).catch(err =>{
      console.error(err);
      alert('Network error while placing order.');
    });
  });

  render();
})();