(function () {
  "use strict";

  var PREFIX = "/pccc24-wp";
  var noImg = PREFIX + "/wp-content/themes/pccc24-theme/assets/images/no-image.png";
  var esc = function (s) {
    var d = document.createElement("div");
    d.textContent = s == null ? "" : String(s);
    return d.innerHTML;
  };

  // 1. Chặn window.fetch để chuyển /wp-json/pccc24/v1/products/{id} sang file .json tĩnh trên GitHub Pages
  var origFetch = window.fetch ? window.fetch.bind(window) : null;
  if (origFetch) {
    window.fetch = function (input, init) {
      if (typeof input === "string") {
        var mProd = input.match(/\/wp-json\/pccc24\/v1\/products\/(\d+)\/?$/);
        if (mProd) {
          input = PREFIX + "/wp-json/pccc24/v1/products/" + mProd[1] + ".json";
        } else if (/\/wp-json\/pccc24\/v1\/supports\/?$/.test(input)) {
          input = PREFIX + "/wp-json/pccc24/v1/supports.json";
        }
      }
      return origFetch(input, init);
    };
  }

  // 2. Fallback tự động cho ảnh bị lỗi
  document.addEventListener(
    "error",
    function (e) {
      var t = e.target;
      if (t && t.tagName === "IMG" && t.src !== noImg && !t.dataset.fallback) {
        t.dataset.fallback = "1";
        t.src = noImg;
      }
    },
    true
  );

  // 3. Xử lý Tìm kiếm (?q=...), Sắp xếp (?sorted=1..4) và Phân trang (?page=N) bằng Vanilla JS
  document.addEventListener("DOMContentLoaded", function () {
    var sp = new URLSearchParams(window.location.search);
    var path = window.location.pathname.replace(/\/+$/, "");
    var isSearchPage = /\/san-pham\/tim-kiem$/.test(path);
    var isProductsRoot = /\/san-pham$/.test(path);
    var catMatch = path.match(/\/san-pham\/([^\/]+)$/);
    var catSlug = catMatch && catMatch[1] !== "tim-kiem" ? catMatch[1] : null;

    var qParam = sp.get("q") || "";
    var sortedParam = parseInt(sp.get("sorted") || "0", 10);
    var pageParam = parseInt(sp.get("page") || "1", 10);

    var sortSelect = document.getElementById("sorted");
    if (sortSelect && sortedParam > 0) {
      sortSelect.value = String(sortedParam);
    }

    if ((isSearchPage || sortedParam > 0 || pageParam > 1) && (isSearchPage || isProductsRoot || catSlug) && origFetch) {
      origFetch(PREFIX + "/wp-json/pccc24/v1/products-all.json")
        .then(function (r) {
          return r.json();
        })
        .then(function (all) {
          if (!Array.isArray(all)) return;
          var list = all.slice();

          if (isSearchPage) {
            var qClean = qParam.trim().toLowerCase();
            document.querySelectorAll('input[name="q"]').forEach(function (inp) {
              inp.value = qParam;
            });
            if (qClean) {
              list = list.filter(function (p) {
                return (p.name || "").toLowerCase().indexOf(qClean) !== -1;
              });
            }
            var h1 = document.querySelector(".page-head h1");
            if (h1) {
              h1.textContent = qParam ? 'Kết quả cho "' + qParam + '" (' + list.length + ")" : "Tìm kiếm sản phẩm";
            }
          } else if (catSlug) {
            list = list.filter(function (p) {
              return Array.isArray(p.cats) && p.cats.indexOf(catSlug) !== -1;
            });
          }

          if (sortedParam === 1) {
            list.sort(function (a, b) {
              return a.name.localeCompare(b.name, "vi");
            });
          } else if (sortedParam === 2) {
            list.sort(function (a, b) {
              return b.name.localeCompare(a.name, "vi");
            });
          } else if (sortedParam === 3) {
            list.sort(function (a, b) {
              return (a.price || 0) - (b.price || 0);
            });
          } else if (sortedParam === 4) {
            list.sort(function (a, b) {
              return (b.price || 0) - (a.price || 0);
            });
          }

          var perPage = 20;
          var totalPages = Math.max(1, Math.ceil(list.length / perPage));
          var page = Math.min(totalPages, Math.max(1, pageParam));
          var slice = list.slice((page - 1) * perPage, page * perPage);

          var grid = document.querySelector(".grid-products");
          var pageHead = document.querySelector(".page-head");
          if (!grid && pageHead) {
            var oldPanel = pageHead.parentElement.querySelector(".panel");
            if (oldPanel) oldPanel.remove();
            grid = document.createElement("div");
            grid.className = "grid-products";
            pageHead.insertAdjacentElement("afterend", grid);
          }

          if (grid) {
            if (slice.length === 0) {
              grid.outerHTML = '<div class="panel">Không tìm thấy sản phẩm phù hợp.</div>';
            } else {
              grid.innerHTML = slice
                .map(function (p) {
                  var priceStr = p.price > 0 ? Number(p.price).toLocaleString("vi-VN") + "đ" : "Liên hệ";
                  return (
                    '<article class="product">' +
                    '<div class="product-img"><img src="' +
                    esc(p.image_url || noImg) +
                    '" alt="" loading="lazy"></div>' +
                    '<a href="' +
                    esc(p.url) +
                    '"><h3>' +
                    esc(p.name) +
                    "</h3></a>" +
                    '<div class="product-foot">' +
                    '<span class="product-price">' +
                    esc(priceStr) +
                    "</span>" +
                    '<button type="button" class="quick" data-quick="' +
                    Number(p.id) +
                    '">Xem nhanh</button>' +
                    "</div>" +
                    "</article>"
                  );
                })
                .join("");
            }
          }

          var pag = document.querySelector("ul.pagination");
          if (totalPages <= 1) {
            if (pag) pag.remove();
          } else {
            if (!pag && grid && grid.parentElement) {
              pag = document.createElement("ul");
              pag.className = "pagination";
              grid.insertAdjacentElement("afterend", pag);
            }
            if (pag) {
              var itemsHtml = "";
              for (var i = 1; i <= totalPages; i++) {
                var nextSp = new URLSearchParams(window.location.search);
                nextSp.set("page", String(i));
                itemsHtml +=
                  '<li class="' +
                  (i === page ? "active" : "") +
                  '"><a href="' +
                  window.location.pathname +
                  "?" +
                  nextSp.toString() +
                  '">' +
                  i +
                  "</a></li>";
              }
              pag.innerHTML = itemsHtml;
            }
          }
        });
    }

    // 4. Nút nổi góc trái dưới mở nhanh trang Đăng nhập / WP-Admin Demo (đã điền sẵn tài khoản, email, mật khẩu)
    var badge = document.createElement("a");
    badge.href = PREFIX + "/wp-login/";
    badge.style.cssText =
      "position:fixed;left:16px;bottom:16px;z-index:1500;background:#1d2329;color:#fff;padding:8px 14px;border-radius:999px;font-size:13px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:center;gap:6px;text-decoration:none;border:1px solid rgba(255,255,255,0.15);";
    badge.innerHTML =
      '<span style="width:8px;height:8px;border-radius:50%;background:#0b7a4b;display:inline-block;"></span>' +
      "<span>🔑 WP-Admin / Đăng nhập (Đã điền sẵn)</span>";
    document.body.appendChild(badge);
  });
})();