(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }
  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function reindexSections(wrap) {
    var sections = qsa(".hm-mm-section", wrap);
    sections.forEach(function (sec, i) {
      sec.setAttribute("data-index", String(i));

      qsa("[name]", sec).forEach(function (el) {
        var name = el.getAttribute("name");
        if (!name) return;
        name = name.replace(/sections\[[^\]]+\]/, "sections[" + i + "]");
        el.setAttribute("name", name);
      });
    });
  }

  function addSection(wrap) {
    var tplEl = qs("#hm-mm-section-template");
    if (!tplEl) return;

    var tpl = tplEl.innerHTML;
    if (!tpl || !tpl.trim()) return;

    var index = qsa(".hm-mm-section", wrap).length;
    tpl = tpl.split("__INDEX__").join(String(index));

    var container = document.createElement("div");
    container.innerHTML = tpl.trim();

    // template HTML root is .hm-mm-section
    var node = container.firstElementChild;
    if (!node) return;

    wrap.appendChild(node);
    reindexSections(wrap);
  }

  function bindRemove(wrap) {
    wrap.addEventListener("click", function (e) {
      var btn = e.target.closest ? e.target.closest(".hm-mm-remove-section") : null;
      if (!btn) return;

      var msg =
        (window.HM_MM_ADMIN &&
          HM_MM_ADMIN.strings &&
          HM_MM_ADMIN.strings.confirmDel) ||
        "Bu bölümü silmek istiyor musun?";

      if (!window.confirm(msg)) return;

      var sec = btn.closest(".hm-mm-section");
      if (sec && sec.parentNode) {
        sec.parentNode.removeChild(sec);
        reindexSections(wrap);
      }
    });
  }

  function init() {
    var wrap = qs("#hm-mm-sections");
    if (!wrap) return;

    bindRemove(wrap);

    var addBtn = qs("#hm-mm-add-section");
    if (addBtn) {
      addBtn.addEventListener("click", function () {
        addSection(wrap);
      });
    }

    reindexSections(wrap);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
