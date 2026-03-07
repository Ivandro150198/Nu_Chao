const DEFAULT_PRODUCTS = [
  {
    id: 1,
    name: "Conjunto Ankara Feminino",
    category: "feminino",
    price: 35000,
    location: "Bissau",
    sizes: ["S", "M", "L"],
    tag: "new",
    image: "assets/prod-1.jpg",
    description:
      "Conjunto de top e saia em tecido Ankara, ideal para eventos e ocasiões especiais.",
    inStock: true
  },
  {
    id: 2,
    name: "Camisa Crioula Masculina",
    category: "masculino",
    price: 28000,
    location: "Bissau",
    sizes: ["M", "L", "XL"],
    tag: "bestseller",
    image: "assets/prod-2.jpg",
    description:
      "Camisa de manga curta com estampa crioula, perfeita para o dia-a-dia e encontros informais.",
    inStock: true
  },
  {
    id: 3,
    name: "Vestido Longo Kente",
    category: "feminino",
    price: 42000,
    location: "Cacheu",
    sizes: ["S", "M"],
    tag: "bestseller",
    image: "assets/prod-3.jpg",
    description:
      "Vestido longo em padrão Kente, com caimento elegante para cerimónias e festas.",
    inStock: true
  },
  {
    id: 4,
    name: "Túnica Unissex Tradicional",
    category: "unissex",
    price: 32000,
    location: "Bissau",
    sizes: ["S", "M", "L", "XL"],
    tag: "new",
    image: "assets/prod-4.jpg",
    description:
      "Túnica tradicional unissex, confortável e versátil para várias ocasiões.",
    inStock: true
  },
  {
    id: 5,
    name: "Saia Midi Estampada",
    category: "feminino",
    price: 25000,
    location: "Bissau",
    sizes: ["S", "M", "L"],
    tag: null,
    image: "assets/prod-5.jpg",
    description:
      "Saia midi com estampa africana, combina com camisas lisas e sandálias.",
    inStock: true
  },
  {
    id: 6,
    name: "Calça Social Crioula",
    category: "masculino",
    price: 30000,
    location: "Gabu",
    sizes: ["M", "L"],
    tag: null,
    image: "assets/prod-6.jpg",
    description:
      "Calça social com detalhes de tecido africano, ideal para trabalho ou eventos.",
    inStock: true
  },
  {
    id: 7,
    name: "Lenço / Turbante Africano",
    category: "acessorios",
    price: 10000,
    location: "Bissau",
    sizes: ["Único"],
    tag: "bestseller",
    image: "assets/prod-7.jpg",
    description:
      "Lenço versátil que pode ser usado como turbante, cinto ou acessório de ombro.",
    inStock: true
  },
  {
    id: 8,
    name: "Camisa Unissex Casual",
    category: "unissex",
    price: 27000,
    location: "Bafatá",
    sizes: ["S", "M", "L"],
    tag: null,
    image: "assets/prod-8.jpg",
    description:
      "Camisa unissex leve e confortável, ideal para o clima da Guiné-Bissau.",
    inStock: true
  }
];

function getProducts() {
  const list = window.LojaDB && window.LojaDB.getProducts ? window.LojaDB.getProducts() : [];
  return Array.isArray(list) && list.length ? list : DEFAULT_PRODUCTS;
}

let products = getProducts();

let cart = [];

const CART_KEY = "nu-chao-cart-v1";
const HEROES_KEY = "nu-chao-heroes";

const DEFAULT_HEROES = [
  {
    id: 1,
    image: "assets/hero-banner.png",
    title: "Roupas de Fuca · Loja No Chão",
    subtitle: "By Aissatu Jafono. Moda inspirada na Guiné-Bissau com tecidos africanos e estilo crioulo.",
    badges: ["Pagamento na entrega (Bissau)", "Entregas em até 48h", "Suporte via WhatsApp"],
    gradient: false
  },
  {
    id: 2,
    image: "",
    title: "Moda inspirada na Guiné-Bissau",
    subtitle: "Descubra coleções exclusivas com tecidos africanos, cortes modernos e envio rápido em todo o país.",
    badges: ["Pagamento na entrega (Bissau)", "Entregas em até 48h", "Suporte via WhatsApp"],
    gradient: true
  }
];

