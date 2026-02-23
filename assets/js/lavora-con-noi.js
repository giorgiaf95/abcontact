(function(){
  'use strict';

  function qs(sel, root){ return (root||document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }

  function lockScroll(lock){
    document.documentElement.classList.toggle('lc-modal-open', !!lock);
    document.body.classList.toggle('lc-modal-open', !!lock);
  }

  function employmentLabel(v){
    return v === 'part_time' ? 'Part-time' : 'Full-time';
  }

  function bytesToKB(b){ return Math.round(b/1024); }

  function renderFileStatus(el, name, state, note){
    if(!el) return;
    el.classList.remove('upload-filename--success','upload-filename--error');
    el.innerHTML = '';
    var icon = document.createElement('span'); icon.className = 'upload-filename__icon';
    icon.textContent = state === 'success' ? '✓' : (state === 'error' ? '✕' : '');
    if(state === 'success') el.classList.add('upload-filename--success');
    if(state === 'error') el.classList.add('upload-filename--error');
    var txt = document.createElement('span'); txt.className = 'upload-filename__name'; txt.textContent = name || '';
    el.appendChild(icon); el.appendChild(txt);
    if(note){
      var noteEl = document.createElement('div'); noteEl.className = 'upload-filename__note'; noteEl.textContent = note;
      el.appendChild(noteEl);
    }
  }

  function validateFile(file, allowedExts, maxBytes){
    if(!file) return { ok: false, reason: 'Nessun file' };
    var name = file.name || '';
    var ext = (name.split('.').pop() || '').toLowerCase();
    if(allowedExts && allowedExts.length && allowedExts.indexOf(ext) === -1){
      return { ok: false, reason: 'Tipo file non supportato' };
    }
    if(maxBytes && file.size > maxBytes){
      return { ok: false, reason: 'File troppo grande. Max ' + (maxBytes/1024/1024) + ' MB' };
    }
    return { ok: true };
  }

  function handleCvChange(input){
    var el = qs('#cv_upload_filename');
    var allowed = ['pdf','doc','docx','rtf','txt'];
    var maxBytes = 10 * 1024 * 1024;

    if(input.files && input.files.length){
      var f = input.files[0];
      var res = validateFile(f, allowed, maxBytes);
      if(res.ok){
        renderFileStatus(el, f.name + ' (' + bytesToKB(f.size) + ' KB)', 'success');
      } else {
        renderFileStatus(el, f.name || '', 'error', res.reason);
      }
    } else {
      renderFileStatus(el, '', null);
    }
  }

  function openModalWithJob(btn){
    var modal = qs('#lc-apply-modal'); if(!modal) return;

    var jobId = btn.getAttribute('data-job-id') || '';
    var title = btn.getAttribute('data-job-title') || '';
    var cat = btn.getAttribute('data-job-category') || 'Posizione';
    var loc = btn.getAttribute('data-job-location') || '—';
    var type = btn.getAttribute('data-job-type') || 'full_time';
    var desc = btn.getAttribute('data-job-description') || '';

    var catEl = qs('#lc-modal-category', modal);
    var titleEl = qs('#lc-modal-title', modal);
    var locEl = qs('#lc-modal-location', modal);
    var typeEl = qs('#lc-modal-type', modal);
    var descEl = qs('#lc-modal-description', modal);

    if(catEl) catEl.textContent = cat;
    if(titleEl) titleEl.textContent = title;
    if(locEl) locEl.textContent = loc;
    if(typeEl) typeEl.textContent = employmentLabel(type);
    if(descEl){
  var safe = (desc || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\r\n|\r|\n/g, '<br>');
  descEl.innerHTML = safe;
}

    var hidId = qs('#lc_job_id', modal);
    var hidTitle = qs('#lc_job_title', modal);
    if(hidId) hidId.value = jobId;
    if(hidTitle) hidTitle.value = title;

    modal.setAttribute('aria-hidden', 'false');
    lockScroll(true);

    setTimeout(function(){
      var close = qs('[data-lc-close]', modal);
      if(close) try{ close.focus(); }catch(e){}
    }, 10);

    try{
      var url = new URL(window.location.href);
      url.searchParams.set('job_id', jobId);
      window.history.replaceState({}, '', url.toString());
    }catch(e){}
  }

  function closeModal(){
    var modal = qs('#lc-apply-modal'); if(!modal) return;
    modal.setAttribute('aria-hidden', 'true');
    lockScroll(false);
  }

  function init(){
    var modal = qs('#lc-apply-modal');
    if(!modal) return;

    qsa('.lc-job-card').forEach(function(btn){
      btn.addEventListener('click', function(){ openModalWithJob(btn); });
    });

    qsa('[data-lc-close]', modal).forEach(function(el){
      el.addEventListener('click', function(e){
        e.preventDefault();
        closeModal();
      });
    });

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false'){
        closeModal();
      }
    });

    var cv = qs('#cv_file', modal);
    if(cv){
      cv.addEventListener('change', function(){ handleCvChange(cv); });
    }

    var form = qs('#lc-apply-form', modal);
    if(form){
      form.addEventListener('submit', function(e){
        var jobId = qs('#lc_job_id', modal);
        var gdpr = qs('#lc_gdpr_confirm', modal);
        if(jobId && !jobId.value){ e.preventDefault(); alert('Seleziona una posizione prima di inviare la candidatura.'); return false; }
        if(gdpr && !gdpr.checked){ e.preventDefault(); alert('Devi accettare il trattamento dei dati personali.'); gdpr.focus(); return false; }
        if(cv && (!cv.files || !cv.files.length)){ e.preventDefault(); alert('Devi caricare il tuo CV.'); cv.focus(); return false; }
        return true;
      });
    }

    if(window.__LC_OPEN_JOB_ID){
      var id = String(window.__LC_OPEN_JOB_ID);
      var btn = null;
      try{
        btn = qs('.lc-job-card[data-job-id="'+ CSS.escape(id) +'"]');
      }catch(e){
        btn = qs('.lc-job-card[data-job-id="'+ id.replace(/"/g,'') +'"]');
      }
      if(btn) openModalWithJob(btn);
    }
  }

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();