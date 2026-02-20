(function(){
  'use strict';

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

  function findOrCreateFilenameEl(wrapper, fileInput){
    // prefer specific ids
    var prefId = fileInput && fileInput.id === 'cv_file' ? 'cv_upload_filename' : 'upload_filename';
    var pref = document.getElementById(prefId);
    if(pref) return pref;

    if(wrapper){
      var s = wrapper.parentElement && wrapper.parentElement.querySelector('.upload-filename');
      if(s) return s;
    }

    // fallback: create directly after wrapper
    var el = document.createElement('div');
    el.className = 'upload-filename';
    if(wrapper && wrapper.parentNode){
      wrapper.parentNode.insertBefore(el, wrapper.nextSibling);
    } else {
      document.body.appendChild(el);
    }
    return el;
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

  function getAllowedExtsFromAccept(accept){
    if(!accept) return null;
    // accept string like ".pdf,image/*" or ".pdf,.jpg"
    var parts = accept.split(',');
    var exts = [];
    parts.forEach(function(p){
      p = p.trim();
      if(!p) return;
      if(p.indexOf('/') !== -1){
        // mime type: image/* => map common ones
        if(p.indexOf('image') !== -1){
          exts.push('jpg','jpeg','png','gif');
        } else if(p.indexOf('pdf') !== -1){
          exts.push('pdf');
        }
      } else if(p.charAt(0) === '.'){
        exts.push(p.substr(1).toLowerCase());
      }
    });
    return exts.length ? exts : null;
  }

  function handleFileInputChange(fileInput){
    var wrapper = fileInput.closest('.upload-drop');
    var filenameEl = findOrCreateFilenameEl(wrapper, fileInput);

    var accept = fileInput.getAttribute('accept') || '';
    var allowedExts = getAllowedExtsFromAccept(accept) || ['pdf','jpg','jpeg','png','doc','docx','rtf','txt'];
    var maxBytes = 10 * 1024 * 1024; // 10MB

    if(fileInput.files && fileInput.files.length){
      var f = fileInput.files[0];
      var res = validateFile(f, allowedExts, maxBytes);
      if(res.ok){
        renderFileStatus(filenameEl, f.name + ' (' + bytesToKB(f.size) + ' KB)', 'success');
      } else {
        renderFileStatus(filenameEl, f.name || '', 'error', res.reason);
      }
    } else {
      renderFileStatus(filenameEl, '', null);
    }
  }

  /* -----------------------
     Toggle UI: user type (privato / azienda)
     ----------------------- */
  function toggleUserTypeUI(value){
    var companyEl = document.getElementById('company_fields');
    var privateEl = document.getElementById('private_fields');
    var first = document.getElementById('first_name');
    var last = document.getElementById('last_name');
    var company = document.getElementById('company_name');

    // normalize presence
    if ( ! companyEl && ! privateEl ) return;

    if ( value === 'azienda' ) {
      if ( companyEl ) companyEl.style.display = 'block';
      if ( privateEl ) privateEl.style.display = 'none';
      if ( company ) company.setAttribute('required','required');
      if ( first ) first.removeAttribute('required');
      if ( last ) last.removeAttribute('required');
    } else if ( value === 'privato' ) {
      if ( companyEl ) companyEl.style.display = 'none';
      if ( privateEl ) privateEl.style.display = 'block';
      if ( company ) company.removeAttribute('required');
      if ( first ) first.setAttribute('required','required');
      if ( last ) last.setAttribute('required','required');
    } else {
      if ( companyEl ) companyEl.style.display = 'none';
      if ( privateEl ) privateEl.style.display = 'none';
      if ( company ) company.removeAttribute('required');
      if ( first ) first.removeAttribute('required');
      if ( last ) last.removeAttribute('required');
    }
  }

  function init(){
    // Remove duplicate elements with same IDs (defensive)
    var ids = ['bolletta_file','upload_drop','upload_filename','cv_file','cv_upload_drop','cv_upload_filename'];
    ids.forEach(function(id){
      var elems = document.querySelectorAll('#' + id);
      if(elems.length > 1){
        for(var i=1;i<elems.length;i++){
          try{ elems[i].parentNode && elems[i].parentNode.removeChild(elems[i]); }catch(e){}
        }
      }
    });

    // Delegate change events for file inputs
    document.addEventListener('change', function(e){
      var t = e.target;
      if(!t) return;
      if(t.tagName && t.tagName.toLowerCase() === 'input' && t.type === 'file'){
        try { handleFileInputChange(t); } catch(err){ console.error('file change handler error', err); }
      }
      // also listen for user_type change if it's an input/select that bubbles (just in case)
      if ( t.id === 'user_type' || t.name === 'user_type' ) {
        try { toggleUserTypeUI(t.value); } catch(err){ console.error('toggle user type error', err); }
      }
    }, false);

    // Also handle already-filled inputs (e.g. on page refresh browsers may not populate files, but handle if possible)
    var fileInputs = Array.prototype.slice.call(document.querySelectorAll('input[type="file"]'));
    fileInputs.forEach(function(fi){ try { if(fi.files && fi.files.length) handleFileInputChange(fi); } catch(e){} });

    // Initial toggle: show relevant fields based on current selection (if any)
    var userTypeSelect = document.getElementById('user_type');
    if ( userTypeSelect ) {
      // ensure the UI reflects the current value on load
      try { toggleUserTypeUI(userTypeSelect.value); } catch(err){ console.error(err); }
      // attach change listener
      userTypeSelect.addEventListener('change', function(){
        try { toggleUserTypeUI(this.value); } catch(err){ console.error(err); }
      });
    }

    // optional: accessibility enhancements or extra UI bindings can go here
  }

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

})();