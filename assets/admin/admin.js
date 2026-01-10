(function ($) {
  "use strict";

  var state = {
    menuId: "",
    items: [],
    targetItemId: "",
    enabled: 0,
    schema: { v: 1, rows: [] }
  };

  function setStatus(msg, isError) {
    var $s = $("#hm-mm-status");
    $s.text(msg || "");
    $s.toggleClass("is-error", !!isError);
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function fetchMenuItems(menuId) {
    return $.post(HM_MM_BUILDER.ajax_url, {
      action: "hm_mm_get_menu_items",
      nonce: HM_MM_BUILDER.nonce,
      menu_id: menuId
    });
  }

  function loadTarget(targetItemId) {
    return $.post(HM_MM_BUILDER.ajax_url, {
      action: "hm_mm_builder_load",
      nonce: HM_MM_BUILDER.nonce,
      target_item_id: targetItemId
    });
  }

  function saveTarget() {
    return $.post(HM_MM_BUILDER.ajax_url, {
      action: "hm_mm_builder_save",
      nonce: HM_MM_BUILDER.nonce,
      target_item_id: state.targetItemId,
      enabled: $("#hm-mm-enabled").is(":checked") ? 1 : 0,
      schema: JSON.stringify(state.schema || { v: 1, rows: [] })
    });
  }

  function buildItemOptions(items, selectedId) {
    var html = '<option value="">' + escHtml("—") + "</option>";
    items.forEach(function (it) {
      var pad = new Array(Math.min(it.depth, 6) + 1).join("— ");
      var sel = String(it.id) === String(selectedId) ? ' selected="selected"' : "";
      html += '<option value="' + escHtml(it.id) + '"' + sel + ">" + escHtml(pad + it.title) + "</option>";
    });
    return html;
  }

  function ensureSchema() {
    if (!state.schema || typeof state.schema !== "object") state.schema = { v: 1, rows: [] };
    if (!Array.isArray(state.schema.rows)) state.schema.rows = [];
    if (!state.schema.v) state.schema.v = 1;
  }

  function renderRows() {
    ensureSchema();
    var $body = $("#hm-mm-rows-body");
    $body.empty();

    if (!state.schema.rows.length) {
      $body.append(
        '<tr class="hm-mm-empty"><td colspan="8">' + escHtml("No rows yet. Add one to start.") + "</td></tr>"
      );
      return;
    }

    state.schema.rows.forEach(function (row) {
      var title = row.title || "";
      var sourceType = row.source_type || "menu_node";
      var sourceId = row.source_id || "";
      var cols = row.columns || 4;
      var depth = row.depth || 3;
      var heading = row.show_heading ? 1 : 0;

      var sourceTypeHtml =
        '<select class="hm-mm-row-source-type" disabled>' +
        '<option value="menu_node" selected>menu_node</option>' +
        "</select>";

      var sourceIdHtml =
        '<select class="hm-mm-row-source-id">' +
        buildItemOptions(state.items, sourceId) +
        "</select>";

      var colsHtml = '<select class="hm-mm-row-columns">';
      for (var c = 1; c <= 6; c++) {
        colsHtml += '<option value="' + c + '"' + (c === cols ? ' selected="selected"' : "") + ">" + c + "</option>";
      }
      colsHtml += "</select>";

      var depthHtml = '<select class="hm-mm-row-depth">';
      for (var d = 1; d <= 3; d++) {
        depthHtml += '<option value="' + d + '"' + (d === depth ? ' selected="selected"' : "") + ">" + d + "</option>";
      }
      depthHtml += "</select>";

      var headingHtml =
        '<label class="hm-mm-mini-toggle"><input type="checkbox" class="hm-mm-row-heading" ' +
        (heading ? 'checked="checked"' : "") +
        " /> <span>On</span></label>";

      var tr =
        '<tr class="hm-mm-row" data-row-id="' +
        escHtml(row.id) +
        '">' +
        '<td class="hm-mm-drag"><span class="hm-mm-handle">≡</span></td>' +
        '<td><input type="text" class="hm-mm-row-title" value="' +
        escHtml(title) +
        '" /></td>' +
        "<td>" +
        sourceTypeHtml +
        "</td>" +
        "<td>" +
        sourceIdHtml +
        "</td>" +
        "<td>" +
        colsHtml +
        "</td>" +
        "<td>" +
        depthHtml +
        "</td>" +
        "<td>" +
        headingHtml +
        "</td>" +
        '<td class="hm-mm-actions"><button type="button" class="button-link-delete hm-mm-row-remove">Remove</button></td>' +
        "</tr>";

      $body.append(tr);
    });

    $body.sortable({
      handle: ".hm-mm-handle",
      items: "tr.hm-mm-row",
      axis: "y",
      update: function () {
        syncRowsFromDom();
      }
    });
  }

  function syncRowsFromDom() {
    var rows = [];
    $("#hm-mm-rows-body tr.hm-mm-row").each(function () {
      var $tr = $(this);
      var id = $tr.data("row-id");
      rows.push({
        id: id,
        title: $tr.find(".hm-mm-row-title").val() || "",
        source_type: "menu_node",
        source_id: parseInt($tr.find(".hm-mm-row-source-id").val() || "0", 10) || 0,
        columns: parseInt($tr.find(".hm-mm-row-columns").val() || "4", 10) || 4,
        depth: parseInt($tr.find(".hm-mm-row-depth").val() || "3", 10) || 3,
        show_heading: $tr.find(".hm-mm-row-heading").is(":checked") ? 1 : 0
      });
    });
    state.schema.rows = rows;
  }

  function addRow() {
    ensureSchema();
    var uid = "row_" + Math.random().toString(16).slice(2, 10);
    state.schema.rows.push({
      id: uid,
      title: "",
      source_type: "menu_node",
      source_id: 0,
      columns: 4,
      depth: 3,
      show_heading: 1
    });
    renderRows();
  }

  function bindRowEvents() {
    $("#hm-mm-rows-body")
      .on("change input", ".hm-mm-row-title, .hm-mm-row-source-id, .hm-mm-row-columns, .hm-mm-row-depth, .hm-mm-row-heading", function () {
        syncRowsFromDom();
      })
      .on("click", ".hm-mm-row-remove", function () {
        if (!confirm(HM_MM_BUILDER.i18n.confirm_remove)) return;
        var $tr = $(this).closest("tr.hm-mm-row");
        var id = $tr.data("row-id");
        state.schema.rows = (state.schema.rows || []).filter(function (r) {
          return String(r.id) !== String(id);
        });
        renderRows();
      });
  }

  function setControlsEnabled(enabled) {
    $("#hm-mm-target-item").prop("disabled", !enabled);
    $("#hm-mm-enabled").prop("disabled", !enabled);
    $("#hm-mm-save").prop("disabled", !enabled);
    $("#hm-mm-add-row").prop("disabled", !enabled);
  }

  $(function () {
    bindRowEvents();

    $("#hm-mm-menu-select").on("change", function () {
      var menuId = $(this).val() || "";
      state.menuId = menuId;
      state.items = [];
      state.targetItemId = "";
      setStatus("");
      setControlsEnabled(false);

      $("#hm-mm-target-item").html('<option value="">' + escHtml("Loading...") + "</option>");

      if (!menuId) {
        $("#hm-mm-target-item").prop("disabled", true).html(
          '<option value="">' + escHtml("Select a menu first") + "</option>"
        );
        return;
      }

      fetchMenuItems(menuId)
        .done(function (res) {
          if (!res || !res.success) {
            setStatus(HM_MM_BUILDER.i18n.load_failed, true);
            return;
          }
          state.items = res.data.items || [];
          $("#hm-mm-target-item").prop("disabled", false).html(
            '<option value="">' + escHtml("Select a target item") + "</option>" +
              state.items
                .map(function (it) {
                  var pad = new Array(Math.min(it.depth, 6) + 1).join("— ");
                  return '<option value="' + escHtml(it.id) + '">' + escHtml(pad + it.title) + "</option>";
                })
                .join("")
          );
        })
        .fail(function () {
          setStatus(HM_MM_BUILDER.i18n.load_failed, true);
        });
    });

    $("#hm-mm-target-item").on("change", function () {
      var targetId = $(this).val() || "";
      state.targetItemId = targetId;
      setStatus("");

      if (!targetId) {
        setControlsEnabled(false);
        $("#hm-mm-rows-body").html(
          '<tr class="hm-mm-empty"><td colspan="8">' + escHtml("No rows yet. Add one to start.") + "</td></tr>"
        );
        return;
      }

      setControlsEnabled(true);
      setStatus("Loading...");

      loadTarget(targetId)
        .done(function (res) {
          if (!res || !res.success) {
            setStatus(HM_MM_BUILDER.i18n.load_failed, true);
            return;
          }
          state.enabled = res.data.enabled ? 1 : 0;
          state.schema = res.data.schema || { v: 1, rows: [] };

          $("#hm-mm-enabled").prop("checked", !!state.enabled);
          renderRows();
          setStatus("");
        })
        .fail(function () {
          setStatus(HM_MM_BUILDER.i18n.load_failed, true);
        });
    });

    $("#hm-mm-add-row").on("click", function () {
      addRow();
    });

    $("#hm-mm-save").on("click", function () {
      if (!state.targetItemId) {
        setStatus(HM_MM_BUILDER.i18n.choose_menu, true);
        return;
      }
      syncRowsFromDom();
      setStatus(HM_MM_BUILDER.i18n.saving);

      saveTarget()
        .done(function (res) {
          if (!res || !res.success) {
            setStatus(HM_MM_BUILDER.i18n.save_failed, true);
            return;
          }
          state.enabled = res.data.enabled ? 1 : 0;
          state.schema = res.data.schema || state.schema;
          $("#hm-mm-enabled").prop("checked", !!state.enabled);
          renderRows();
          setStatus(HM_MM_BUILDER.i18n.saved);
        })
        .fail(function () {
          setStatus(HM_MM_BUILDER.i18n.save_failed, true);
        });
    });
  });
})(jQuery);
