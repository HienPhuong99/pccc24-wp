(function (window, $) {
  "use strict";
  if (!$) return;

  var PREFIX = "/pccc24-wp";
  var noImg = PREFIX + "/wp-content/themes/pccc24-theme/assets/images/no-image.png";

  // Fallback tự động nếu ảnh bất kỳ bị lỗi
  $(document).on("error", "img", function () {
    if (this.src && this.src !== noImg && !this.dataset.fallbackApplied) {
      this.dataset.fallbackApplied = "1";
      this.src = noImg;
    }
  });
  $("img").each(function () {
    if (this.complete && this.naturalWidth === 0 && this.src !== noImg) {
      this.src = noImg;
    }
  });

  // 1. Điều hướng request REST API sang file .json tĩnh hoặc xử lý Đặt hàng trên trình duyệt
  $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
    var u = options.url || "";
    var mProduct = u.match(/\/wp-json\/pccc24\/v1\/products\/(\d+)\/?$/);
    if (mProduct) {
      options.url = PREFIX + "/wp-json/pccc24/v1/products/" + mProduct[1] + ".json";
      return;
    }
    if (/\/wp-json\/pccc24\/v1\/supports\/?$/.test(u)) {
      options.url = PREFIX + "/wp-json/pccc24/v1/supports.json";
      return;
    }
    if (/\/wp-json\/pccc24\/v1\/(orders|carts)\/?$/.test(u) && (options.type || options.method || "").toUpperCase() === "POST") {
      jqXHR.abort();
      var payload = {};
      try {
        payload = typeof options.data === "string" ? JSON.parse(options.data) : options.data || {};
      } catch (e) {}
      var name = $.trim(payload.name || "");
      var phone = $.trim(payload.phone || "");
      if (!name || !/^[0-9 +().-]{8,20}$/.test(phone)) {
        if (options.error) {
          options.error({ responseJSON: { message: "Vui lòng nhập họ tên và số điện thoại hợp lệ" } });
        }
        return;
      }
      var orders = [];
      try {
        orders = JSON.parse(localStorage.getItem("pccc24_demo_orders") || "[]");
      } catch (e) {}
      var newOrder = {
        id: 2000 + orders.length + 1,
        name: name,
        phone: phone,
        email: payload.email || "",
        address: payload.address || "",
        note: payload.note || "",
        items: payload.items || [],
        created_at: new Date().toLocaleString("vi-VN"),
      };
      orders.unshift(newOrder);
      try {
        localStorage.setItem("pccc24_demo_orders", JSON.stringify(orders));
      } catch (e) {}
      setTimeout(function () {
        if (options.success) {
          options.success({
            id: newOrder.id,
            total: 0,
            items: newOrder.items,
            message: "Đặt hàng thành công! Đơn hàng #" + newOrder.id + " đã được lưu vào wp-admin Demo.",
          });
        }
      }, 150);
    }
  });

  // 2. Xử lý Tìm kiếm (?q=...), Sắp xếp (?sorted=1..5) và Phân trang (?page=N) động trên GitHub Pages
  $(document).ready(function () {
    var params = window.getUrlVars ? window.getUrlVars() : {};
    var path = window.location.pathname.replace(/\/+$/, "");
    var isSearchPage = /\/san-pham\/tim-kiem$/.test(path);
    var isProductsRoot = /\/san-pham$/.test(path);
    var catMatch = path.match(/\/san-pham\/([^\/]+)$/);
    var catSlug = catMatch && catMatch[1] !== "tim-kiem" ? catMatch[1] : null;

    var hasDynamicQuery = isSearchPage || params.sorted || (params.page && parseInt(params.page, 10) > 1);
    if (hasDynamicQuery && (isSearchPage || isProductsRoot || catSlug)) {
      $.getJSON(PREFIX + "/wp-json/pccc24/v1/products-all.json").done(function (all) {
        if (!Array.isArray(all)) return;
        var filtered = all.slice();

        if (isSearchPage) {
          var q = $.trim(params.q || "").toLowerCase();
          $(".right-content h1, .crumb li:last-child").last().text("Tìm kiếm: " + (params.q || ""));
          $('input[name="q"]').val(params.q || "");
          if (q) {
            filtered = filtered.filter(function (p) {
              return (p.name || "").toLowerCase().indexOf(q) !== -1;
            });
          }
        } else if (catSlug) {
          filtered = filtered.filter(function (p) {
            return Array.isArray(p.cats) && p.cats.indexOf(catSlug) !== -1;
          });
        }

        var sorted = parseInt(params.sorted || 5, 10);
        if (sorted === 1) {
          filtered.sort(function (a, b) {
            return a.name.localeCompare(b.name, "vi");
          });
        } else if (sorted === 2) {
          filtered.sort(function (a, b) {
            return b.name.localeCompare(a.name, "vi");
          });
        } else if (sorted === 3) {
          filtered.sort(function (a, b) {
            return (a.price || 0) - (b.price || 0);
          });
        } else if (sorted === 4) {
          filtered.sort(function (a, b) {
            return (b.price || 0) - (a.price || 0);
          });
        }

        var perPage = 20;
        var totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        var page = Math.min(totalPages, Math.max(1, parseInt(params.page || 1, 10)));
        var slice = filtered.slice((page - 1) * perPage, page * perPage);

        var $grid = $(".right-content .grid.gap-4").first();
        if ($grid.length) {
          if (slice.length === 0) {
            $grid.html('<p class="col-span-4 text-gray-500 py-6">Không tìm thấy sản phẩm phù hợp.</p>');
          } else {
            var cards = slice
              .map(function (p) {
                var priceTxt = p.price > 0 ? window.formatMoney(p.price, 0, ",", ".") + "đ" : "Liên hệ";
                return (
                  '<div class="bg-white shadow rounded p-3 relative previewProduct">' +
                  '<div class="flex flex-col justify-between h-full">' +
                  '<div>' +
                  '<a href="' + p.url + '" title="' + $("<div>").text(p.name).html() + '">' +
                  '<div class="flex items-center justify-center h-44">' +
                  '<img src="' + p.image_url + '" class="img-product-responsive max-h-44" alt="' + $("<div>").text(p.name).html() + '" />' +
                  "</div>" +
                  '<h3 class="mt-2 text-base limit-line limit-line-2 hover:text-red-600">' +
                  $("<div>").text(p.name).html() +
                  "</h3>" +
                  "</a>" +
                  "</div>" +
                  '<div class="mt-2 pt-2 border-t flex justify-between items-center">' +
                  '<span class="text-red-600 font-medium">' + priceTxt + "</span>" +
                  '<div class="flex gap-1">' +
                  '<button type="button" onclick="previewProduct(' + p.id + ')" class="md:flex xs-s:hidden items-center justify-center w-8 h-8 rounded-full border hover:bg-red-500 hover:text-white cursor-pointer" title="Xem nhanh"><i class="bx bx-show"></i></button>' +
                  '<button type="button" onclick="addCart(' + p.id + ', 1)" class="flex items-center justify-center w-8 h-8 rounded-full border hover:bg-red-500 hover:text-white cursor-pointer" title="Thêm vào giỏ"><i class="bx bx-cart"></i></button>' +
                  "</div>" +
                  "</div>" +
                  "</div>" +
                  "</div>"
                );
              })
              .join("");
            $grid.html(cards);
          }
        }

        var $pag = $(".paginate-container");
        if ($pag.length) {
          if (totalPages <= 1) {
            $pag.empty();
          } else {
            var ul = '<ul class="pagination">';
            for (var i = 1; i <= totalPages; i++) {
              var href = window.replaceUrlParam("page", i);
              ul += '<li class="' + (i === page ? "active" : "") + '"><a href="' + href + '">' + i + "</a></li>";
            }
            ul += "</ul>";
            $pag.html(ul);
          }
        }
      });
    }

    // 3. Nút nổi chuyển nhanh sang trang Quản trị WordPress (wp-admin Demo) ở góc trái dưới
    var badge =
      '<a href="' +
      PREFIX +
      '/wp-admin/" style="position:fixed;left:16px;bottom:16px;z-index:1500;background:#1d2327;color:#fff;padding:8px 14px;border-radius:999px;font-size:13px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:center;gap:6px;text-decoration:none;">' +
      '<span style="width:8px;height:8px;border-radius:50%;background:#00a32a;display:inline-block;"></span>' +
      '<span>WP-Admin Demo</span>' +
      "</a>";
    $("body").append(badge);
  });
})(window, window.jQuery);