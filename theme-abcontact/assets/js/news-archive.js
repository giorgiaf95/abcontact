(function () {
  'use strict';

  var $ = window.jQuery || null;

  var AbContactNews = {
    ajaxUrl: window.abcontactNews && window.abcontactNews.ajax_url ? window.abcontactNews.ajax_url : (window.ajaxurl || '/wp-admin/admin-ajax.php'),
    nonce: window.abcontactNews && window.abcontactNews.nonce ? window.abcontactNews.nonce : '',
    postsPerPage: window.abcontactNews && window.abcontactNews.posts_per_page ? parseInt(window.abcontactNews.posts_per_page,10) : 6,
    init: function () {
      this.bindUI();
    },
    bindUI: function () {
      var self = this;
      document.getElementById('news-load-more').addEventListener('click', function (e) {
        e.preventDefault();
        self.loadMore(this);
      });

      // Filters
      var filterBtns = document.querySelectorAll('.news-filter-button');
      filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          self.applyFilter(this.getAttribute('data-cat'));
          filterBtns.forEach(function (b) { b.classList.remove('active'); });
          this.classList.add('active');
        });
      });
    },

    applyFilter: function (catId) {
      var grid = document.getElementById('news-archive-grid');
      var loadMoreBtn = document.getElementById('news-load-more');
      loadMoreBtn.setAttribute('data-page', 1);
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = abcontactNews.load_more_label || 'Carica altri articoli';

      this.fetchPosts(1, catId, function (res) {
        grid.innerHTML = res.html;
      });
    },

    loadMore: function (btn) {
      var page = parseInt(btn.getAttribute('data-page'), 10) || 1;
      var next = page + 1;
      var exclude = btn.getAttribute('data-exclude') || 0;
      var activeCatBtn = document.querySelector('.news-filter-button.active');
      var cat = activeCatBtn ? activeCatBtn.getAttribute('data-cat') : 0;

      btn.disabled = true;
      btn.textContent = '...';

      this.fetchPosts(next, cat, function (res) {
        var grid = document.getElementById('news-archive-grid');
        var tmp = document.createElement('div');
        tmp.innerHTML = res.html;
        while (tmp.firstChild) {
          grid.appendChild(tmp.firstChild);
        }
        btn.disabled = false;
        btn.setAttribute('data-page', next);
        if ( res.max_pages && next >= res.max_pages ) {
          btn.style.display = 'none';
        } else {
          btn.textContent = abcontactNews.load_more_label || 'Carica altri articoli';
        }
      });
    },

    fetchPosts: function (page, cat, callback ) {
      var data = new FormData();
      data.append( 'action', 'abcontact_load_more_news' );
      data.append( 'nonce', this.nonce );
      data.append( 'page', page );
      data.append( 'cat', cat );
      var excludeEl = document.getElementById('news-load-more');
      if ( excludeEl ) {
        data.append( 'exclude', excludeEl.getAttribute('data-exclude') || 0 );
      }

      fetch( this.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      } ).then(function (resp) {
        return resp.json();
      }).then(function (json) {
        if ( json.success ) {
          callback( json.data );
        } else {
          console.error('abcontact: load more error', json);
          callback( { html: '' } );
        }
      }).catch(function (err) {
        console.error(err);
        callback( { html: '' } );
      });
    }
  };

  if ( document.readyState === 'loading' ) {
    document.addEventListener('DOMContentLoaded', function () { AbContactNews.init(); });
  } else {
    AbContactNews.init();
  }

})();