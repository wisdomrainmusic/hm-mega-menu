(function () {
  "use strict";

  var OPEN_CLASS = "hm-mm-open";
  var HOVER_OPEN_DELAY = 70;
  var HOVER_CLOSE_DELAY = 140;

  var openTimer = null;
  var closeTimer = null;

  function isDesktop() {
    return window.matchMedia && window.matchMedia("(min-width: 1025px)").matches;
  }

  function closestLi(el) {
    while (el && el !== document.body) {
      if (el.tagName && el.tagName.toLowerCase() === "li") return el;
      el = el.parentNode;
    }
    return null;
  }

  function findMegaLis() {
    return Array.prototype.slice.call(document.querySelectorAll("li > .hm-mega-panel")).map(function (panel) {
      return panel.parentNode;
    });
  }

  function closeAll(exceptLi) {
    findMegaLis().forEach(function (li) {
      if (exceptLi && li === exceptLi) return;
      li.classList.remove(OPEN_CLASS);
      var panel = li.querySelector(":scope > .hm-mega-panel");
      if (panel) panel.setAttribute("aria-hidden", "true");
    });
  }

  function openLi(li) {
    if (!li) return;
    closeAll(li);
    li.classList.add(OPEN_CLASS);
    var panel = li.querySelector(":scope > .hm-mega-panel");
    if (panel) panel.setAttribute("aria-hidden", "false");
  }

  function closeLi(li) {
    if (!li) return;
    li.classList.remove(OPEN_CLASS);
    var panel = li.querySelector(":scope > .hm-mega-panel");
    if (panel) panel.setAttribute("aria-hidden", "true");
  }

  function attach() {
    if (!isDesktop()) return;

    var megaLis = findMegaLis();
    if (!megaLis.length) return;

    megaLis.forEach(function (li) {
      li.addEventListener("mouseenter", function () {
        if (!isDesktop()) return;
        if (closeTimer) clearTimeout(closeTimer);
        openTimer = setTimeout(function () {
          openLi(li);
        }, HOVER_OPEN_DELAY);
      });

      li.addEventListener("mouseleave", function () {
        if (!isDesktop()) return;
        if (openTimer) clearTimeout(openTimer);
        closeTimer = setTimeout(function () {
          closeLi(li);
        }, HOVER_CLOSE_DELAY);
      });

      // Prevent immediate close when moving within panel
      var panel = li.querySelector(":scope > .hm-mega-panel");
      if (panel) {
        panel.addEventListener("mouseenter", function () {
          if (closeTimer) clearTimeout(closeTimer);
        });
        panel.addEventListener("mouseleave", function () {
          if (!isDesktop()) return;
          closeTimer = setTimeout(function () {
            closeLi(li);
          }, HOVER_CLOSE_DELAY);
        });
      }
    });

    // Close on outside click
    document.addEventListener("click", function (e) {
      if (!isDesktop()) return;
      var target = e.target;
      var li = closestLi(target);
      // If click happened inside an open mega li, keep it open
      if (li && li.classList && li.classList.contains(OPEN_CLASS)) return;

      // If click is inside any mega panel, keep open
      var insidePanel = target && target.closest ? target.closest(".hm-mega-panel") : null;
      if (insidePanel) return;

      closeAll(null);
    });

    // Close on Escape
    document.addEventListener("keydown", function (e) {
      if (!isDesktop()) return;
      if (e.key === "Escape") closeAll(null);
    });

    // Close all on resize crossing breakpoint
    window.addEventListener("resize", function () {
      if (!isDesktop()) {
        closeAll(null);
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", attach);
  } else {
    attach();
  }
})();
