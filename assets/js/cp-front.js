document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".svg-wrapper");
  const carte = document.getElementById("carte-svg");
  const form = document.getElementById("cp-filtres-avances");
  const popup = document.getElementById("project-popup");
  const toggleBtn = document.getElementById("toggle-filtres");
  const filtresContainer = document.getElementById("cp-filtres-avances-container");
  const resetBtn = document.getElementById("reset-filtres");

  if (!container || !carte || typeof CP_MAP_AJAX === "undefined") return;

  const urlParams = new URLSearchParams(window.location.search);
  const currentPays = urlParams.get("pays") || "suisse";

  // === INIT ===
  loadPoints(currentPays);
  renderMiniCartePoints();

  // === HELPERS ===
  function getSelectedFilters(){
    if (!form) return {};
    const formData = new FormData(form);
    return Object.fromEntries([...formData].filter(([_, v]) => v));
  };

  const activeMiniCarteButton = (pays) => {
    document.querySelectorAll(".cp-mini-carte-button").forEach((btn) =>
      btn.classList.toggle("active", btn.dataset.pays === pays)
    );
  };

  const showPopup = (projet) => {
    if (!popup) return;
    popup.style.display = "block";
    popup.querySelector("#popup-number").textContent = projet.numero || "•";
    popup.querySelector("#popup-title").textContent = projet.title;
    popup.querySelector("#popup-excerpt").textContent = projet.popup_excerpt || "";
    popup.querySelector("#popup-link").href = projet.link;

    const icon = popup.querySelector("#popup-icon img");
    icon.src = projet.phase_icon || "";
    icon.alt = projet.phase_name || "";

    ["popup-phase", "popup-secteur", "popup-category"].forEach((id) => {
      const container = popup.querySelector(`#${id}`);
      container && (container.innerHTML = "");
    });

    addMetadataPoint(popup.querySelector("#popup-phase"), projet.phase_name);
    addMetadataPoint(popup.querySelector("#popup-secteur"), projet.secteur_name);
    addMetadataPoint(popup.querySelector("#popup-category"), projet.category_name);
  };

  function addMetadataPoint(container, label) {
    if (!container || !label) return;
    const bullet = `<div class="li-bullet"></div><span class="metadata-label">${label}</span>`;
    container.insertAdjacentHTML("beforeend", bullet);
  };

  function loadPoints(pays) {
    const filters = getSelectedFilters();
    const query = new URLSearchParams({
      action: "cp_get_projets",
      pays,
      ...filters,
    });

    fetch(`${CP_MAP_AJAX.ajax_url}?${query}`)
      .then((res) => res.json())
      .then((data) => {
        if (!data.success || !Array.isArray(data.data)) throw new Error("Erreur AJAX");

        // Clear existing points
        document.querySelectorAll(".point-projet").forEach((p) => p.remove());

        // Add new points
        data.data.forEach((projet) => {
          const point = document.createElement("div");
          point.className = "point-projet";
          point.style.cssText = `position:absolute;top:${projet.y}%;left:${projet.x}%;`;
          point.textContent = projet.numero || "•";

          // Set data attributes for popup
          Object.assign(point.dataset, {
            title: projet.title,
            excerpt: projet.excerpt,
            link: projet.link,
          });

          // Add click event for popup
          point.addEventListener("click", () => showPopup(projet));

          container.appendChild(point);
        });
      })
      .catch((err) => console.error("Erreur chargement projets :", err));
  };

  function renderMiniCartePoints() {
    document.querySelectorAll(".mini-points").forEach((mini) => {
      const pays = mini.dataset.pays;

      fetch(`${CP_MAP_AJAX.ajax_url}?action=cp_get_projets&pays=${pays}`)
        .then((res) => res.json())
        .then((data) => {
          if (!data.success || !Array.isArray(data.data)) return;

          data.data.forEach((projet) => {
            const point = document.createElement("div");
            point.className = "point-projet-mini";
            point.style.cssText = `left:${projet.x}%;top:${projet.y}%;`;
            mini.appendChild(point);
          });
        });
    });
  };

  // === EVENTS ===

  // Filtres : appliquer au changement
  form?.addEventListener("change", (e) => {
    if (e.target.matches('input[type="radio"]')) {
      const groupName = e.target.name;
      form.querySelectorAll(`input[name="${groupName}"]`).forEach((input) =>
        input.closest(".cp-filter-icon")?.classList.remove("active")
      );
      e.target.closest(".cp-filter-icon")?.classList.add("active");
      loadPoints(currentPays);
    }
  });

  // Filtres : réinitialiser
  resetBtn?.addEventListener("click", () => {
    setTimeout(() => {
      form.querySelectorAll(".cp-filter-icon.active").forEach((el) => {
        el.classList.remove("active");
      });
      form.querySelectorAll("fieldset").forEach((group) => {
        group.querySelector('input[value=""]')?.closest(".cp-filter-icon")?.classList.add("active");
      });
      loadPoints(currentPays);
    }, 50);
  });

  // Mini-cartes : changement de pays
  document.querySelectorAll(".cp-mini-carte-form").forEach((miniForm) => {
    miniForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const pays = miniForm.querySelector('[name="pays"]').value;

      // Active le bouton + met à jour l’URL
      activeMiniCarteButton(pays);
      history.replaceState(null, "", "?pays=" + pays);

      // Change la carte
      const carte = document.getElementById("carte-svg");
      const newCarte = document.createElement("object");
      newCarte.id = "carte-svg";
      newCarte.type = "image/svg+xml";
      newCarte.data = `${CP_MAP_AJAX.svgBaseUrl}${pays}.svg`;
      carte.replaceWith(newCarte);

      // Recharge les points
      loadPoints(pays);
    });
  });

  // Popup : fermer
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".point-projet") && !e.target.closest("#project-popup")) {
      popup.style.display = "none";
    }
  });

  popup?.querySelector("#popup-close")?.addEventListener("click", (e) => {
    e.stopPropagation();
    popup.style.display = "none";
  });

  // Filtres dropdown (mobile)
  toggleBtn?.addEventListener("click", () => {
    const isOpen = toggleBtn.getAttribute("aria-expanded") === "true";
    toggleBtn.setAttribute("aria-expanded", !isOpen);
    filtresContainer?.classList.toggle("open");
    toggleBtn.querySelector(".cp-dropdown-icon").textContent = isOpen ? "+" : "–";
  });
});
