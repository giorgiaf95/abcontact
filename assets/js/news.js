(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var imgs = document.querySelectorAll('.news-card__thumb--large, .news-card__thumb--small');
    imgs.forEach(function (img) {
      img.style.transition = img.style.transition || 'transform 240ms ease';
      img.setAttribute('loading', img.getAttribute('loading') || 'lazy');
    });
  });
})();