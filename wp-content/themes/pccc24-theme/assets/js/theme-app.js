/**
 * PCCC24 Theme Frontend Application (Replicated 100% from D:\Code\PCCC24\src\js\)
 * Includes: OwlCarousel init, Lightbox, CustomAlert, ProductPreview, SortSearch,
 * ProductFilter, SupportApp, Mobile Menu Drawer, LocalStorage Cart & Order Checkout.
 */
(function (window, $) {
  "use strict";

  var CFG = window.PCCC24_CONFIG || {
    baseUrl: window.baseUrl || "/",
    restUrl: "/wp-json/pccc24/v1",
    noImageUrl: "/wp-content/themes/pccc24-theme/assets/images/no-image.png",
    supports: [],
  };

  /* ---------- 1. URL & Formatting Helpers (from function.js) ---------- */

  window.getUrlVars = function () {
    var vars = {};
    window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
      vars[key] = decodeURIComponent(value);
    });
    return vars;
  };

  window.removeUrlParam = function (key, sourceURL) {
    var url = sourceURL || window.location.href;
    var rtn = url.split("?")[0],
      param,
      params_arr = [],
      queryString = url.indexOf("?") !== -1 ? url.split("?")[1] : "";
    if (queryString !== "") {
      params_arr = queryString.split("&");
      for (var i = params_arr.length - 1; i >= 0; i -= 1) {
        param = params_arr[i].split("=")[0];
        if (param === key) {
          params_arr.splice(i, 1);
        }
      }
      if (params_arr.length > 0) {
        rtn = rtn + "?" + params_arr.join("&");
      }
    }
    return rtn;
  };

  window.replaceUrlParam = function (paramName, paramValue, sourceURL) {
    var url = sourceURL || window.location.href;
    if (paramValue == null) {
      paramValue = "";
    }
    var pattern = new RegExp("\\b(" + paramName + "=).*?(&|#|$)");
    if (url.search(pattern) >= 0) {
      return url.replace(pattern, "$1" + encodeURIComponent(paramValue) + "$2");
    }
    url = url.replace(/[?#]$/, "");
    return url + (url.indexOf("?") > 0 ? "&" : "?") + paramName + "=" + encodeURIComponent(paramValue);
  };

  window.formatMoney = function (amount, decimalCount, decimal, thousands) {
    decimalCount = decimalCount === undefined ? 0 : Math.abs(decimalCount);
    decimal = decimal === undefined ? "," : decimal;
    thousands = thousands === undefined ? "." : thousands;
    try {
      var negativeSign = amount < 0 ? "-" : "";
      var i = parseInt((amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)), 10).toString();
      var j = i.length > 3 ? i.length % 3 : 0;
      return (
        negativeSign +
        (j ? i.substr(0, j) + thousands : "") +
        i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) +
        (decimalCount
          ? decimal +
            Math.abs(amount - i)
              .toFixed(decimalCount)
              .slice(2)
          : "")
      );
    } catch (e) {
      return "0";
    }
  };

  window.priceProduct = function (price) {
    var p = Number(price) || 0;
    return p > 0 ? window.formatMoney(p, 0, ",", ".") + "đ" : "Liên hệ";
  };

  window.imagePath = function (photoPath) {
    if (!photoPath) return CFG.noImageUrl;
    if (/^https?:\/\//i.test(photoPath) || photoPath.indexOf("/") === 0) {
      return photoPath;
    }
    return CFG.baseUrl.replace(/\/$/, "") + "/wp-content/uploads/pccc24/" + photoPath.replace(/^\/+/, "");
  };

  window.applyFilter = function (ids) {
    var paramValue = (ids || []).join(",");
    var href = window.replaceUrlParam("propertyId", paramValue);
    href = window.removeUrlParam("page", href);
    window.location.href = href;
  };

  window.closePreview = function () {
    $("#preview-box").addClass("hidden");
  };

  window.closeMenu = function () {
    $(".menu-mobile").addClass("hidden");
  };

  /* ---------- 2. CustomAlert (from custom-alert.js) ---------- */

  window.CustomAlert = function (conf, callback) {
    conf = conf || {};
    var title = conf.title !== undefined ? conf.title : "Thông báo";
    var body = conf.body || "Thành công";
    var danger = !!conf.danger;
    $(".pccc24-custom-alert-overlay").remove();
    var html =
      '<div class="pccc24-custom-alert-overlay fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.45);z-index:9999;">' +
      '<div class="bg-white rounded-lg shadow-xl p-5 max-w-md w-full mx-4 relative">' +
      (title
        ? '<h3 class="text-lg font-bold mb-2 ' + (danger ? "text-red-600" : "text-slate-800") + '">' + title + "</h3>"
        : "") +
      '<div class="text-base text-slate-700 mb-4">' +
      body +
      "</div>" +
      '<div class="text-right">' +
      '<button type="button" class="pccc24-alert-ok px-4 py-1.5 rounded text-white ' +
      (danger ? "bg-red-600 hover:bg-red-500" : "bg-violet-600 hover:bg-violet-500") +
      '">Đóng</button>' +
      "</div>" +
      "</div>" +
      "</div>";
    var $el = $(html).appendTo("body");
    $el.find(".pccc24-alert-ok").on("click", function () {
      $el.remove();
      if (typeof callback === "function") callback(true);
    });
  };

  /* ---------- 3. Lightbox Plugin (from function.js) ---------- */

  $.lightbox = { version: "1.0" };
  $.fn.lightbox = function (params) {
    var settings = $.extend(
      {
        target: "lightbox",
        classname: ".lightbox-box",
      },
      params || {}
    );

    return $(this)
      .off("click.pcccLightbox")
      .on("click.pcccLightbox", function (e) {
        e.preventDefault();
        var src = $(this).data("href") || $(this).attr("src");
        if (!src) return;
        $(".lightbox-box").remove();
        var markup =
          '<div class="lightbox-box" style="display:block;">' +
          '<span class="lightbox-close"><span class="x-lightbox"></span></span>' +
          '<div id="lightbox-container"><img id="lightboxImage" src="' +
          src +
          '" /></div>' +
          "</div>";
        var $box = $(markup).appendTo("body");
        $box.find(".lightbox-close").on("click", function () {
          $box.remove();
        });
        $box.on("click", function (ev) {
          if (ev.target.tagName !== "IMG") {
            $box.remove();
          }
        });
      });
  };

  /* ---------- 4. Quick View Product Modal (window.previewProduct) ---------- */

  window.previewProduct = function (id) {
    if (!id) return;
    var $container = $("#ProductPreviewApp");
    var $box = $("#preview-box");
    if (!$container.length) return;

    $container.html('<div class="p-8 text-center text-gray-500">Đang tải thông tin sản phẩm...</div>');
    $box.removeClass("hidden");

    $.getJSON(CFG.restUrl + "/products/" + encodeURIComponent(id))
      .done(function (product) {
        var img = window.imagePath(product.image_url);
        var priceText = window.priceProduct(product.price);
        var url = product.url || "/san-pham/" + product.slug + "/" + product.id;
        var html =
          '<span onclick="closePreview()" class="rounded-full border bg-slate-100 cursor-pointer hover:text-white hover:bg-amber-200 flex items-center justify-center p-2 absolute -top-5 -right-5 z-20">' +
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
          "</span>" +
          '<div class="p-5 max-h-full">' +
          '<div class="grid md:grid-cols-4 xs-s:grid-cols-1 gap-4 max-h-full">' +
          '<div class="col-span-2 max-h-full">' +
          '<div class="w-full flex items-center justify-center h-full pr-4">' +
          '<img class="preview-image" src="' +
          img +
          '" alt="' +
          $("<div>").text(product.name || "").html() +
          '" />' +
          "</div>" +
          "</div>" +
          '<div class="col-span-2">' +
          '<h1 class="text-xl font-medium">' +
          '<a href="' +
          url +
          '" class="hover:text-red-600">' +
          $("<div>").text(product.name || "").html() +
          "</a>" +
          "</h1>" +
          '<div class="flex mt-3 items-center justify-between">' +
          '<span class="text-amber-600 text-lg py-2 font-semibold">' +
          priceText +
          "</span>" +
          '<button type="button" onclick="addCart(' +
          Number(product.id) +
          ', 1)" class="rounded px-3 py-1.5 bg-violet-600 hover:bg-violet-500 text-white text-sm flex items-center gap-1 cursor-pointer">' +
          '<i class="bx bx-cart"></i> Thêm vào giỏ' +
          "</button>" +
          "</div>" +
          '<div class="custom-scroll-bar overflow-auto content-preview mt-4">' +
          "<div>" +
          (product.content || "") +
          "</div>" +
          "</div>" +
          '<div class="mt-3 text-right">' +
          '<a href="' +
          url +
          '" class="text-sky-600 hover:underline text-sm">Xem chi tiết sản phẩm &raquo;</a>' +
          "</div>" +
          "</div>" +
          "</div>" +
          "</div>";
        $container.html(html);
      })
      .fail(function () {
        $box.addClass("hidden");
        window.CustomAlert({
          title: "Thông báo",
          body: "Không tải được thông tin sản phẩm.",
          danger: true,
        });
      });
  };

  /* ---------- 5. Shopping Cart (localStorage) & Checkout (/wp-json/pccc24/v1/orders) ---------- */

  var CART_KEY = "pccc24_cart";

  function getCart() {
    try {
      var raw = window.localStorage.getItem(CART_KEY);
      var parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(items) {
    try {
      window.localStorage.setItem(CART_KEY, JSON.stringify(items));
    } catch (e) {}
    updateCartBadge();
  }

  function updateCartBadge() {
    var items = getCart();
    var totalQty = items.reduce(function (sum, it) {
      return sum + (parseInt(it.quantity, 10) || 1);
    }, 0);
    $(".countCart").text(totalQty);
    if (totalQty > 0) {
      $(".countCart").removeClass("hidden");
    }
  }

  window.addCart = function (id, qty) {
    if (window.event && typeof window.event.preventDefault === "function") {
      window.event.preventDefault();
    }
    var productId = parseInt(id, 10);
    var quantity = Math.max(1, parseInt(qty, 10) || 1);
    if (!productId) return;

    $.getJSON(CFG.restUrl + "/products/" + productId)
      .done(function (product) {
        var cart = getCart();
        var existing = null;
        for (var i = 0; i < cart.length; i++) {
          if (parseInt(cart[i].id, 10) === productId) {
            existing = cart[i];
            break;
          }
        }
        if (existing) {
          existing.quantity = (parseInt(existing.quantity, 10) || 1) + quantity;
        } else {
          cart.push({
            id: productId,
            name: product.name || "Sản phẩm #" + productId,
            price: parseInt(product.price, 10) || 0,
            image_url: window.imagePath(product.image_url),
            url: product.url || "/san-pham/" + product.slug + "/" + productId,
            quantity: quantity,
          });
        }
        saveCart(cart);
        window.openCartModal();
      })
      .fail(function () {
        var cart = getCart();
        cart.push({
          id: productId,
          name: "Sản phẩm #" + productId,
          price: 0,
          image_url: CFG.noImageUrl,
          url: "#",
          quantity: quantity,
        });
        saveCart(cart);
        window.openCartModal();
      });
  };

  window.updateCartItemQty = function (productId, deltaOrValue, isExact) {
    var cart = getCart();
    var next = [];
    for (var i = 0; i < cart.length; i++) {
      var item = cart[i];
      if (parseInt(item.id, 10) === parseInt(productId, 10)) {
        var newQty = isExact ? parseInt(deltaOrValue, 10) : (parseInt(item.quantity, 10) || 1) + parseInt(deltaOrValue, 10);
        if (newQty > 0) {
          item.quantity = newQty;
          next.push(item);
        }
      } else {
        next.push(item);
      }
    }
    saveCart(next);
    renderCartModalContent();
  };

  window.removeCartItem = function (productId) {
    var cart = getCart().filter(function (it) {
      return parseInt(it.id, 10) !== parseInt(productId, 10);
    });
    saveCart(cart);
    renderCartModalContent();
  };

  window.closeCartModal = function () {
    $("#pccc24-cart-modal").addClass("hidden");
  };

  function renderCartModalContent() {
    var $modal = $("#pccc24-cart-modal");
    if (!$modal.length) return;
    var cart = getCart();
    var $list = $modal.find("#pccc24-cart-items");
    var $total = $modal.find("#pccc24-cart-total");

    if (cart.length === 0) {
      $list.html('<p class="text-center text-gray-500 py-6">Chưa có sản phẩm nào trong giỏ hàng.</p>');
      $total.text("Liên hệ");
      return;
    }

    var totalAmount = 0;
    var rowsHtml = "";
    for (var i = 0; i < cart.length; i++) {
      var it = cart[i];
      var sub = (parseInt(it.price, 10) || 0) * (parseInt(it.quantity, 10) || 1);
      totalAmount += sub;
      rowsHtml +=
        '<div class="flex items-center justify-between gap-3 py-3 border-b">' +
        '<div class="flex items-center gap-3 flex-1 min-w-0">' +
        '<img src="' +
        (it.image_url || CFG.noImageUrl) +
        '" class="w-14 h-14 object-contain rounded border bg-white" alt="" />' +
        '<div class="min-w-0 flex-1">' +
        '<a href="' +
        (it.url || "#") +
        '" class="text-sm font-medium text-slate-800 hover:text-red-600 limit-line limit-line-2">' +
        $("<div>").text(it.name).html() +
        "</a>" +
        '<div class="text-xs text-amber-600 mt-0.5">' +
        window.priceProduct(it.price) +
        "</div>" +
        "</div>" +
        "</div>" +
        '<div class="flex items-center gap-1">' +
        '<button type="button" onclick="updateCartItemQty(' +
        it.id +
        ', -1)" class="w-7 h-7 border rounded hover:bg-slate-100">-</button>' +
        '<input type="text" readonly value="' +
        it.quantity +
        '" class="w-10 h-7 text-center border rounded text-sm" />' +
        '<button type="button" onclick="updateCartItemQty(' +
        it.id +
        ', 1)" class="w-7 h-7 border rounded hover:bg-slate-100">+</button>' +
        '<button type="button" onclick="removeCartItem(' +
        it.id +
        ')" class="ml-2 text-red-500 hover:text-red-700 text-lg leading-none" title="Xóa">&times;</button>' +
        "</div>" +
        "</div>";
    }
    $list.html(rowsHtml);
    $total.text(totalAmount > 0 ? window.formatMoney(totalAmount, 0, ",", ".") + "đ" : "Liên hệ báo giá");
  }

  window.openCartModal = function () {
    var $modal = $("#pccc24-cart-modal");
    if (!$modal.length) return;
    renderCartModalContent();
    $modal.removeClass("hidden");
  };

  function initCartOrderForm() {
    $(document).on("submit", "#pccc24-order-form", function (e) {
      e.preventDefault();
      var cart = getCart();
      if (cart.length === 0) {
        window.CustomAlert({
          title: "Thông báo",
          body: "Chưa có sản phẩm nào trong giỏ hàng!",
          danger: true,
        });
        return;
      }
      var $form = $(this);
      var payload = {
        name: $.trim($form.find('[name="name"]').val()),
        phone: $.trim($form.find('[name="phone"]').val()),
        email: $.trim($form.find('[name="email"]').val()),
        address: $.trim($form.find('[name="address"]').val()),
        note: $.trim($form.find('[name="note"]').val()),
        items: cart.map(function (it) {
          return { id: parseInt(it.id, 10), quantity: parseInt(it.quantity, 10) || 1 };
        }),
      };

      if (!payload.name || !payload.phone) {
        window.CustomAlert({
          title: "Thông báo",
          body: "Vui lòng nhập đầy đủ Họ tên và Số điện thoại!",
          danger: true,
        });
        return;
      }

      var $btn = $form.find('button[type="submit"]');
      $btn.prop("disabled", true).text("Đang gửi...");

      $.ajax({
        url: CFG.restUrl + "/orders",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify(payload),
      })
        .done(function (res) {
          saveCart([]);
          $form[0].reset();
          window.closeCartModal();
          window.CustomAlert({
            title: "Thông báo",
            body: (res && res.message) || "Đặt hàng thành công! Chúng tôi sẽ liên hệ sớm nhất.",
          });
        })
        .fail(function (xhr) {
          var msg =
            (xhr.responseJSON && xhr.responseJSON.message) ||
            "Vui lòng nhập họ tên và số điện thoại hợp lệ!";
          window.CustomAlert({
            title: "Thông báo",
            body: msg,
            danger: true,
          });
        })
        .always(function () {
          $btn.prop("disabled", false).text("Đặt hàng");
        });
    });
  }

  /* ---------- 6. SortSearch Widget (replicates SortSearch.jsx) ---------- */

  function initSortSearch() {
    var $containers = $(".SortSearch");
    if (!$containers.length) return;

    var sortedLists = [
      { id: 5, name: "Mặc định" },
      { id: 1, name: "A -> Z" },
      { id: 2, name: "Z -> A" },
      { id: 3, name: "Giá tăng dần" },
      { id: 4, name: "Giá giảm dần" },
    ];

    $containers.each(function () {
      var $el = $(this);
      var props = {};
      try {
        props = JSON.parse($el.attr("data-props") || "{}");
      } catch (e) {}
      var currentSort = parseInt(props.sorted || window.getUrlVars()["sorted"] || 5, 10);
      var selectedObj =
        sortedLists.find(function (s) {
          return s.id === currentSort;
        }) || sortedLists[0];

      var optionsHtml = sortedLists
        .map(function (s) {
          return (
            '<div data-sort-id="' +
            s.id +
            '" class="pccc-sort-item py-1 cursor-pointer hover:text-sky-500">' +
            s.name +
            "</div>"
          );
        })
        .join("");

      var html =
        '<div class="relative">' +
        '<span class="pccc-sort-trigger flex gap-2 cursor-pointer items-center justify-between">' +
        '<span class="sm:block xs-s:hidden">Sắp xếp theo:</span>' +
        '<span class="sm:block xs-s:hidden font-medium">' +
        selectedObj.name +
        "</span>" +
        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="sm:h-5 sm:w-5 xs-s:h-6 xs-s:w-6">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />' +
        "</svg>" +
        "</span>" +
        '<div class="pccc-sort-dropdown hidden absolute right-0 z-20 top-8">' +
        '<div class="relative p-3 w-36 z-20 bg-white rounded shadow">' +
        optionsHtml +
        "</div>" +
        '<div class="pccc-sort-backdrop fixed inset-0 bg-black opacity-0 z-10"></div>' +
        "</div>" +
        "</div>";

      $el.html(html);

      $el.find(".pccc-sort-trigger").on("click", function () {
        $el.find(".pccc-sort-dropdown").toggleClass("hidden");
      });
      $el.find(".pccc-sort-backdrop").on("click", function () {
        $el.find(".pccc-sort-dropdown").addClass("hidden");
      });
      $el.find(".pccc-sort-item").on("click", function () {
        var sortId = $(this).data("sort-id");
        var href = window.replaceUrlParam("sorted", sortId);
        href = window.removeUrlParam("page", href);
        window.location.href = href;
      });
    });
  }

  /* ---------- 7. ProductFilter Widget (replicates ProductFilter.jsx) ---------- */

  function initProductFilter() {
    var $el = $("#filterProduct");
    if (!$el.length) return;
    var props = {};
    try {
      props = JSON.parse($el.attr("data-props") || "{}");
    } catch (e) {}
    var groups = props.category_properties || [];
    if (!groups.length) return;

    var rawIds = window.getUrlVars()["propertyId"] || "";
    var activeIds = rawIds
      ? rawIds
          .split(/,|%2C/)
          .map(function (x) {
            return String(x).trim();
          })
          .filter(Boolean)
      : [];

    function render() {
      var listHtml = '<ul class="display-grid">';
      for (var i = 0; i < groups.length; i++) {
        var g = groups[i];
        var children = g.childrens || g.values || [];
        if (!children.length) continue;
        listHtml +=
          '<li class="display-grid mb-2">' +
          '<span class="mr-2 font-medium">' +
          $("<div>").text(g.name).html() +
          ":</span>" +
          '<div class="flex flex-wrap mt-2 gap-2">';
        for (var k = 0; k < children.length; k++) {
          var ch = children[k];
          var cid = String(ch.id);
          var isSelected = activeIds.indexOf(cid) !== -1;
          listHtml +=
            '<span data-prop-id="' +
            cid +
            '" class="pccc-prop-item border rounded px-2 py-0.5 cursor-pointer text-sm ' +
            (isSelected ? "border-red-500 bg-red-50 text-red-600 font-medium" : "hover:border-slate-700") +
            '">' +
            $("<div>").text(ch.name).html() +
            "</span>";
        }
        listHtml += "</div></li>";
      }
      listHtml += "</ul>";

      var actionsHtml = '<div class="mt-3">';
      if (activeIds.length > 0) {
        actionsHtml +=
          '<button type="button" class="pccc-filter-apply border rounded px-2 py-0.5 cursor-pointer hover:border-slate-700 mr-3">Áp dụng</button>' +
          '<button type="button" class="pccc-filter-clear border border-red-500 rounded px-2 py-0.5 cursor-pointer hover:border-red-200 hover:bg-red-200">Xóa bộ lọc</button>';
      } else {
        actionsHtml +=
          '<button type="button" disabled class="border rounded px-2 py-0.5 opacity-60 cursor-not-allowed">Áp dụng</button>';
      }
      actionsHtml += "</div>";

      $el.html(listHtml + actionsHtml);

      $el.find(".pccc-prop-item").on("click", function () {
        var id = String($(this).data("prop-id"));
        var idx = activeIds.indexOf(id);
        if (idx !== -1) {
          activeIds.splice(idx, 1);
        } else {
          activeIds.push(id);
        }
        render();
      });

      $el.find(".pccc-filter-apply").on("click", function () {
        window.applyFilter(activeIds);
      });

      $el.find(".pccc-filter-clear").on("click", function () {
        var href = window.removeUrlParam("propertyId", window.location.href);
        href = window.removeUrlParam("page", href);
        window.location.href = href;
      });
    }

    render();
  }

  /* ---------- 8. SupportApp Widget (replicates SupportApp.jsx) ---------- */

  function renderSupportItems(supports) {
    var $box = $("#SupportApp");
    if (!$box.length || !Array.isArray(supports)) return;

    function hrefLink(s) {
      var clean = String(s.content || "").replace(/\./g, "").replace(/\s+/g, "");
      if (s.support_type === "phone") return "tel:" + clean;
      if (s.support_type === "zalo") return "https://zalo.me/" + clean;
      if (s.support_type === "messenger") return "https://m.me/" + s.content;
      if (s.support_type === "mail" || s.support_type === "email") return "mailto:" + s.content;
      return "#";
    }

    function iconSvg(type) {
      if (type === "phone") {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M493.4 24.6l-104-24c-11.3-2.6-22.9 3.3-27.5 13.9l-48 112c-4.2 9.8-1.4 21.3 6.9 28l60.6 49.6c-36 76.7-98.9 140.5-177.2 177.2l-49.6-60.6c-6.8-8.3-18.2-11.1-28-6.9l-112 48C3.9 366.5-2 378.1.6 389.4l24 104C27.1 504.2 36.7 512 48 512c256.1 0 464-207.5 464-464 0-11.2-7.7-20.9-18.6-23.4z"></path></svg>';
      }
      if (type === "zalo") {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 614.501 613.667"><path fill="currentColor" d="M464.721,301.399c-13.984-0.014-23.707,11.478-23.944,28.312c-0.251,17.771,9.168,29.208,24.037,29.202c14.287-0.007,23.799-11.095,24.01-27.995C489.028,313.536,479.127,301.399,464.721,301.399z"></path><path fill="currentColor" d="M291.83,301.392c-14.473-0.316-24.578,11.603-24.604,29.024c-0.02,16.959,9.294,28.259,23.496,28.502c15.072,0.251,24.592-10.87,24.539-28.707C315.214,313.318,305.769,301.696,291.83,301.392z"></path><path fill="currentColor" d="M310.518,3.158C143.102,3.158,7.375,138.884,7.375,306.3s135.727,303.142,303.143,303.142c167.415,0,303.143-135.727,303.143-303.142S477.933,3.158,310.518,3.158z"></path></svg>';
      }
      return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>';
    }

    var html = supports
      .map(function (s) {
        return (
          '<div class="item ' +
          s.support_type +
          '">' +
          '<a href="' +
          hrefLink(s) +
          '">' +
          '<span class="img">' +
          iconSvg(s.support_type) +
          "</span>" +
          '<div class="detail grid gap-1">' +
          '<span class="arcu-item-title">' +
          $("<div>").text(s.name || "").html() +
          "</span>" +
          '<b class="arcu-item-subtitle">' +
          $("<div>").text(s.content || "").html() +
          "</b>" +
          "</div>" +
          "</a>" +
          "</div>"
        );
      })
      .join("");

    $box.html(html);
  }

  function initSupportApp() {
    if (!$("#SupportApp").length) return;
    if (Array.isArray(CFG.supports) && CFG.supports.length > 0) {
      renderSupportItems(CFG.supports);
    } else {
      $.getJSON(CFG.restUrl + "/supports").done(function (res) {
        if (res && Array.isArray(res.supports)) {
          renderSupportItems(res.supports);
        }
      });
    }
  }

  /* ---------- 9. DOM Ready Initialization (from app.js) ---------- */

  $(document).ready(function () {
    updateCartBadge();
    initCartOrderForm();
    initSortSearch();
    initProductFilter();
    initSupportApp();

    $(window).on("scroll", function () {
      var scroll = $(window).scrollTop();
      if (scroll < 245) {
        $(".scroll-to-target").removeClass("open");
      } else {
        $(".scroll-to-target").addClass("open");
      }
    });

    if ($(".scroll-to-target").length) {
      $(".scroll-to-target").on("click", function () {
        var target = $(this).attr("data-target") || "html";
        $("html, body").animate(
          {
            scrollTop: $(target).offset() ? $(target).offset().top : 0,
          },
          600
        );
      });
    }

    $(".out-circle").on("click", function () {
      $(".def-content").toggleClass("unsee");
      $(".out-circle .close-icon").toggleClass("unsee");
    });

    $(".def-header .close-icon").on("click", function (e) {
      e.stopPropagation();
      $(".def-content").toggleClass("unsee");
      $(".out-circle .close-icon").toggleClass("unsee");
    });

    $(".show-menu").on("click", function () {
      $(".menu-mobile").removeClass("hidden");
    });

    $("#toggle-menu-ct-mobile").on("click", function () {
      $(".menu-ct-mobile").toggleClass("hidden");
    });

    $(".show-ct-child-mobile").on("click", function () {
      var $parent = $(this).parents(".li-parent");
      var $ul = $parent.find("ul");
      $ul.toggleClass("hidden");
    });

    if ($.fn.owlCarousel) {
      if ($("#sliders").length > 0) {
        $("#sliders").owlCarousel({
          items: 1,
          loop: false,
          responsiveClass: true,
          dots: true,
          nav: true,
          autoplay: true,
          autoplayTimeout: 5000,
          autoplayHoverPause: true,
        });
      }

      if ($("#service-sliders").length > 0) {
        $("#service-sliders").owlCarousel({
          items: 4,
          loop: false,
          responsiveClass: true,
          margin: 15,
          dots: false,
          nav: false,
          autoplay: true,
          autoplayTimeout: 5000,
          autoplayHoverPause: true,
          responsive: {
            0: { items: 1 },
            540: { items: 2 },
            680: { items: 3 },
            890: { items: 4 },
            1000: { items: 4 },
          },
        });
      }

      if ($(".SLIDER-PRODUCT-CT").length > 0) {
        $(".SLIDER-PRODUCT-CT").owlCarousel({
          items: 5,
          loop: false,
          responsiveClass: true,
          margin: 15,
          dots: false,
          nav: true,
          autoplay: false,
          autoplayTimeout: 5000,
          autoplayHoverPause: true,
          responsive: {
            0: { items: 1 },
            300: { items: 2 },
            680: { items: 3 },
            890: { items: 4 },
            1000: { items: 5 },
          },
        });
      }
    }

    $(".lightbox").each(function () {
      $(this).lightbox({
        target: $(this).data("id"),
      });
    });
  });
})(window, window.jQuery);
