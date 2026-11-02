/* DevFolio CMS - admin behaviour (vanilla JS, Bootstrap 5 bundle required) */
(function () {
  'use strict';

  /* ---- mobile sidebar ---- */
  document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
    b.addEventListener('click', function () { document.body.classList.toggle('sidebar-open'); });
  });
  document.querySelectorAll('[data-sidebar-close]').forEach(function (b) {
    b.addEventListener('click', function () { document.body.classList.remove('sidebar-open'); });
  });

  /* ---- auto-dismiss success alerts ---- */
  document.querySelectorAll('[data-autodismiss]').forEach(function (el) {
    var ms = parseInt(el.getAttribute('data-autodismiss'), 10);
    if (ms > 0) {
      setTimeout(function () {
        if (window.bootstrap && document.body.contains(el)) { bootstrap.Alert.getOrCreateInstance(el).close(); }
      }, ms);
    }
  });

  /* ---- image preview ---- */
  function setPreview(id, src) {
    var img = document.querySelector('[data-preview-for="' + id + '"]');
    if (!img) { return; }
    if (src) { img.src = src; img.classList.remove('d-none'); }
    else { img.removeAttribute('src'); img.classList.add('d-none'); }
  }
  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input.matches || !input.matches('[data-image-input]')) { return; }
    var file = input.files && input.files[0];
    if (!file) { return; }
    if (file.size > 3 * 1024 * 1024) { alert('Image is larger than 3 MB.'); input.value = ''; return; }
    var r = new FileReader();
    r.onload = function () { setPreview(input.id, r.result); };
    r.readAsDataURL(file);
  });

  /* ---- bootstrap-icons live preview ---- */
  function iconPreview(input) {
    var i = input.closest('.input-group').querySelector('[data-icon-preview]');
    if (i) { i.className = 'bi ' + (/^bi-[a-z0-9-]+$/.test(input.value) ? input.value : 'bi-question-circle'); }
  }
  document.addEventListener('input', function (e) {
    if (e.target.matches && e.target.matches('[data-icon-input]')) { iconPreview(e.target); }
  });

  /* ---- generic CRUD modal: fill / reset ---- */
  function fillForm(form, row) {
    form.reset();
    form.querySelector('input[name="id"]').value = row && row.id ? row.id : '';
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.name === 'id' || el.name === 'csrf_token' || el.name.charAt(0) === '_') { return; }
      if (el.type === 'file') {
        setPreview(el.id, row && row[el.name] ? '../' + row[el.name] : '');
        return;
      }
      if (!row) { return; }                       /* "add": keep defaults from form.reset() */
      if (!(el.name in row)) { return; }
      if (el.type === 'checkbox') { el.checked = String(row[el.name]) === '1'; }
      else { el.value = row[el.name] === null ? '' : row[el.name]; }
    });
    form.querySelectorAll('[data-icon-input]').forEach(iconPreview);
    form.dispatchEvent(new CustomEvent('crud:fill', { detail: row || null }));
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('[data-crud-add],[data-crud-edit]') : null;
    if (btn) {
      var modal = document.querySelector(btn.getAttribute('data-bs-target'));
      var form = modal.querySelector('[data-crud-form]');
      var row = null;
      if (btn.hasAttribute('data-crud-edit')) { try { row = JSON.parse(btn.getAttribute('data-row')); } catch (x) { row = null; } }
      modal.querySelector('[data-modal-title]').textContent = btn.getAttribute('data-title') || '';
      fillForm(form, row);
    }
    var del = e.target.closest ? e.target.closest('[data-delete-id]') : null;
    if (del) {
      var dm = document.querySelector(del.getAttribute('data-bs-target'));
      dm.querySelector('input[name="id"]').value = del.getAttribute('data-delete-id');
      dm.querySelector('[data-delete-label]').textContent = del.getAttribute('data-delete-name') || '';
    }
  });

  /* ---- re-open a modal after a server-side validation error ---- */
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-crud-form][data-reopen]');
    if (!form) { return; }
    var modal = form.closest('.modal');
    var row = JSON.parse(form.getAttribute('data-reopen'));
    fillForm(form, row);
    modal.querySelector('[data-modal-title]').textContent = row.id ? 'Edit' : 'Add';
    bootstrap.Modal.getOrCreateInstance(modal).show();
  });

  /* ---- generic confirm helper: <form data-confirm="Really?"> ---- */
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
    if (msg && !confirm(msg)) { e.preventDefault(); }
  });
})();
