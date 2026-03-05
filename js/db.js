/**
 * Banco de dados local "loja_bd" (localStorage com chave única).
 * Todas as coleções da loja ficam num único objeto guardado em localStorage.
 */
(function () {
  "use strict";

  var DB_KEY = "loja_bd";

  function getDB() {
    try {
      var s = localStorage.getItem(DB_KEY);
      if (s) {
        var data = JSON.parse(s);
        if (data && typeof data === "object") return data;
      }
      /* Migração única: copiar das chaves antigas para loja_bd */
      var migrated = migrateFromLegacyKeys();
      if (migrated) return migrated;
    } catch (e) {}
    return {
      products: [],
      heroes: [],
      orders: [],
      customers: [],
      coupons: [],
      newsletter: [],
      settings: {},
      users: [],
      audit: [],
      contact: {},
      about: {}
    };
  }

  function migrateFromLegacyKeys() {
    try {
      var products = [];
      var s = localStorage.getItem("nu-chao-products");
      if (s) {
        var p = JSON.parse(s);
        if (Array.isArray(p) && p.length) products = p;
      }
      var heroes = [];
      s = localStorage.getItem("nu-chao-heroes");
      if (s) {
        var h = JSON.parse(s);
        if (Array.isArray(h) && h.length) heroes = h;
      }
      var orders = [];
      s = localStorage.getItem("nu-chao-orders");
      if (s) {
        var o = JSON.parse(s);
        if (Array.isArray(o)) orders = o;
      }
      var data = {
        products: products,
        heroes: heroes,
        orders: orders,
        customers: [],
        coupons: [],
        newsletter: [],
        settings: {},
        users: [],
        audit: [],
        contact: {},
        about: {}
      };
      saveDB(data);
      return data;
    } catch (e) {}
    return null;
  }

  function saveDB(data) {
    try {
      localStorage.setItem(DB_KEY, JSON.stringify(data));
    } catch (e) {}
  }

  window.LojaDB = {
    getProducts: function () {
      var db = getDB();
      return Array.isArray(db.products) ? db.products : [];
    },
    saveProducts: function (arr) {
      var db = getDB();
      db.products = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },

    getHeroes: function () {
      var db = getDB();
      return Array.isArray(db.heroes) && db.heroes.length ? db.heroes : [];
    },
    saveHeroes: function (arr) {
      var db = getDB();
      db.heroes = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },

    getOrders: function () {
      var db = getDB();
      return Array.isArray(db.orders) ? db.orders : [];
    },
    saveOrders: function (arr) {
      var db = getDB();
      db.orders = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },
    addOrder: function (order) {
      var db = getDB();
      var list = Array.isArray(db.orders) ? db.orders : [];
      var maxId = list.reduce(function (m, o) { return (o.id > m ? o.id : m); }, 0);
      order.id = maxId + 1;
      order.createdAt = order.updatedAt = new Date().toISOString();
      list.push(order);
      db.orders = list;
      saveDB(db);
      return order.id;
    },

    getCustomers: function () {
      var db = getDB();
      return Array.isArray(db.customers) ? db.customers : [];
    },
    saveCustomers: function (arr) {
      var db = getDB();
      db.customers = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },
    addCustomer: function (customer) {
      var db = getDB();
      var list = Array.isArray(db.customers) ? db.customers : [];
      var maxId = list.reduce(function (m, c) { return (c.id > m ? c.id : m); }, 0);
      customer.id = maxId + 1;
      customer.createdAt = new Date().toISOString();
      list.push(customer);
      db.customers = list;
      saveDB(db);
      return customer.id;
    },

    getCoupons: function () {
      var db = getDB();
      return Array.isArray(db.coupons) ? db.coupons : [];
    },
    saveCoupons: function (arr) {
      var db = getDB();
      db.coupons = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },

    getNewsletter: function () {
      var db = getDB();
      return Array.isArray(db.newsletter) ? db.newsletter : [];
    },
    saveNewsletter: function (arr) {
      var db = getDB();
      db.newsletter = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },

    getSettings: function () {
      var db = getDB();
      return db.settings && typeof db.settings === "object" ? db.settings : {};
    },
    saveSettings: function (obj) {
      var db = getDB();
      db.settings = obj && typeof obj === "object" ? obj : {};
      saveDB(db);
    },

    getUsers: function () {
      var db = getDB();
      return Array.isArray(db.users) ? db.users : [];
    },
    saveUsers: function (arr) {
      var db = getDB();
      db.users = Array.isArray(arr) ? arr : [];
      saveDB(db);
    },

    getAudit: function () {
      var db = getDB();
      return Array.isArray(db.audit) ? db.audit : [];
    },

    getContact: function () {
      var db = getDB();
      return db.contact && typeof db.contact === "object" ? db.contact : {};
    },
    saveContact: function (obj) {
      var db = getDB();
      db.contact = obj && typeof obj === "object" ? obj : {};
      saveDB(db);
    },

    getAbout: function () {
      var db = getDB();
      return db.about && typeof db.about === "object" ? db.about : {};
    },
    saveAbout: function (obj) {
      var db = getDB();
      db.about = obj && typeof obj === "object" ? obj : {};
      saveDB(db);
    },

    appendAudit: function (entry) {
      var db = getDB();
      var list = Array.isArray(db.audit) ? db.audit : [];
      list.unshift({
        user: entry.user || "Admin",
        action: entry.action || "",
        detail: entry.detail || "",
        date: new Date().toISOString()
      });
      if (list.length > 200) list = list.slice(0, 200);
      db.audit = list;
      saveDB(db);
    }
  };
})();
