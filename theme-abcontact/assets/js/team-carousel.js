(function(){
  'use strict';

  // CONFIG
  var VISIBLE_COUNT = 5;          // number of visible items at once (requested)
  var SPEED_PX_PER_S = 60;        // px per second scroll speed; changeable
  var GAP_PX = null;              // JS will read gap from computed style
  var SAFE_PADDING = null;        // JS will read safe padding from CSS var

  function px(v){ return Math.round(v); }

  function init() {
    var viewport = document.querySelector('.cs-team-viewport');
    if (!viewport) return;
    var track = viewport.querySelector('.cs-team-track');
    if (!track) return;

    // remove arrows if present (defensive)
    var controls = document.querySelectorAll('.cs-team-controls');
    controls.forEach(function(n){ n.style.display = 'none'; });

    // read CSS gap and safe padding
    var trackStyle = window.getComputedStyle(track);
    GAP_PX = parseFloat(trackStyle.gap || trackStyle.columnGap || 28) || 28;
    var rootStyles = getComputedStyle(document.documentElement);
    SAFE_PADDING = parseFloat(rootStyles.getPropertyValue('--team-safe-padding')) || 72;

    // prepare original items array
    var originals = Array.prototype.slice.call(track.querySelectorAll('.cs-team-item'));
    if (!originals.length) return;

    // If fewer items than visible, clone originals until >= visible+1 to allow smooth movement
    while (originals.length < VISIBLE_COUNT + 1) {
      originals = originals.concat(originals.map(function(n){ return n.cloneNode(true); }));
    }

    // Build inner wrap containing the originals (this becomes "inner"), then append cloned copy
    var inner = document.createElement('div');
    inner.className = 'cs-team-inner-wrap';
    originals.forEach(function(node){ inner.appendChild(node); });

    // clear track and append inner + clone
    track.innerHTML = '';
    track.appendChild(inner);
    var clone = inner.cloneNode(true);
    clone.classList.add('__cloned');
    track.appendChild(clone);

    var items = Array.prototype.slice.call(track.querySelectorAll('.cs-team-item'));
    var innerWidth = 0;

    // compute visible width area (the area in which we must show 5 items)
    function computeDimensions() {
      // available width = viewport clientWidth - left/right safe padding
      var availWidth = Math.max(300, viewport.clientWidth - (SAFE_PADDING * 2));
      // compute item width so that VISIBLE_COUNT items + gaps fill availWidth
      var totalGaps = GAP_PX * (VISIBLE_COUNT - 1);
      var itemW = (availWidth - totalGaps) / VISIBLE_COUNT;
      itemW = Math.max(80, itemW); // floor minimum width

      // set each item flex-basis
      items.forEach(function(it){
        it.style.flex = '0 0 ' + px(itemW) + 'px';
        it.style.width = px(itemW) + 'px';
      });

      // compute innerWidth as scrollWidth of inner (first child)
      var innerEl = track.querySelector('.cs-team-inner-wrap');
      innerWidth = innerEl.scrollWidth;
    }

    // animation state
    var pos = 0; // current translateX in px
    var lastTs = null;
    var running = true;
    var rafId = null;

    function step(ts) {
      if (!lastTs) lastTs = ts;
      var dt = (ts - lastTs) / 1000;
      lastTs = ts;
      if (running && innerWidth > 0) {
        pos -= SPEED_PX_PER_S * dt; // move left
        // when we've translated past -innerWidth, wrap around seamlessly
        if (Math.abs(pos) >= innerWidth) {
          pos += innerWidth; // bring back into [ -innerWidth, 0 )
        }
        track.style.transform = 'translate3d(' + px(pos) + 'px,0,0)';
      }
      rafId = requestAnimationFrame(step);
    }

    // start after images loaded and dimensions computed
    function start() {
      computeDimensions();
      // initial pos = 0
      pos = 0;
      track.style.transform = 'translate3d(0,0,0)';
      lastTs = null;
      if (rafId) cancelAnimationFrame(rafId);
      running = true;
      rafId = requestAnimationFrame(step);
    }

    // pause/resume on hover
    viewport.addEventListener('mouseenter', function(){ running = false; });
    viewport.addEventListener('mouseleave', function(){ running = true; lastTs = null; });

    // recompute on resize (debounced)
    var rt = null;
    window.addEventListener('resize', function(){
      if (rt) clearTimeout(rt);
      rt = setTimeout(function(){
        computeDimensions();
      }, 120);
    });

    // ensure images loaded then start
    var imgs = Array.prototype.slice.call(track.querySelectorAll('img.cs-team-photo'));
    var pending = imgs.length;
    if (pending === 0) {
      setTimeout(start, 60);
    } else {
      imgs.forEach(function(img){
        if (img.complete) {
          pending--;
          if (pending === 0) start();
        } else {
          img.addEventListener('load', function(){ pending--; if (pending === 0) start(); });
          img.addEventListener('error', function(){ pending--; if (pending === 0) start(); });
        }
      });
    }

    // expose control for debug
    window.abTeamCarousel = {
      stop: function(){ running = false; },
      start: function(){ running = true; lastTs = null; },
      setSpeed: function(pxPerS){ SPEED_PX_PER_S = Number(pxPerS) || SPEED_PX_PER_S; },
      getState: function(){ return {pos: pos, innerWidth: innerWidth, itemCount: items.length}; }
    };
  }

  // init
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();