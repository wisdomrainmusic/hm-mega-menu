(function () {
  var OPEN_DELAY = 90;
  var CLOSE_DELAY = 180;

  function getBreakpoint() {
    try {
      if (window.HM_MM_PUBLIC && typeof HM_MM_PUBLIC.breakpoint === "number") {
        return HM_MM_PUBLIC.breakpoint;
      }
    } catch (e) {}
    return 1024;
  }

  function isDesktop() {
    return window.innerWidth > getBreakpoint();
  }

  function closestLiHasMega(el) {
    if (!el) return null;
    return el.closest ? el.closest("li.hm-mm-has-mega") : null;
  }

  function closeAll() {
    var openLis = document.querySelectorAll("li.hm-mm-has-mega.hm-mm-open");
    openLis.forEach(function (li) {
      li.classList.remove("hm-mm-open");
      var panel = li.querySelector(":scope > .hm-mm-panel");
      if (panel) panel.setAttribute("aria-hidden", "true");
    });
  }

  function openLi(li) {
    if (!li || !isDesktop()) return;
    li.classList.add("hm-mm-open");
    var panel = li.querySelector(":scope > .hm-mm-panel");
    if (panel) panel.setAttribute("aria-hidden", "false");
  }

  function closeLi(li) {
    if (!li) return;
    li.classList.remove("hm-mm-open");
    var panel = li.querySelector(":scope > .hm-mm-panel");
    if (panel) panel.setAttribute("aria-hidden", "true");
  }

  function init() {
    var lis = document.querySelectorAll("li.hm-mm-has-mega");
    if (!lis.length) return;

    var timers = new WeakMap();

    function clearTimers(li) {
      var t = timers.get(li);
      if (!t) return;
      if (t.open) clearTimeout(t.open);
      if (t.close) clearTimeout(t.close);
      timers.set(li, { open: null, close: null });
    }

    lis.forEach(function (li) {
      timers.set(li, { open: null, close: null });

      li.addEventListener("mouseenter", function () {
        if (!isDesktop()) return;
        clearTimers(li);

        var t = timers.get(li) || {};
        t.open = setTimeout(function () {
          // single-open behavior
          closeAll();
          openLi(li);
        }, OPEN_DELAY);

        timers.set(li, t);
      });

      li.addEventListener("mouseleave", function () {
        clearTimers(li);

        var t = timers.get(li) || {};
        t.close = setTimeout(function () {
          closeLi(li);
        }, CLOSE_DELAY);

        timers.set(li, t);
      });

      // Keep open while hovering panel as well (mouseenter/mouseleave bubble on li anyway)
      var panel = li.querySelector(":scope > .hm-mm-panel");
      if (panel) {
        panel.addEventListener("mouseenter", function () {
          if (!isDesktop()) return;
          clearTimers(li);
        });
        panel.addEventListener("mouseleave", function () {
          clearTimers(li);
          var t = timers.get(li) || {};
          t.close = setTimeout(function () {
            closeLi(li);
          }, CLOSE_DELAY);
          timers.set(li, t);
        });
      }
    });

    // Escape closes
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        closeAll();
      }
    });

    // Outside click closes
    document.addEventListener("click", function (e) {
      var target = e.target;
      var li = closestLiHasMega(target);
      if (li) return; // clicked inside mega li
      closeAll();
    });

    // On resize: if switching to mobile, force close
    window.addEventListener("resize", function () {
      if (!isDesktop()) {
        closeAll();
      }
    });

    // Initial mobile safeguard
    if (!isDesktop()) {
      closeAll();
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
