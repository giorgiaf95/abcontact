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

      // Guard: attach load-more handler only if the button exists
      var loadMoreBtn = document.getElementById('news-load-more');
      if ( loadMoreBtn ) {
        loadMoreBtn.addEventListener('click', function (e) {
          e.preventDefault();
          self.loadMore(this);
        });
      }

      // Filters: multi-category + search + apply button
      var filterChips = document.querySelectorAll('.news-filter-chip');
      if ( filterChips && filterChips.length ) {
        filterChips.forEach(function (btn) {
          btn.addEventListener('click', function () {
            this.classList.toggle('is-selected');
          });
        });
      }

      var applyBtn = document.getElementById('news-filter-apply');
      if ( applyBtn ) {
        applyBtn.addEventListener('click', function () {
          self.applyFilters();
        });
      }

      var clearBtn = document.getElementById('news-filter-clear');
      if ( clearBtn ) {
        clearBtn.addEventListener('click', function () {
          self.clearFilters();
        });
      }

      var searchInput = document.getElementById('news-filter-search');
      if ( searchInput ) {
        searchInput.addEventListener('keydown', function (e) {
          if ( e.key === 'Enter' ) {
            e.preventDefault();
            self.applyFilters();
          }
        });
      }

      this.bindMobileFiltersToggle();
    },

    bindMobileFiltersToggle: function () {
      var catsWrap = document.querySelector('.news-filters-cats-wrap');
      var toggleBtn = document.getElementById('news-filters-toggle');
      if ( !catsWrap || !toggleBtn ) {
        return;
      }

      toggleBtn.addEventListener('click', function () {
        var isOpen = catsWrap.classList.toggle('is-open');
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    },

    applyFilters: function () {
      var grid = document.getElementById('news-archive-grid');
      var loadMoreBtn = document.getElementById('news-load-more');
      var filters = this.getCurrentFilters();

      if ( loadMoreBtn ) {
        loadMoreBtn.setAttribute('data-page', 1);
        loadMoreBtn.style.display = '';
        loadMoreBtn.disabled = false;
        loadMoreBtn.textContent = (window.abcontactNews && window.abcontactNews.load_more_label) || 'Carica altri articoli';
      }

      this.fetchPosts(1, filters, function (res) {
        if ( grid ) {
          if ( res.html ) {
            grid.innerHTML = res.html;
          } else {
            grid.innerHTML = '<p class="news-empty">' + (((window.abcontactNews && window.abcontactNews.no_results_label) || 'Nessun articolo trovato con i filtri selezionati.')) + '</p>';
          }
        }
        if ( loadMoreBtn && ( !res.max_pages || res.max_pages <= 1 ) ) {
          loadMoreBtn.style.display = 'none';
        }

        if ( window.matchMedia('(max-width: 760px)').matches ) {
          var catsWrap = document.querySelector('.news-filters-cats-wrap');
          var toggleBtn = document.getElementById('news-filters-toggle');
          if ( catsWrap && toggleBtn ) {
            catsWrap.classList.remove('is-open');
            toggleBtn.setAttribute('aria-expanded', 'false');
          }
        }
      });
    },

    clearFilters: function () {
      var searchInput = document.getElementById('news-filter-search');
      var filterChips = document.querySelectorAll('.news-filter-chip.is-selected');
      if ( searchInput ) {
        searchInput.value = '';
      }
      if ( filterChips && filterChips.length ) {
        filterChips.forEach(function (chip) {
          chip.classList.remove('is-selected');
        });
      }
      this.applyFilters();
    },

    getCurrentFilters: function () {
      var selected = [];
      var chips = document.querySelectorAll('.news-filter-chip.is-selected');
      if ( chips && chips.length ) {
        chips.forEach(function (chip) {
          var id = parseInt(chip.getAttribute('data-cat'), 10);
          if ( id > 0 ) {
            selected.push(id);
          }
        });
      }

      var searchInput = document.getElementById('news-filter-search');
      var searchTerm = searchInput ? (searchInput.value || '').trim() : '';

      return {
        cats: selected,
        search: searchTerm
      };
    },

    loadMore: function (btn) {
      var page = parseInt(btn.getAttribute('data-page'), 10) || 1;
      var next = page + 1;
      var filters = this.getCurrentFilters();

      btn.disabled = true;
      btn.textContent = ((window.abcontactNews && window.abcontactNews.loading_label) || 'Caricamento...');

      this.fetchPosts(next, filters, function (res) {
        var grid = document.getElementById('news-archive-grid');
        if (!grid) {
          // nothing to append to
          btn.disabled = false;
          btn.textContent = (window.abcontactNews && window.abcontactNews.load_more_label) || 'Carica altri articoli';
          return;
        }
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
          btn.textContent = (window.abcontactNews && window.abcontactNews.load_more_label) || 'Carica altri articoli';
        }
      });
    },

    fetchPosts: function (page, filters, callback ) {
      var data = new FormData();
      data.append( 'action', 'abcontact_load_more_news' );
      data.append( 'nonce', this.nonce );
      data.append( 'page', page );
      data.append( 'search', filters && filters.search ? filters.search : '' );
      if ( filters && filters.cats && filters.cats.length ) {
        filters.cats.forEach(function (catId) {
          data.append( 'cats[]', catId );
        });
      } else {
        data.append( 'cat', 0 );
      }
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
        if ( json && json.success ) {
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