function saveCart() {
  try {
    window.localStorage.setItem(CART_KEY, JSON.stringify(cart));
  } catch {
    // ignore storage errors (private mode, etc.)
  }
}

function loadCart() {
  try {
    const stored = window.localStorage.getItem(CART_KEY);
    if (!stored) return;
    const parsed = JSON.parse(stored);
    if (Array.isArray(parsed)) {
      cart = parsed;
    }
  } catch {
    // ignore parse errors
  }
}

function getHeroes() {
  const list = window.LojaDB && window.LojaDB.getHeroes ? window.LojaDB.getHeroes() : [];
  return Array.isArray(list) && list.length ? list : DEFAULT_HEROES.slice();
}

function buildHeroCarousel() {
  const heroes = getHeroes();
  HERO_SLIDES = heroes.length;
  const track = document.getElementById("heroTrack");
  if (!track) return;
  track.innerHTML = heroes
    .map((h, i) => {
      const isGradient = !!h.gradient;
      const style = isGradient ? "" : " background-image: url('" + (h.image || "").replace(/'/g, "\\'") + "');";
      const badges = (h.badges || []).map((b) => "<span>&#10003; " + String(b).replace(/</g, "&lt;") + "</span>").join("");
      return (
        '<div class="hero-slide ' +
        (isGradient ? "hero-slide--gradient" : "hero-slide--image") +
        '" data-slide="' +
        i +
        '" style="' +
        style +
        '">' +
        '<div class="hero-overlay' +
        (isGradient ? " hero-overlay--dark" : "") +
        '"></div>' +
        '<div class="container hero-inner">' +
        '<div class="hero-text">' +
        "<h1>" +
        (h.title || "").replace(/</g, "&lt;") +
        "</h1>" +
        "<p>" +
        (h.subtitle || "").replace(/</g, "&lt;") +
        "</p>" +
        '<div class="hero-actions"><a href="#products" class="btn primary">Ver cole&#231;&#245;es</a> <a href="#about" class="btn ghost">Saber mais</a></div>' +
        '<div class="hero-badges">' +
        badges +
        "</div></div>" +
        (isGradient
          ? '<div class="hero-image"><div class="hero-card"><span class="pill">Nova Cole&#231;&#227;o</span><h2>Estilo Crioulo</h2><p>Pe&#231;as pensadas para o dia-a-dia, festas e cerim&#243;nias.</p></div></div>'
          : "") +
        "</div></div>"
      );
    })
    .join("");
}

let HERO_SLIDES = 0;
let heroIndex = 0;
let heroAutoplay = null;

function goToHeroSlide(index) {
  heroIndex = (index + HERO_SLIDES) % HERO_SLIDES;
  if (heroTrack) {
    heroTrack.style.transform = `translateX(-${heroIndex * 100}%)`;
  }
  document.querySelectorAll(".hero-dots .dot").forEach((dot, i) => {
    dot.classList.toggle("active", i === heroIndex);
    dot.setAttribute("aria-selected", i === heroIndex);
  });
}

function heroNextSlide() {
  goToHeroSlide(heroIndex + 1);
}

function heroPrevSlide() {
  goToHeroSlide(heroIndex - 1);
}

function initHeroCarousel() {
  if (!heroTrack || HERO_SLIDES === 0) return;

  heroDots.innerHTML = "";
  for (let i = 0; i < HERO_SLIDES; i++) {
    const dot = document.createElement("button");
    dot.type = "button";
    dot.className = "dot" + (i === 0 ? " active" : "");
    dot.setAttribute("aria-label", `Ir para slide ${i + 1}`);
    dot.setAttribute("aria-selected", i === 0);
    dot.addEventListener("click", () => goToHeroSlide(i));
    heroDots.appendChild(dot);
  }

  if (heroPrev) heroPrev.addEventListener("click", heroPrevSlide);
  if (heroNext) heroNext.addEventListener("click", heroNextSlide);

  function startAutoplay() {
    if (heroAutoplay) clearInterval(heroAutoplay);
    heroAutoplay = setInterval(heroNextSlide, 6000);
  }
  startAutoplay();
  const carousel = heroTrack.closest(".hero-carousel");
  if (carousel) {
    carousel.addEventListener("mouseenter", () => {
      if (heroAutoplay) clearInterval(heroAutoplay);
      heroAutoplay = null;
    });
    carousel.addEventListener("mouseleave", startAutoplay);
  }
}

const productsGrid = document.getElementById("productsGrid");
const heroTrack = document.getElementById("heroTrack");
const heroPrev = document.getElementById("heroPrev");
const heroNext = document.getElementById("heroNext");
const heroDots = document.getElementById("heroDots");
const categoryFilter = document.getElementById("categoryFilter");
const sortFilter = document.getElementById("sortFilter");
const priceFilter = document.getElementById("priceFilter");
const priceLabel = document.getElementById("priceLabel");
const searchInput = document.getElementById("searchInput");

const searchToggle = document.getElementById("searchToggle");
const searchBar = document.getElementById("searchBar");

const cartToggle = document.getElementById("cartToggle");
const cartDrawer = document.getElementById("cartDrawer");
const cartClose = document.getElementById("cartClose");
const backdrop = document.getElementById("backdrop");
const cartItems = document.getElementById("cartItems");
const cartCount = document.getElementById("cartCount");
const cartTotal = document.getElementById("cartTotal");
const checkoutButton = document.getElementById("checkoutButton");

const productViewModal = document.getElementById("productViewModal");
const productViewContent = document.getElementById("productViewContent");
const productViewClose = document.getElementById("productViewClose");

function formatPrice(value) {
  return `${value.toLocaleString("pt-PT")} CFA`;
}

function renderProducts() {
  const searchTerm = searchInput.value.trim().toLowerCase();
  const maxPrice = Number(priceFilter.value);
  const category = categoryFilter.value;
  const sort = sortFilter.value;

  const productList = getProducts();
  let filtered = productList.filter((p) => {
    const matchesCategory = category === "all" || p.category === category;
    const matchesPrice = p.price <= maxPrice;
    const matchesSearch = !searchTerm || p.name.toLowerCase().includes(searchTerm);
    return matchesCategory && matchesPrice && matchesSearch;
  });

  if (sort === "price-asc") {
    filtered = filtered.slice().sort((a, b) => a.price - b.price);
  } else if (sort === "price-desc") {
    filtered = filtered.slice().sort((a, b) => b.price - a.price);
  } else if (sort === "featured") {
    filtered = filtered.slice().sort((a, b) => {
      const weight = (p) => (p.tag === "bestseller" ? 2 : p.tag === "new" ? 1 : 0);
      return weight(b) - weight(a);
    });
  }

  productsGrid.innerHTML = "";

  if (!filtered.length) {
    productsGrid.innerHTML = `<p style="grid-column: 1 / -1; color: #a3a3b5; font-size: 13px;">Nenhum produto encontrado com os filtros selecionados.</p>`;
    return;
  }

  filtered.forEach((product) => {
    const card = document.createElement("article");
    card.className = "product-card";

    const tagText =
      product.tag === "new"
        ? "Novo"
        : product.tag === "bestseller"
        ? "Mais vendido"
        : null;

    const sizeOptions = product.sizes
      .map((s) => `<option value="${s}">${s}</option>`)
      .join("");

    card.innerHTML = `
      ${tagText ? `<span class="product-tag ${product.tag === "new" ? "product-tag--new" : "product-tag--bestseller"}">${tagText}</span>` : ""}
      <div class="product-image">
        ${
          product.image
            ? `<img src="${product.image}" alt="${product.name}">`
            : `<div class="product-pattern"></div>
        <div class="product-placeholder" aria-hidden="true"></div>`
        }
      </div>
      <div class="product-info">
        <h3 class="product-name">${product.name}</h3>
        <div class="product-meta">
          <span class="product-price">${formatPrice(product.price)}</span>
        </div>
        <div class="product-size-row">
          <label class="product-size-label">
            Tamanho:
            <select class="product-size-select" data-id="${product.id}">
              ${sizeOptions}
            </select>
          </label>
          <span class="product-stock">${product.inStock ? "Em stock" : "Esgotado"}</span>
        </div>
        <div class="product-footer">
          <span class="product-location">📍 ${product.location}</span>
          <div class="product-card-actions">
            <button type="button" class="product-view-btn" data-id="${product.id}">Ver detalhes</button>
            <button class="product-add" data-id="${product.id}" ${product.inStock ? "" : "disabled"}>Adicionar</button>
          </div>
        </div>
      </div>
    `;

    productsGrid.appendChild(card);
  });

  document
    .querySelectorAll(".product-add")
    .forEach((button) => button.addEventListener("click", handleAddToCart));

  document.querySelectorAll(".product-view-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = Number(btn.dataset.id);
      const product = getProducts().find((p) => p.id === id);
      if (product) openProductView(product);
    });
  });
}

