// Chặng B: iOS Bottom Sheet báo giá (Pointer Events kéo đóng >30% chiều cao, nhả giữa chừng bật về, gửi REST POST /quotes)
(function () {
  var sheet = document.getElementById('quote-sheet');
  if (!sheet) return;

  var handle = sheet.querySelector('[data-sheet-drag]');
  var form   = document.getElementById('quote-form');
  var msgEl  = document.getElementById('quote-msg');
  var noteEl = document.getElementById('quote-note');
  var CURVE  = 'cubic-bezier(0.32, 0.72, 0, 1)';

  function openSheet() {
    sheet.style.transition = '';
    sheet.style.transform  = '';
    if (noteEl && !noteEl.value) {
      var prodH1 = document.querySelector('.product-detail h1');
      if (prodH1) noteEl.value = prodH1.textContent.trim();
    }
    if (!sheet.open) sheet.showModal();
  }

  // Bấm nút [data-quote-open] hoặc nút Gọi báo giá ở thanh đáy .float-contact sẽ mở Bottom Sheet
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-quote-open]');
    if (!trigger) return;
    e.preventDefault();
    openSheet();
  });

  // Kéo thanh handle theo chuẩn iOS (Pointer Events: hỗ trợ cả cảm ứng và chuột)
  if (handle) {
    var startY = 0;
    var deltaY = 0;
    var dragging = false;

    handle.addEventListener('pointerdown', function (e) {
      dragging = true;
      startY = e.clientY;
      deltaY = 0;
      sheet.style.transition = 'none';
      if (handle.setPointerCapture) {
        try { handle.setPointerCapture(e.pointerId); } catch (_) {}
      }
    });

    handle.addEventListener('pointermove', function (e) {
      if (!dragging) return;
      deltaY = Math.max(0, e.clientY - startY);
      sheet.style.transform = 'translateY(' + deltaY.toFixed(1) + 'px)';
    });

    function endDrag() {
      if (!dragging) return;
      dragging = false;
      var h = sheet.offsetHeight || 360;
      sheet.style.transition = 'transform 280ms ' + CURVE;
      if (deltaY > h * 0.3) {
        sheet.style.transform = 'translateY(100%)';
        setTimeout(function () {
          sheet.close();
          sheet.style.transition = '';
          sheet.style.transform  = '';
        }, 260);
      } else {
        sheet.style.transform = 'translateY(0)';
      }
    }

    handle.addEventListener('pointerup', endDrag);
    handle.addEventListener('pointercancel', endDrag);
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var CFG = window.PCCC24_CONFIG || {};
      var fd  = new FormData(form);
      var payload = {
        name: (fd.get('name') || '').toString().trim(),
        phone: (fd.get('phone') || '').toString().trim(),
        need: (fd.get('need') || 'Bình chữa cháy').toString(),
        note: (fd.get('note') || '').toString().trim(),
        website: (fd.get('website') || '').toString(),
        source_url: window.location.href
      };
      var btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      msgEl.hidden = true;

      fetch((CFG.restUrl || '/wp-json/pccc24/v1') + '/quotes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(function (r) {
          return r.json().then(function (data) { return { status: r.status, ok: r.ok, data: data }; });
        })
        .then(function (res) {
          if (btn) btn.disabled = false;
          msgEl.hidden = false;
          if (res.ok && res.data && res.data.ok) {
            msgEl.className = 'ios-sheet-msg ok';
            msgEl.textContent = res.data.message || 'Đã nhận yêu cầu! Kỹ thuật viên sẽ gọi lại sớm.';
            form.reset();
          } else {
            msgEl.className = 'ios-sheet-msg err';
            msgEl.textContent = (res.data && res.data.message) || 'Vui lòng kiểm tra lại họ tên và số điện thoại.';
          }
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          msgEl.hidden = false;
          msgEl.className = 'ios-sheet-msg err';
          msgEl.textContent = 'Không gửi được yêu cầu. Vui lòng bấm nút Gọi trực tiếp phía trên.';
        });
    });
  }
})();
