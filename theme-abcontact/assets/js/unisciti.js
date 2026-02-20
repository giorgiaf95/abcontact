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
    var prefId = fileInput && fileInput.id === 'cv_file' ? 'cv_upload_filename' : 'upload_filename';
    var pref = document.getElementById(prefId);
    if(pref) return pref;
    if(wrapper){
      var s = wrapper.parentElement && wrapper.parentElement.querySelector('.upload-filename');
      if(s) return s;
    }
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
    var parts = accept.split(',');
    var exts = [];
    parts.forEach(function(p){
      p = p.trim();
      if(!p) return;
      if(p.indexOf('/') !== -1){
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
    var allowedExts = getAllowedExtsFromAccept(accept) || ['pdf','doc','docx','rtf','txt','jpg','jpeg','png'];
    var maxBytes = 10 * 1024 * 1024;

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

  function init(){
    // Defensive: remove duplicate ids created by other scripts/templates
    ['cv_file','cv_upload_drop','cv_upload_filename','bolletta_file','upload_drop','upload_filename'].forEach(function(id){
      var elems = document.querySelectorAll('#' + id);
      if(elems.length > 1){
        for(var i=1;i<elems.length;i++){
          try{ elems[i].parentNode && elems[i].parentNode.removeChild(elems[i]); }catch(e){}
        }
      }
    });

    // Delegate change events
    document.addEventListener('change', function(e){
      var t = e.target;
      if(!t) return;
      if(t.tagName && t.tagName.toLowerCase() === 'input' && t.type === 'file'){
        try{ handleFileInputChange(t); } catch(err){ console.error(err); }
      }
    }, false);

    // prepopulate if inputs already have files (unlikely, but safe)
    var fileInputs = Array.prototype.slice.call(document.querySelectorAll('input[type="file"]'));
    fileInputs.forEach(function(fi){ try{ if(fi.files && fi.files.length) handleFileInputChange(fi); }catch(e){} });

    // Optional: client-side submit checks kept in original script (if any)
    var form = document.getElementById('unisciti-form');
    if(form){
      form.addEventListener('submit', function(e){
        var gdpr = document.getElementById('gdpr_confirm');
        var position = document.getElementById('position');
        if(position && !position.value){ e.preventDefault(); alert('Seleziona la posizione per cui ti candidi.'); position.focus(); return false; }
        if(gdpr && !gdpr.checked){ e.preventDefault(); alert('Devi accettare il trattamento dei dati personali.'); gdpr.focus(); return false; }
        return true;
      }, false);
    }
  }

  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

})();