function handleAddToCart(event) {
  const id = Number(event.currentTarget.dataset.id);
  const product = getProducts().find((p) => p.id === id);
  if (!product) return;

  const card = event.currentTarget.closest(".product-card");
  const sizeSelect = card
    ? card.querySelector('.product-size-select[data-id="' + id + '"]')
    : null;
  const selectedSize = sizeSelect ? sizeSelect.value : product.sizes[0] || "Único";

  const existing = cart.find((item) => item.id === id && item.size === selectedSize);
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({ ...product, quantity: 1, size: selectedSize });
  }

  renderCart();
  openCart();
}

function openProductView(product) {
  if (!productViewModal || !productViewContent) return;
  const tagText =
    product.tag === "new"
      ? "Novo"
      : product.tag === "bestseller"
      ? "Mais vendido"
      : null;
  const sizeOptions = product.sizes
    .map((s) => `<option value="${s}">${s}</option>`)
    .join("");
  productViewContent.innerHTML = `
    <div class="product-view-image">
      ${
        product.image
          ? `<img src="${product.image}" alt="${product.name}">`
          : `<div class="product-pattern"></div><div class="product-placeholder" aria-hidden="true"></div>`
      }
    </div>
    ${tagText ? `<span class="product-view-tag ${product.tag === "new" ? "product-view-tag--new" : "product-view-tag--bestseller"}">${tagText}</span>` : ""}
    <h2 class="product-view-title" id="productViewTitle">${product.name}</h2>
    <p class="product-view-price">${formatPrice(product.price)}</p>
    ${product.description ? `<p class="product-view-desc">${product.description}</p>` : ""}
    <p class="product-view-meta">📍 ${product.location} · ${product.inStock ? "Em stock" : "Esgotado"}</p>
    <div class="product-view-size-row">
      <label>Tamanho
        <select class="product-view-size-select" data-product-id="${product.id}">
          ${sizeOptions}
        </select>
      </label>
    </div>
    <div class="product-view-actions">
      <button type="button" class="btn primary product-view-add-btn" data-product-id="${product.id}" ${product.inStock ? "" : "disabled"}>Adicionar ao carrinho</button>
      <button type="button" class="btn ghost product-view-close-btn">Fechar</button>
    </div>
  `;

  const addBtn = productViewContent.querySelector(".product-view-add-btn");
  const closeBtn = productViewContent.querySelector(".product-view-close-btn");
  const sizeSelect = productViewContent.querySelector(".product-view-size-select");

  if (addBtn) {
    addBtn.addEventListener("click", () => {
      const selectedSize = sizeSelect ? sizeSelect.value : product.sizes[0] || "Único";
      const existing = cart.find((item) => item.id === product.id && item.size === selectedSize);
      if (existing) {
        existing.quantity += 1;
      } else {
        cart.push({ ...product, quantity: 1, size: selectedSize });
      }
      renderCart();
      saveCart();
      closeProductView();
      openCart();
    });
  }
  if (closeBtn) closeBtn.addEventListener("click", closeProductView);

  productViewModal.classList.add("visible");
  productViewModal.setAttribute("aria-hidden", "false");
}

