(function ($) {
  function reindexSections($wrap) {
    $wrap.find(".hm-mm-section").each(function (i) {
      var $sec = $(this);
      $sec.attr("data-index", i);

      // Update name attributes: sections[INDEX][field]
      $sec.find("[name]").each(function () {
        var name = $(this).attr("name");
        if (!name) return;

        // Replace sections[anything] with sections[i]
        name = name.replace(/sections\[[^\]]+\]/, "sections[" + i + "]");
        $(this).attr("name", name);
      });
    });
  }

  function initSortable($wrap) {
    $wrap.sortable({
      handle: ".hm-mm-section__handle",
      axis: "y",
      tolerance: "pointer",
      update: function () {
        reindexSections($wrap);
      }
    });
  }

  function addSection($wrap) {
    var tpl = $("#hm-mm-section-template").html();
    if (!tpl) return;

    var index = $wrap.find(".hm-mm-section").length;
    tpl = tpl.split("__INDEX__").join(String(index));

    var $node = $(tpl);
    $wrap.append($node);
    reindexSections($wrap);
  }

  function bindRemove($wrap) {
    $wrap.on("click", ".hm-mm-remove-section", function () {
      if (window.HM_MM_ADMIN && HM_MM_ADMIN.strings && HM_MM_ADMIN.strings.confirmDel) {
        if (!window.confirm(HM_MM_ADMIN.strings.confirmDel)) return;
      } else {
        if (!window.confirm("Silmek istiyor musun?")) return;
      }
      $(this).closest(".hm-mm-section").remove();
      reindexSections($wrap);
    });
  }

  $(function () {
    var $wrap = $("#hm-mm-sections");
    if (!$wrap.length) return;

    initSortable($wrap);
    bindRemove($wrap);

    $("#hm-mm-add-section").on("click", function () {
      addSection($wrap);
    });

    // Ensure initial indices are consistent
    reindexSections($wrap);
  });
})(jQuery);
