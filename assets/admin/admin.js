(function () {
  try { console.log('[HM Mega Menu] admin.js FILE LOADED'); } catch(e) {}

  // Admin builder script (debug build)
  // Goal: make it undeniable that the script is loaded and the click handler fires.

  function setStatus(text, ok) {
    var el = document.getElementById("hm-mm-js-status");
    if (!el) return;
    el.textContent = text;
    el.setAttribute("data-ok", ok ? "1" : "0");
  }

  function log() {
    if (!window.console || !console.log) return;
    var args = Array.prototype.slice.call(arguments);
    args.unshift("[HM Mega Menu]");
    console.log.apply(console, args);
  }

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
    if (!tplEl) {
      setStatus("JS loaded, but template not found (#hm-mm-section-template)", false);
      log("Template element missing");
      return;
    }

    var tpl = tplEl.innerHTML;
    if (!tpl || !tpl.trim()) {
      setStatus("JS loaded, but template is empty", false);
      log("Template empty");
      return;
    }

    var index = qsa(".hm-mm-section", wrap).length;
    tpl = tpl.split("__INDEX__").join(String(index));

    var container = document.createElement("div");
    container.innerHTML = tpl.trim();

    // template HTML root is .hm-mm-section
    var node = container.firstElementChild;
    if (!node) {
      setStatus("JS loaded, but could not parse template HTML", false);
      log("Template parse failed");
      return;
    }

    wrap.appendChild(node);
    reindexSections(wrap);

    var count = qsa(".hm-mm-section", wrap).length;
    setStatus("Click OK → section added (total: " + count + ")", true);
    log("Section added", { total: count });
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
    setStatus("JS loaded (init starting)", true);
    log("init()");

    var wrap = qs("#hm-mm-sections");
    if (!wrap) {
      setStatus("JS loaded, but container not found (#hm-mm-sections)", false);
      log("Sections container missing");
      return;
    }

    bindRemove(wrap);

    // Bind add button (direct + delegated) for maximum reliability.
    var addBtn = qs("#hm-mm-add-section");
    if (addBtn) {
      addBtn.addEventListener("click", function (e) {
        if (e && e.preventDefault) e.preventDefault();
        setStatus("Click received → adding section…", true);
        log("Add button click");
        addSection(wrap);
      });
    } else {
      log("Add button missing (#hm-mm-add-section)");
    }

    document.addEventListener("click", function (e) {
      var t = e && e.target ? e.target : null;
      if (!t || !t.closest) return;
      var btn = t.closest("#hm-mm-add-section");
      if (!btn) return;
      // If direct binding failed for any reason, this still catches the click.
      if (e && e.preventDefault) e.preventDefault();
      setStatus("Click received (delegated) → adding section…", true);
      log("Add button click (delegated)");
      addSection(wrap);
    });

    reindexSections(wrap);

    var count = qsa(".hm-mm-section", wrap).length;
    setStatus("JS ready (current sections: " + count + ")", true);
    log("ready", { total: count });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