function closeProductView() {
  if (!productViewModal) return;
  productViewModal.classList.remove("visible");
  productViewModal.setAttribute("aria-hidden", "true");
}

function renderCart() {
  if (!cart.length) {
    cartItems.innerHTML = `<p class="cart-empty">O seu carrinho está vazio.</p>`;
    cartCount.textContent = "0";
    cartTotal.textContent = formatPrice(0);
    return;
  }

  cartItems.innerHTML = "";

  cart.forEach((item) => {
    const row = document.createElement("div");
    row.className = "cart-item";
    row.innerHTML = `
      <div class="cart-item-thumb"></div>
      <div class="cart-item-info">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-meta">
          <span>${item.category}</span> • <span>Tam: ${item.size || "Único"}</span> • <span>Qtd: ${item.quantity}</span>
        </div>
      </div>
      <div class="cart-item-actions">
        <div class="cart-item-price">${formatPrice(item.price * item.quantity)}</div>
        <div class="qty-control">
          <button class="qty-btn" data-action="dec" data-id="${item.id}" data-size="${item.size}">-</button>
          <span>${item.quantity}</span>
          <button class="qty-btn" data-action="inc" data-id="${item.id}" data-size="${item.size}">+</button>
        </div>
        <button class="remove-btn" data-id="${item.id}" data-size="${item.size}">Remover</button>
      </div>
    `;

    cartItems.appendChild(row);
  });

  cartItems.querySelectorAll(".qty-btn").forEach((btn) =>
    btn.addEventListener("click", (event) => {
      const id = Number(event.currentTarget.dataset.id);
      const action = event.currentTarget.dataset.action;
      const size = event.currentTarget.dataset.size;
      const item = cart.find((p) => p.id === id && p.size === size);
      if (!item) return;

      if (action === "inc") {
        item.quantity += 1;
      } else if (action === "dec") {
        item.quantity -= 1;
        if (item.quantity <= 0) {
          cart = cart.filter((p) => p.id !== id);
        }
      }
      renderCart();
    })
  );

  cartItems.querySelectorAll(".remove-btn").forEach((btn) =>
    btn.addEventListener("click", (event) => {
      const id = Number(event.currentTarget.dataset.id);
      const size = event.currentTarget.dataset.size;
      cart = cart.filter((p) => !(p.id === id && p.size === size));
      renderCart();
    })
  );

  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

  cartCount.textContent = String(totalItems);
  cartTotal.textContent = formatPrice(totalPrice);

  saveCart();
}

