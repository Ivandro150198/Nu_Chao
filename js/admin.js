(function () {
  "use strict";

  if (!requireAuth("login.html?return=admin.html")) return;

  var defaultHeroes = [
    { id: 1, image: "assets/hero-banner.png", title: "Roupas de Fuca · Loja No Chão", subtitle: "By Aissatu Jafono. Moda inspirada na Guiné-Bissau.", badges: ["Pagamento na entrega", "Entregas 48h", "Suporte WhatsApp"], gradient: false },
    { id: 2, image: "", title: "Moda inspirada na Guiné-Bissau", subtitle: "Descubra coleções exclusivas.", badges: [], gradient: true }
  ];

  function getProducts() { return window.LojaDB ? window.LojaDB.getProducts() : []; }
  function saveProducts(arr) { if (window.LojaDB) window.LojaDB.saveProducts(arr); }

  function getHeroes() {
    var h = window.LojaDB ? window.LojaDB.getHeroes() : [];
    return Array.isArray(h) && h.length ? h : defaultHeroes.slice();
  }
  function saveHeroes(arr) { if (window.LojaDB) window.LojaDB.saveHeroes(arr); }

  function getOrders() { return window.LojaDB ? window.LojaDB.getOrders() : []; }
  function saveOrders(arr) { if (window.LojaDB) window.LojaDB.saveOrders(arr); }

  function getCustomers() { return window.LojaDB ? window.LojaDB.getCustomers() : []; }
  function saveCustomers(arr) { if (window.LojaDB) window.LojaDB.saveCustomers(arr); }
  function addCustomer(obj) { return window.LojaDB && window.LojaDB.addCustomer ? window.LojaDB.addCustomer(obj) : null; }

  function getCoupons() { return window.LojaDB ? window.LojaDB.getCoupons() : []; }
  function saveCoupons(arr) { if (window.LojaDB) window.LojaDB.saveCoupons(arr); }

  function getNewsletter() { return window.LojaDB ? window.LojaDB.getNewsletter() : []; }
  function saveNewsletter(arr) { if (window.LojaDB) window.LojaDB.saveNewsletter(arr); }

  function getSettings() { return window.LojaDB ? window.LojaDB.getSettings() : {}; }
  function saveSettings(obj) { if (window.LojaDB) window.LojaDB.saveSettings(obj); }

  function getUsers() { return window.LojaDB ? window.LojaDB.getUsers() : []; }
  function saveUsers(arr) { if (window.LojaDB) window.LojaDB.saveUsers(arr); }

  function getAudit() { return window.LojaDB ? window.LojaDB.getAudit() : []; }
  function appendAudit(entry) { if (window.LojaDB) window.LojaDB.appendAudit(entry); }

  function nextId(arr) {
    var max = 0;
    (arr || []).forEach(function (item) { if (item.id && item.id > max) max = item.id; });
    return max + 1;
  }

  function currentUser() {
    var s = getSession();
    return (s && s.user && s.user.login) ? s.user.login : "Admin";
  }

  // ——— Tabs ———
  document.querySelectorAll(".admin-tab").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var tab = btn.getAttribute("data-tab");
      document.querySelectorAll(".admin-tab").forEach(function (b) {
        b.classList.remove("active");
        b.setAttribute("aria-selected", "false");
      });
      document.querySelectorAll(".admin-panel").forEach(function (p) { p.classList.remove("active"); });
      btn.classList.add("active");
      btn.setAttribute("aria-selected", "true");
      var panel = document.getElementById("panel-" + tab);
      if (panel) panel.classList.add("active");
      if (tab === "pedidos") renderOrders();
      if (tab === "estoque") renderStock();
      if (tab === "clientes") renderCustomers();
      if (tab === "marketing") { renderCoupons(); renderNewsletter(); }
      if (tab === "relatorios") renderDashboard();
      if (tab === "config") { loadConfigIntoForm(); renderUsers(); renderAuditLog(); }
    });
  });

  // ——— Logout ———
  var logoutEl = document.getElementById("adminLogout");
  if (logoutEl) logoutEl.addEventListener("click", function (e) { e.preventDefault(); logout(); window.location.href = "login.html"; });

  // ——— CATÁLOGO: Produtos ———
  var productsBody = document.getElementById("productsBody");
  var productId = document.getElementById("productId");
  var productName = document.getElementById("productName");
  var productCategory = document.getElementById("productCategory");
  var productCollection = document.getElementById("productCollection");
  var productType = document.getElementById("productType");
  var productDescription = document.getElementById("productDescription");
  var productComposition = document.getElementById("productComposition");
  var productSizeGuide = document.getElementById("productSizeGuide");
  var productPrice = document.getElementById("productPrice");
  var productLocation = document.getElementById("productLocation");
  var productSizes = document.getElementById("productSizes");
  var productTag = document.getElementById("productTag");
  var productSkus = document.getElementById("productSkus");
  var productImage = document.getElementById("productImage");
  var productGallery = document.getElementById("productGallery");
  var productInStock = document.getElementById("productInStock");
  var productSave = document.getElementById("productSave");
  var productCancel = document.getElementById("productCancel");

  function clearProductForm() {
    productId.value = "";
    productName.value = "";
    productCategory.value = "feminino";
    productCollection.value = "";
    productType.value = "casual";
    productDescription.value = "";
    productComposition.value = "";
    productSizeGuide.value = "";
    productPrice.value = "";
    productLocation.value = "Bissau";
    productSizes.value = "S, M, L";
    productTag.value = "";
    productSkus.value = "";
    productImage.value = "";
    productGallery.value = "";
    productInStock.checked = true;
  }

  function renderProductsTable() {
    var list = getProducts();
    if (!productsBody) return;
    if (!list.length) {
      productsBody.innerHTML = "<tr><td colspan=\"6\" class=\"empty-msg\">Nenhum produto. Use o formulário abaixo.</td></tr>";
      return;
    }
    productsBody.innerHTML = list.map(function (p) {
      return "<tr><td>" + (p.name || "") + "</td><td>" + (p.category || "") + "</td><td>" + (p.collection || "-") + "</td><td>" + (p.price != null ? Number(p.price).toLocaleString("pt-PT") : "") + "</td><td>" + (p.inStock !== false ? "Sim" : "Não") + "</td><td>" +
        "<button type=\"button\" class=\"btn-sm btn-edit\" data-id=\"" + p.id + "\">Editar</button> " +
        "<button type=\"button\" class=\"btn-sm btn-del\" data-id=\"" + p.id + "\">Apagar</button></td></tr>";
    }).join("");

    productsBody.querySelectorAll(".btn-edit").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = Number(btn.getAttribute("data-id"));
        var p = getProducts().find(function (x) { return x.id === id; });
        if (!p) return;
        productId.value = p.id;
        productName.value = p.name || "";
        productCategory.value = p.category || "feminino";
        productCollection.value = p.collection || "";
        productType.value = p.productType || "casual";
        productDescription.value = p.description || "";
        productComposition.value = p.composition || "";
        productSizeGuide.value = p.sizeGuide || "";
        productPrice.value = p.price != null ? p.price : "";
        productLocation.value = p.location || "Bissau";
        productSizes.value = Array.isArray(p.sizes) ? p.sizes.join(", ") : "";
        productTag.value = p.tag || "";
        productSkus.value = Array.isArray(p.skus) ? p.skus.join("\n") : "";
        productImage.value = p.image || "";
        productGallery.value = Array.isArray(p.gallery) ? p.gallery.join("\n") : "";
        productInStock.checked = p.inStock !== false;
      });
    });

    productsBody.querySelectorAll(".btn-del").forEach(function (btn) {
      btn.addEventListener("click", function () {
        if (!confirm("Apagar este produto?")) return;
        var id = Number(btn.getAttribute("data-id"));
        saveProducts(getProducts().filter(function (x) { return x.id !== id; }));
        appendAudit({ user: currentUser(), action: "Produto apagado", detail: "ID " + id });
        renderProductsTable();
        clearProductForm();
      });
    });
  }

  if (productSave) productSave.addEventListener("click", function () {
    var name = productName.value.trim();
    var price = Number(productPrice.value);
    if (!name || isNaN(price) || price < 0) { alert("Preencha título e preço."); return; }
    var list = getProducts();
    var id = productId.value ? Number(productId.value) : nextId(list);
    var sizesStr = productSizes.value.trim();
    var sizes = sizesStr ? sizesStr.split(",").map(function (s) { return s.trim(); }).filter(Boolean) : ["Único"];
    var skuStr = productSkus.value.trim();
    var skus = skuStr ? skuStr.split("\n").map(function (s) { return s.trim(); }).filter(Boolean) : [];
    var galleryStr = productGallery.value.trim();
    var gallery = galleryStr ? galleryStr.split("\n").map(function (s) { return s.trim(); }).filter(Boolean) : [];
    var item = {
      id: id, name: name, category: productCategory.value || "feminino", collection: productCollection.value.trim() || null, productType: productType.value || "casual",
      description: productDescription.value.trim() || "", composition: productComposition.value.trim() || null, sizeGuide: productSizeGuide.value.trim() || null,
      price: price, location: productLocation.value.trim() || "Bissau", sizes: sizes, tag: productTag.value || null, skus: skus,
      image: productImage.value.trim() || null, gallery: gallery, inStock: productInStock.checked, stockQty: undefined
    };
    var idx = list.findIndex(function (x) { return x.id === id; });
    if (idx >= 0) list[idx] = item; else list.push(item);
    saveProducts(list);
    appendAudit({ user: currentUser(), action: "Produto guardado", detail: name });
    renderProductsTable();
    clearProductForm();
  });
  if (productCancel) productCancel.addEventListener("click", clearProductForm);

  // ——— Heróis ———
  var heroesList = document.getElementById("heroesList");
  var heroId = document.getElementById("heroId");
  var heroImage = document.getElementById("heroImage");
  var heroTitle = document.getElementById("heroTitle");
  var heroSubtitle = document.getElementById("heroSubtitle");
  var heroBadges = document.getElementById("heroBadges");
  var heroGradient = document.getElementById("heroGradient");
  var heroSave = document.getElementById("heroSave");
  var heroCancel = document.getElementById("heroCancel");

  function clearHeroForm() {
    heroId.value = ""; heroImage.value = ""; heroTitle.value = ""; heroSubtitle.value = ""; heroBadges.value = ""; heroGradient.checked = false;
  }

  function renderHeroesList() {
    var list = getHeroes();
    if (!heroesList) return;
    if (!list.length) { heroesList.innerHTML = "<p class=\"empty-msg\">Nenhum slide.</p>"; return; }
    heroesList.innerHTML = list.map(function (h) {
      return "<div class=\"hero-item\"><span>" + (h.title || "Slide #" + h.id) + "</span><div>" +
        "<button type=\"button\" class=\"btn-sm btn-edit hero-edit\" data-id=\"" + h.id + "\">Editar</button> " +
        "<button type=\"button\" class=\"btn-sm btn-del hero-del\" data-id=\"" + h.id + "\">Apagar</button></div></div>";
    }).join("");

    heroesList.querySelectorAll(".hero-edit").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var h = getHeroes().find(function (x) { return x.id === Number(btn.getAttribute("data-id")); });
        if (!h) return;
        heroId.value = h.id; heroImage.value = h.image || ""; heroTitle.value = h.title || ""; heroSubtitle.value = h.subtitle || "";
        heroBadges.value = Array.isArray(h.badges) ? h.badges.join("\n") : ""; heroGradient.checked = !!h.gradient;
      });
    });
    heroesList.querySelectorAll(".hero-del").forEach(function (btn) {
      btn.addEventListener("click", function () {
        if (!confirm("Apagar slide?")) return;
        var id = Number(btn.getAttribute("data-id"));
        saveHeroes(getHeroes().filter(function (x) { return x.id !== id; }));
        renderHeroesList();
        clearHeroForm();
      });
    });
  }

  if (heroSave) heroSave.addEventListener("click", function () {
    var list = getHeroes();
    var id = heroId.value ? Number(heroId.value) : nextId(list);
    var badges = heroBadges.value.trim() ? heroBadges.value.split("\n").map(function (s) { return s.trim(); }).filter(Boolean) : [];
    var item = { id: id, image: heroImage.value.trim() || "", title: heroTitle.value.trim() || "Slide", subtitle: heroSubtitle.value.trim() || "", badges: badges, gradient: heroGradient.checked };
    var idx = list.findIndex(function (x) { return x.id === id; });
    if (idx >= 0) list[idx] = item; else list.push(item);
    saveHeroes(list);
    renderHeroesList();
    clearHeroForm();
  });
  if (heroCancel) heroCancel.addEventListener("click", clearHeroForm);

  // ——— PEDIDOS ———
  var ordersBody = document.getElementById("ordersBody");
  var ordersEmpty = document.getElementById("ordersEmpty");
  var orderEditForm = document.getElementById("orderEditForm");
  var orderId = document.getElementById("orderId");
  var orderEditId = document.getElementById("orderEditId");
  var orderStatus = document.getElementById("orderStatus");
  var orderTracking = document.getElementById("orderTracking");
  var orderSave = document.getElementById("orderSave");
  var orderCancel = document.getElementById("orderCancel");

  function renderOrders() {
    var list = getOrders();
    if (!ordersBody) return;
    if (ordersEmpty) ordersEmpty.style.display = list.length ? "none" : "block";
    ordersBody.innerHTML = list.map(function (o) {
      var name = (o.customer && o.customer.name) ? o.customer.name : "—";
      return "<tr><td>" + o.id + "</td><td>" + name + "</td><td>" + (o.total != null ? Number(o.total).toLocaleString("pt-PT") + " CFA" : "") + "</td><td>" + (o.status || "Aguardando Pagamento") + "</td><td>" + (o.createdAt ? new Date(o.createdAt).toLocaleDateString("pt-PT") : "") + "</td><td>" +
        "<button type=\"button\" class=\"btn-sm btn-edit order-edit\" data-id=\"" + o.id + "\">Editar</button></td></tr>";
    }).join("");

    ordersBody.querySelectorAll(".order-edit").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = Number(btn.getAttribute("data-id"));
        var o = getOrders().find(function (x) { return x.id === id; });
        if (!o) return;
        orderId.value = o.id;
        if (orderEditId) orderEditId.textContent = "#" + o.id;
        orderStatus.value = o.status || "Aguardando Pagamento";
        orderTracking.value = o.trackingCode || "";
        if (orderEditForm) orderEditForm.classList.add("visible");
      });
    });
  }

  if (orderSave) orderSave.addEventListener("click", function () {
    var id = Number(orderId.value);
    var list = getOrders();
    var o = list.find(function (x) { return x.id === id; });
    if (!o) return;
    var oldStatus = o.status;
    o.status = orderStatus.value;
    o.trackingCode = orderTracking.value.trim();
    o.updatedAt = new Date().toISOString();
    saveOrders(list);
    appendAudit({ user: currentUser(), action: "Pedido alterado", detail: "#" + id + " status: " + oldStatus + " → " + o.status });
    renderOrders();
    if (orderEditForm) orderEditForm.classList.remove("visible");
  });
  if (orderCancel) orderCancel.addEventListener("click", function () { if (orderEditForm) orderEditForm.classList.remove("visible"); });

  // ——— ESTOQUE ———
  var lowStockThreshold = document.getElementById("lowStockThreshold");
  var saveStockSettings = document.getElementById("saveStockSettings");
  var stockBody = document.getElementById("stockBody");

  function renderStock() {
    var prods = getProducts();
    var settings = getSettings();
    var threshold = (settings.lowStockThreshold != null) ? settings.lowStockThreshold : 5;
    if (lowStockThreshold) lowStockThreshold.value = threshold;
    if (!stockBody) return;
    stockBody.innerHTML = prods.map(function (p) {
      var qty = p.stockQty != null ? p.stockQty : (p.inStock !== false ? 99 : 0);
      var alert = qty < threshold ? "⚠ Baixo" : "OK";
      return "<tr><td>" + (p.name || "") + "</td><td>" + qty + "</td><td>" + alert + "</td><td><button type=\"button\" class=\"btn-sm btn-edit\" data-id=\"" + p.id + "\" data-qty=\"" + qty + "\">Alterar qtd</button></td></tr>";
    }).join("");

    stockBody.querySelectorAll("button[data-id]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = Number(btn.getAttribute("data-id"));
        var qty = prompt("Nova quantidade em stock:", btn.getAttribute("data-qty"));
        if (qty === null) return;
        qty = parseInt(qty, 10);
        if (isNaN(qty) || qty < 0) return;
        var list = getProducts();
        var p = list.find(function (x) { return x.id === id; });
        if (!p) return;
        p.stockQty = qty;
        p.inStock = qty > 0;
        saveProducts(list);
        appendAudit({ user: currentUser(), action: "Estoque atualizado", detail: p.name + " → " + qty });
        renderStock();
      });
    });
  }

  if (saveStockSettings) saveStockSettings.addEventListener("click", function () {
    var settings = getSettings();
    settings.lowStockThreshold = parseInt(lowStockThreshold.value, 10) || 5;
    saveSettings(settings);
    renderStock();
  });

  // ——— CLIENTES ———
  var customersBody = document.getElementById("customersBody");
  var customerName = document.getElementById("customerName");
  var customerPhone = document.getElementById("customerPhone");
  var customerEmail = document.getElementById("customerEmail");
  var customerAddress = document.getElementById("customerAddress");
  var customerSave = document.getElementById("customerSave");

  function renderCustomers() {
    var fromDb = getCustomers();
    var orders = getOrders();
    var fromOrders = {};
    orders.forEach(function (o) {
      var c = o.customer;
      if (!c || !(c.phone || c.email)) return;
      var key = (c.phone || c.email || "").trim();
      if (!key) return;
      if (!fromOrders[key]) fromOrders[key] = { name: c.name || "", phone: c.phone || "", email: c.email || "", address: c.address || "", source: "Pedido", id: null };
    });
    var rows = [];
    fromDb.forEach(function (c) {
      rows.push({ name: c.name || "", phone: c.phone || "", email: c.email || "", address: c.address || "", source: "Cadastro", id: c.id });
    });
    Object.keys(fromOrders).forEach(function (k) {
      var o = fromOrders[k];
      if (!rows.some(function (r) { return (r.phone && r.phone === o.phone) || (r.email && r.email === o.email); })) {
        rows.push({ name: o.name, phone: o.phone, email: o.email, address: o.address, source: o.source, id: null });
      }
    });
    if (!customersBody) return;
    customersBody.innerHTML = rows.length ? rows.map(function (c) {
      var del = c.id ? "<button type=\"button\" class=\"btn-sm btn-del customer-del\" data-id=\"" + c.id + "\">Apagar</button>" : "—";
      return "<tr><td>" + (c.name || "—") + "</td><td>" + (c.phone || "—") + "</td><td>" + (c.email || "—") + "</td><td>" + (c.source || "—") + "</td><td>" + del + "</td></tr>";
    }).join("") : "<tr><td colspan=\"5\" class=\"empty-msg\">Nenhum cliente. Cadastre abaixo ou faça pedidos na loja.</td></tr>";

    customersBody.querySelectorAll(".customer-del").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = Number(btn.getAttribute("data-id"));
        if (!confirm("Remover este cliente?")) return;
        var list = getCustomers().filter(function (x) { return x.id !== id; });
        saveCustomers(list);
        appendAudit({ user: currentUser(), action: "Cliente removido", detail: "ID " + id });
        renderCustomers();
      });
    });
  }

  if (customerSave) customerSave.addEventListener("click", function () {
    var name = (customerName && customerName.value.trim()) || "";
    var phone = (customerPhone && customerPhone.value.trim()) || "";
    if (!name || !phone) { alert("Preencha nome e telefone."); return; }
    addCustomer({ name: name, phone: phone, email: (customerEmail && customerEmail.value.trim()) || "", address: (customerAddress && customerAddress.value.trim()) || "" });
    appendAudit({ user: currentUser(), action: "Cliente cadastrado", detail: name });
    renderCustomers();
    if (customerName) customerName.value = "";
    if (customerPhone) customerPhone.value = "";
    if (customerEmail) customerEmail.value = "";
    if (customerAddress) customerAddress.value = "";
  });

  // ——— MARKETING: Cupons ———
  var couponsBody = document.getElementById("couponsBody");
  var couponCode = document.getElementById("couponCode");
  var couponType = document.getElementById("couponType");
  var couponValue = document.getElementById("couponValue");
  var couponValidTo = document.getElementById("couponValidTo");
  var couponMinOrder = document.getElementById("couponMinOrder");
  var couponSave = document.getElementById("couponSave");

  function renderCoupons() {
    var list = getCoupons();
    if (!couponsBody) return;
    couponsBody.innerHTML = list.map(function (c) {
      var val = c.type === "percent" ? c.value + "%" : (c.type === "shipping" ? "Frete grátis" : (c.value + " CFA"));
      var valid = c.validTo ? new Date(c.validTo).toLocaleDateString("pt-PT") : "—";
      return "<tr><td>" + (c.code || "") + "</td><td>" + (c.type || "") + "</td><td>" + val + "</td><td>" + valid + "</td><td><button type=\"button\" class=\"btn-sm btn-del coupon-del\" data-code=\"" + (c.code || "") + "\">Apagar</button></td></tr>";
    }).join("");

    couponsBody.querySelectorAll(".coupon-del").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var code = btn.getAttribute("data-code");
        saveCoupons(getCoupons().filter(function (x) { return x.code !== code; }));
        renderCoupons();
      });
    });
  }

  if (couponSave) couponSave.addEventListener("click", function () {
    var code = (couponCode && couponCode.value.trim()) || "";
    if (!code) { alert("Indique o código."); return; }
    var list = getCoupons();
    if (list.some(function (x) { return x.code === code; })) { alert("Código já existe."); return; }
    var type = couponType ? couponType.value : "fixed";
    var val = couponValue ? couponValue.value.trim() : "0";
    list.push({
      code: code,
      type: type,
      value: type === "percent" ? parseFloat(val) || 0 : parseInt(val, 10) || 0,
      validTo: couponValidTo ? couponValidTo.value : null,
      minOrder: couponMinOrder ? parseInt(couponMinOrder.value, 10) || 0 : 0,
      usedCount: 0
    });
    saveCoupons(list);
    if (couponCode) couponCode.value = "";
    renderCoupons();
  });

  // ——— Newsletter ———
  var newsletterEmail = document.getElementById("newsletterEmail");
  var newsletterAdd = document.getElementById("newsletterAdd");
  var newsletterList = document.getElementById("newsletterList");

  function renderNewsletter() {
    var list = getNewsletter();
    if (!newsletterList) return;
    newsletterList.innerHTML = list.map(function (email) {
      return "<li>" + email + " <button type=\"button\" class=\"btn-sm btn-del\" data-email=\"" + email + "\">Remover</button></li>";
    }).join("");
    newsletterList.querySelectorAll("button").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var email = btn.getAttribute("data-email");
        saveNewsletter(getNewsletter().filter(function (e) { return e !== email; }));
        renderNewsletter();
      });
    });
  }

  if (newsletterAdd) newsletterAdd.addEventListener("click", function () {
    var email = newsletterEmail ? newsletterEmail.value.trim() : "";
    if (!email) { alert("Indique o email."); return; }
    var list = getNewsletter();
    if (list.indexOf(email) >= 0) { alert("Email já está na lista."); return; }
    list.push(email);
    saveNewsletter(list);
    if (newsletterEmail) newsletterEmail.value = "";
    renderNewsletter();
  });

  // ——— RELATÓRIOS ———
  function renderDashboard() {
    var orders = getOrders();
    var totalRevenue = orders.reduce(function (sum, o) { return sum + (Number(o.total) || 0); }, 0);
    var settings = getSettings();
    var threshold = (settings.lowStockThreshold != null) ? settings.lowStockThreshold : 5;
    var prods = getProducts();
    var lowStock = prods.filter(function (p) { var q = p.stockQty != null ? p.stockQty : (p.inStock ? 99 : 0); return q < threshold; });

    var sold = {};
    orders.forEach(function (o) {
      (o.items || []).forEach(function (it) {
        var key = it.name || it.productId;
        sold[key] = (sold[key] || 0) + (it.quantity || 0);
      });
    });
    var bestsellers = Object.keys(sold).map(function (k) { return { name: k, qty: sold[k] }; }).sort(function (a, b) { return b.qty - a.qty; }).slice(0, 10);

    var cardsEl = document.getElementById("dashboardCards");
    if (cardsEl) {
      cardsEl.innerHTML =
        "<div class=\"dashboard-card\"><span class=\"label\">Total pedidos</span><span class=\"value\">" + orders.length + "</span></div>" +
        "<div class=\"dashboard-card\"><span class=\"label\">Faturamento</span><span class=\"value\">" + totalRevenue.toLocaleString("pt-PT") + " CFA</span></div>" +
        "<div class=\"dashboard-card\"><span class=\"label\">Alertas estoque</span><span class=\"value\">" + lowStock.length + "</span></div>";
    }

    var bestBody = document.getElementById("bestsellersBody");
    if (bestBody) {
      bestBody.innerHTML = bestsellers.length ? bestsellers.map(function (b) { return "<tr><td>" + b.name + "</td><td>" + b.qty + "</td></tr>"; }).join("") : "<tr><td colspan=\"2\" class=\"empty-msg\">Nenhuma venda registada.</td></tr>";
    }

    var lowList = document.getElementById("lowStockList");
    if (lowList) {
      lowList.innerHTML = lowStock.length ? lowStock.map(function (p) { return "<li>" + (p.name || "") + " (estoque baixo)</li>"; }).join("") : "<li class=\"empty-msg\">Nenhum.</li>";
    }
  }

  // ——— CONFIG: SEO ———
  var seoTitle = document.getElementById("seoTitle");
  var seoDescription = document.getElementById("seoDescription");
  var seoSave = document.getElementById("seoSave");

  if (seoSave) seoSave.addEventListener("click", function () {
    var s = getSettings();
    s.seoTitle = seoTitle ? seoTitle.value.trim() : "";
    s.seoDescription = seoDescription ? seoDescription.value.trim() : "";
    saveSettings(s);
    appendAudit({ user: currentUser(), action: "SEO atualizado", detail: s.seoTitle });
    alert("Guardado.");
  });

  // ——— CONFIG: Utilizadores ———
  var usersBody = document.getElementById("usersBody");
  var userLogin = document.getElementById("userLogin");
  var userPassword = document.getElementById("userPassword");
  var userRole = document.getElementById("userRole");
  var userSave = document.getElementById("userSave");

  function renderUsers() {
    var list = getUsers();
    if (!usersBody) return;
    usersBody.innerHTML = list.map(function (u) {
      return "<tr><td>" + (u.login || "") + "</td><td>" + (u.role || "admin") + "</td><td><button type=\"button\" class=\"btn-sm btn-del user-del\" data-login=\"" + (u.login || "") + "\">Apagar</button></td></tr>";
    }).join("");
    usersBody.querySelectorAll(".user-del").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var login = btn.getAttribute("data-login");
        saveUsers(getUsers().filter(function (x) { return x.login !== login; }));
        renderUsers();
      });
    });
  }

  if (userSave) userSave.addEventListener("click", function () {
    var login = (userLogin && userLogin.value.trim()) || "";
    var pass = (userPassword && userPassword.value.trim()) || "";
    if (!login) { alert("Indique o login."); return; }
    var list = getUsers();
    if (list.some(function (x) { return x.login === login; })) { alert("Login já existe."); return; }
    list.push({ login: login, password: pass, role: (userRole && userRole.value) || "atendente" });
    saveUsers(list);
    if (userLogin) userLogin.value = "";
    if (userPassword) userPassword.value = "";
    renderUsers();
  });

  // ——— CONFIG: Audit log ———
  function renderAuditLog() {
    var list = document.getElementById("auditLogList");
    if (!list) return;
    var logs = getAudit().slice(0, 50);
    list.innerHTML = logs.length ? logs.map(function (l) {
      var d = l.date ? new Date(l.date).toLocaleString("pt-PT") : "";
      return "<li><span>" + d + "</span> <strong>" + (l.user || "") + "</strong> — " + (l.action || "") + " " + (l.detail || "") + "</li>";
    }).join("") : "<li class=\"empty-msg\">Nenhum registo.</li>";
  }

  // ——— Inicialização ———
  function loadConfigIntoForm() {
    var s = getSettings();
    if (seoTitle) seoTitle.value = s.seoTitle || "";
    if (seoDescription) seoDescription.value = s.seoDescription || "";
  }

  renderProductsTable();
  renderHeroesList();
  renderOrders();
  renderStock();
  renderCustomers();
  renderCoupons();
  renderNewsletter();
  renderUsers();
  loadConfigIntoForm();
})();
