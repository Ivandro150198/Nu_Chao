/**
 * Modais de cadastro no admin
 */
(function () {
  const openModal = (modal) => {
    if (!modal) return;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    const focusable = modal.querySelector('input, select, textarea, button:not(.modal-close)');
    focusable?.focus();
  };

  const closeModal = (modal) => {
    if (!modal) return;
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.modal-root:not([hidden])')) {
      document.body.classList.remove('modal-open');
    }
  };

  document.querySelectorAll('[data-open-modal]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-open-modal');
      openModal(document.getElementById(id));
    });
  });

  document.querySelectorAll('.modal-root.admin-modal').forEach((modal) => {
    modal.querySelectorAll('[data-close-modal]').forEach((el) => {
      el.addEventListener('click', () => closeModal(modal));
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.modal-root.admin-modal:not([hidden])').forEach(closeModal);
  });

  // Abrir modal de edição se marcado
  document.querySelectorAll('.modal-root.admin-modal[data-auto-open="1"]').forEach(openModal);

  // Preview de imagem no modal
  document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const target = document.getElementById(input.getAttribute('data-preview') || '');
      const file = input.files && input.files[0];
      if (!target || !file) return;
      const url = URL.createObjectURL(file);
      target.innerHTML = '<img src="' + url + '" alt="Pré-visualização">';
    });
  });

  // Preview de várias imagens
  document.querySelectorAll('input[type="file"][data-preview-multi]').forEach((input) => {
    input.addEventListener('change', () => {
      const target = document.getElementById(input.getAttribute('data-preview-multi') || '');
      if (!target) return;
      target.innerHTML = '';
      Array.from(input.files || []).forEach((file) => {
        const url = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Pré-visualização';
        target.appendChild(img);
      });
    });
  });
})();