function openCart() {
  cartDrawer.classList.add("open");
  backdrop.classList.add("visible");
  cartDrawer.setAttribute("aria-hidden", "false");
}

function closeCart() {
  cartDrawer.classList.remove("open");
  backdrop.classList.remove("visible");
  cartDrawer.setAttribute("aria-hidden", "true");
}

searchToggle.addEventListener("click", () => {
  const isActive = searchBar.classList.toggle("active");
  if (isActive) {
    searchInput.focus();
  }
});

cartToggle.addEventListener("click", openCart);
cartClose.addEventListener("click", closeCart);
backdrop.addEventListener("click", closeCart);

categoryFilter.addEventListener("change", renderProducts);
sortFilter.addEventListener("change", renderProducts);
priceFilter.addEventListener("input", () => {
  priceLabel.textContent = `At\u00e9 ${Number(priceFilter.value).toLocaleString("pt-PT")} CFA`;
  renderProducts();
});
searchInput.addEventListener("input", renderProducts);

const checkoutModal = document.getElementById("checkoutModal");
const checkoutForm = document.getElementById("checkoutForm");
const checkoutModalClose = document.getElementById("checkoutModalClose");

function openCheckoutModal() {
  if (checkoutModal) {
    checkoutModal.classList.add("visible");
    checkoutModal.setAttribute("aria-hidden", "false");
  }
}

