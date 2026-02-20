(function(){
  'use strict';

  // CONFIG
  var VISIBLE_COUNT = 5;
  var DEFAULT_SPEED_PX_PER_S = 22;
  var SPEED_PX_PER_S = DEFAULT_SPEED_PX_PER_S;
  var GAP_PX = 28;
  var SAFE_PADDING = 72;

  function fmt(v){ return Number(v).toFixed(2); }

  function initCarousel() {
    // select elements
    var scrollWrap = document.getElementById('cs-team-scrollwrap');
    var track = document.getElementById('cs-team-track') || (scrollWrap && scrollWrap.querySelector('.cs-team-track')) || document.querySelector('.cs-team-track');
    if (!track) return;

    // create wrapper if missing
    if (!scrollWrap) {
      var parent = track.parentElement;
      scrollWrap = document.createElement('div');
      scrollWrap.id = 'cs-team-scrollwrap';
      scrollWrap.className = 'cs-team-scrollwrap';
      parent.insertBefore(scrollWrap, track);
      scrollWrap.appendChild(track);
    }

    var viewport = track.closest('.cs-team-viewport') || document.querySelector('.cs-team-viewport') || track.parentElement;
    if (!viewport) viewport = document.body;

    // cleanup previous instances
    try {
      if (window.__abTeamCarouselInterval) { clearInterval(window.__abTeamCarouselInterval); window.__abTeamCarouselInterval = null; }
      if (window.__abTeamCarouselFallback) { clearInterval(window.__abTeamCarouselFallback); window.__abTeamCarouselFallback = null; }
      if (window.__abTeamCarouselRAF) { cancelAnimationFrame(window.__abTeamCarouselRAF); window.__abTeamCarouselRAF = null; }
      if (window.__abTeamCarouselWriter) { clearInterval(window.__abTeamCarouselWriter); window.__abTeamCarouselWriter = null; }
    } catch(e){}

    // read CSS values
    var trackStyle = getComputedStyle(track);
    GAP_PX = parseFloat(trackStyle.gap || trackStyle.columnGap) || GAP_PX;
    var rootStyles = getComputedStyle(document.documentElement);
    SAFE_PADDING = parseFloat(rootStyles.getPropertyValue('--team-safe-padding')) || SAFE_PADDING;

    // ensure inner wrap
    var innerEl = track.querySelector('.cs-team-inner-wrap');
    if (!innerEl) {
      var originals = Array.prototype.slice.call(track.querySelectorAll('.cs-team-item'));
      if (!originals.length) return;
      innerEl = document.createElement('div');
      innerEl.className = 'cs-team-inner-wrap';
      originals.forEach(function(n){ innerEl.appendChild(n); });
      track.innerHTML = '';
      track.appendChild(innerEl);
    }

    // set widths helper
    function setItemWidths() {
      var viewportWidth = Math.max(300, (viewport.clientWidth || window.innerWidth) - (SAFE_PADDING * 2));
      var totalGaps = GAP_PX * (VISIBLE_COUNT - 1);
      var itemW = (viewportWidth - totalGaps) / VISIBLE_COUNT;
      itemW = Math.max(80, itemW);
      var items = track.querySelectorAll('.cs-team-item');
      items.forEach(function(it){
        it.style.flex = '0 0 ' + Math.round(itemW) + 'px';
        it.style.width = Math.round(itemW) + 'px';
      });
    }

    // initial size and cloning until safe
    setItemWidths();
    var singleWidth = innerEl.scrollWidth || innerEl.offsetWidth || 0;
    var viewportW = Math.max(300, (viewport.clientWidth || window.innerWidth));
    var requiredTotal = viewportW + (singleWidth * 2) + 10;
    var safety = 0;
    while (track.scrollWidth < requiredTotal && safety < 12) {
      var clone = innerEl.cloneNode(true);
      clone.classList.add('__cloned');
      track.appendChild(clone);
      safety++;
    }
    // recompute after clones
    setItemWidths();
    singleWidth = innerEl.scrollWidth || singleWidth;
    var totalTrackWidth = track.scrollWidth || (singleWidth * 2);

    // animation state
    var pos = 0.0;
    var lastTs = null;
    var running = true;
    var rafId = null;
    var fallbackId = null;
    var writerId = null;
    var rafExecuted = false;

    // performance
    scrollWrap.style.willChange = 'transform';
    scrollWrap.style.transformStyle = 'preserve-3d';
    scrollWrap.style.backfaceVisibility = 'hidden';

    // write transform safely with !important
    function writeTransform(v) {
      try {
        scrollWrap.style.setProperty('transform', 'translate3d(' + fmt(v) + 'px,0,0)', 'important');
      } catch (e) {
        scrollWrap.style.transform = 'translate3d(' + Math.round(v) + 'px,0,0)';
      }
    }

    // primary RAF step
    function step(ts) {
      rafExecuted = true;
      if (!lastTs) lastTs = ts;
      var dt = (ts - lastTs) / 1000;
      lastTs = ts;
      if (dt > 0.12) dt = 0.016;
      if (running && singleWidth > 0) {
        pos -= SPEED_PX_PER_S * dt;
        if (Math.abs(pos) >= singleWidth) pos += singleWidth;
        writeTransform(pos);
      }
      rafId = requestAnimationFrame(step);
      window.__abTeamCarouselRAF = rafId;
    }

    // fallback animator
    function startFallback() {
      if (fallbackId) return;
      var last = Date.now();
      fallbackId = setInterval(function(){
        var now = Date.now();
        var dt = (now - last) / 1000;
        last = now;
        if (!running || singleWidth <= 0) return;
        pos -= SPEED_PX_PER_S * dt;
        if (Math.abs(pos) >= singleWidth) pos += singleWidth;
        writeTransform(pos);
      }, 40);
      window.__abTeamCarouselFallback = fallbackId;
    }

    // writer that ensures inline transform exists even if removed by other code
    function startWriter() {
      if (writerId) return;
      writerId = setInterval(function(){
        var inline = scrollWrap.style.getPropertyValue('transform');
        var computed = getComputedStyle(scrollWrap).transform;
        if (!inline || inline === '' || computed === 'none' || computed === 'matrix(1, 0, 0, 1, 0, 0)') {
          writeTransform(pos);
        }
      }, 150);
      window.__abTeamCarouselWriter = writerId;
    }

    function stopAll() {
      running = false;
      if (rafId) { cancelAnimationFrame(rafId); rafId = null; window.__abTeamCarouselRAF = null; }
      if (fallbackId) { clearInterval(fallbackId); fallbackId = null; window.__abTeamCarouselFallback = null; }
      if (writerId) { clearInterval(writerId); writerId = null; window.__abTeamCarouselWriter = null; }
    }

    function startAll() {
      // recompute
      setItemWidths();
      singleWidth = innerEl.scrollWidth || singleWidth;
      totalTrackWidth = track.scrollWidth || totalTrackWidth;
      pos = 0.0;
      writeTransform(pos);
      lastTs = null;
      running = true;
      rafExecuted = false;
      if (rafId) cancelAnimationFrame(rafId);
      rafId = requestAnimationFrame(step);
      // safety: if RAF didn't execute within 200ms, start fallback
      setTimeout(function(){
        if (!rafExecuted && !fallbackId) startFallback();
      }, 200);
      // ensure writer is active
      startWriter();
    }

    // pause/resume on hover
    viewport.addEventListener('mouseenter', function(){ running = false; });
    viewport.addEventListener('mouseleave', function(){ running = true; lastTs = null; });

    // resize debounce
    var rt = null;
    window.addEventListener('resize', function(){
      if (rt) clearTimeout(rt);
      rt = setTimeout(function(){
        setItemWidths();
        singleWidth = innerEl.scrollWidth || singleWidth;
        totalTrackWidth = track.scrollWidth || totalTrackWidth;
      }, 140);
    });

    // wait for images; but also start unconditionally shortly (fix for environments where load/complete isn't reliable)
    var imgs = Array.prototype.slice.call(track.querySelectorAll('img.cs-team-photo'));
    var pending = imgs.length;
    // always attempt to start shortly after init to guarantee auto-start
    setTimeout(startAll, 120);

    if (pending === 0) {
      setTimeout(startAll, 80);
    } else {
      imgs.forEach(function(img){
        if (img.complete) {
          pending--;
          if (pending === 0) startAll();
        } else {
          img.addEventListener('load', function(){ pending--; if (pending === 0) startAll(); });
          img.addEventListener('error', function(){ pending--; if (pending === 0) startAll(); });
        }
      });
    }

    // Public API
    window.abTeamCarousel = {
      startFull: function(){ startAll(); },
      stop: function(){ stopAll(); },
      setSpeed: function(pxPerS){ SPEED_PX_PER_S = Number(pxPerS) || SPEED_PX_PER_S; },
      getState: function(){ return { pos: pos, singleWidth: singleWidth, totalTrackWidth: totalTrackWidth, running: running }; },
      dumpTrackStyle: function(){ console.log('inline:', scrollWrap.style.getPropertyValue('transform'), 'computed:', getComputedStyle(scrollWrap).transform); }
    };

    window.__abTeamCarouselStartedOnce = true;
  }

  // init after load so layout & images are ready; also safe if document already complete
  if (document.readyState === 'complete') {
    setTimeout(initCarousel, 30);
  } else {
    window.addEventListener('load', function(){ setTimeout(initCarousel, 30); });
  }

})();