function closeCheckoutModal() {
  if (checkoutModal) {
    checkoutModal.classList.remove("visible");
    checkoutModal.setAttribute("aria-hidden", "true");
  }
}

checkoutButton.addEventListener("click", () => {
  if (!cart.length) {
    alert("O seu carrinho está vazio.");
    return;
  }
  if (!getSession()) {
    const returnTo = encodeURIComponent("index.html");
    window.location.href = "login.html?return=" + returnTo;
    return;
  }
  openCheckoutModal();
});

if (checkoutForm) {
  checkoutForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const nameEl = document.getElementById("checkoutName");
    const phoneEl = document.getElementById("checkoutPhone");
    const emailEl = document.getElementById("checkoutEmail");
    const addressEl = document.getElementById("checkoutAddress");
    const name = (nameEl && nameEl.value.trim()) || "";
    const phone = (phoneEl && phoneEl.value.trim()) || "";
    const email = (emailEl && emailEl.value.trim()) || "";
    const address = (addressEl && addressEl.value.trim()) || "";
    if (!name || !phone || !address) {
      alert("Preencha nome, telefone e morada.");
      return;
    }

    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const order = {
      customer: { name, phone, email, address },
      items: cart.map((item) => ({
        productId: item.id,
        name: item.name,
        size: item.size || "Único",
        quantity: item.quantity,
        price: item.price
      })),
      total: totalPrice,
      status: "Aguardando Pagamento",
      trackingCode: ""
    };
    if (window.LojaDB && window.LojaDB.addOrder) window.LojaDB.addOrder(order);

    const message = [
      "Olá, gostaria de finalizar um pedido na Nu Chao:",
      "",
      "Dados: " + name + " | " + phone + (email ? " | " + email : ""),
      "Morada: " + address,
      "",
      ...cart.map(
        (item) => `- ${item.quantity}x ${item.name} Tam: ${item.size || "Único"} (${formatPrice(item.price)} cada)`
      ),
      "",
      "Total: " + formatPrice(totalPrice),
      "",
      "Pode confirmar disponibilidade e opções de entrega e pagamento?"
    ].join("\n");

    const encoded = encodeURIComponent(message);
    const whatsappNumber = "2459000000";
    window.open("https://wa.me/" + whatsappNumber + "?text=" + encoded, "_blank");

    cart.length = 0;
    saveCart();
    renderCart();
    closeCart();
    closeCheckoutModal();
    checkoutForm.reset();
  });
}

if (checkoutModalClose) {
  checkoutModalClose.addEventListener("click", closeCheckoutModal);
}
if (checkoutModal) {
  checkoutModal.addEventListener("click", (e) => {
    if (e.target === checkoutModal) closeCheckoutModal();
  });
}

if (productViewClose) {
  productViewClose.addEventListener("click", closeProductView);
}
if (productViewModal) {
  productViewModal.addEventListener("click", (e) => {
    if (e.target === productViewModal) closeProductView();
  });
}

const yearSpan = document.getElementById("year");
if (yearSpan) {
  yearSpan.textContent = new Date().getFullYear();
}

function updateAuthNav() {
  const el = document.getElementById("authNav");
  if (!el) return;
  const session = getSession();
  if (session) {
    el.innerHTML = '<a href="admin.html" class="icon-button auth-icon" aria-label="Admin" title="Admin">&#9881;</a> <a href="#" id="logoutLink" class="icon-button auth-icon" aria-label="Sair" title="Sair">&#128682;</a>';
    const lnk = document.getElementById("logoutLink");
    if (lnk) lnk.addEventListener("click", function (e) { e.preventDefault(); logout(); window.location.reload(); });
  } else {
    el.innerHTML = '<a href="registar.html" class="icon-button auth-icon" aria-label="Criar conta" title="Criar conta">&#128221;</a> <a href="login.html" class="icon-button auth-icon" aria-label="Entrar" title="Entrar">&#128100;</a>';
  }
}

/**
 * Aplica as defini\u00e7\u00f5es do admin (SEO) na p\u00e1gina.
 * Afeta t\u00edtulo e meta descri\u00e7\u00e3o em todos os dispositivos, incluindo mobile.
 */
function applyDefinicoes() {
  if (!window.LojaDB || !window.LojaDB.getSettings) return;
  var s = window.LojaDB.getSettings();
  if (s.seoTitle && String(s.seoTitle).trim()) {
    document.title = String(s.seoTitle).trim();
  }
  var metaDesc = document.getElementById("metaDescription") || document.querySelector('meta[name="description"]');
  if (metaDesc && s.seoDescription && String(s.seoDescription).trim()) {
    metaDesc.setAttribute("content", String(s.seoDescription).trim());
  }
}

function renderAboutAndContact() {
  var aboutEl = document.getElementById("aboutContent");
  var contactEl = document.getElementById("contactContent");
  if (window.LojaDB && window.LojaDB.getAbout) {
    var a = window.LojaDB.getAbout();
    if (aboutEl && (a.title || a.text || a.image)) {
      var imgHtml = a.image ? "<div class=\"about-image-wrap\"><img src=\"" + String(a.image).replace(/"/g, "&quot;") + "\" alt=\"\" class=\"about-image\" /></div>" : "";
      var textHtml = "<div class=\"about-text\">";
      if (a.title) textHtml += "<h3>" + String(a.title).replace(/</g, "&lt;") + "</h3>";
      if (a.text) {
        var paras = String(a.text).split(/\n/).filter(Boolean);
        textHtml += paras.map(function (p) { return "<p>" + p.replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</p>"; }).join("");
      }
      textHtml += "</div>";
      aboutEl.innerHTML = imgHtml + textHtml;
    }
  }
  if (window.LojaDB && window.LojaDB.getContact) {
    var c = window.LojaDB.getContact();
    if (contactEl && (c.phone || c.email || c.address || c.extra || c.hours)) {
      var html = "<h3>Contacto</h3>";
      if (c.extra) html += "<p>" + String(c.extra).replace(/\n/g, "</p><p>").replace(/</g, "&lt;") + "</p>";
      if (c.phone) html += "<p><strong>WhatsApp:</strong> " + String(c.phone).replace(/</g, "&lt;") + "</p>";
      if (c.email) html += "<p><strong>Email:</strong> " + String(c.email).replace(/</g, "&lt;") + "</p>";
      if (c.address) html += "<p><strong>Localiza\u00e7\u00e3o:</strong> " + String(c.address).replace(/</g, "&lt;") + "</p>";
      if (c.hours) html += "<p><strong>Hor\u00e1rio:</strong> " + String(c.hours).replace(/</g, "&lt;") + "</p>";
      contactEl.innerHTML = html;
    }
  }
}

document.addEventListener("DOMContentLoaded", function () {
  loadCart();
  updateAuthNav();
  applyDefinicoes();
  buildHeroCarousel();
  priceLabel.textContent = "At\u00e9 " + Number(priceFilter.value).toLocaleString("pt-PT") + " CFA";
  renderProducts();
  renderCart();
  renderAboutAndContact();
  initHeroCarousel();
});

