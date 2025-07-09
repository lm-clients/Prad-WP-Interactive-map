document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".svg-wrapper");
  const carte = document.getElementById("carte-svg");
  const form = document.getElementById("cp-filtres-avances");
  const popup = document.getElementById("project-popup");
  const toggleBtn = document.getElementById("toggle-filtres");
  const filtresContainer = document.getElementById(
    "cp-filtres-avances-container"
  );
  const resetBtn = document.getElementById("reset-filtres");

  if (!container || !carte || typeof CP_MAP_AJAX === "undefined") return;

  const urlParams = new URLSearchParams(window.location.search);
  let currentPays = urlParams.get("pays") || "suisse";
  let activePoint = null; // Pour savoir quel point est actif
  let activeProjetId = null; // Le projet actuellement affiché dans le popup

  // === INIT ===
  loadPoints(currentPays);
  renderMiniCartePoints();

  // === HELPERS ===
  function getSelectedFilters() {
    if (!form) return {};
    const formData = new FormData(form);
    return Object.fromEntries([...formData].filter(([_, v]) => v));
  }

  function activeMiniCarteButton(pays) {
    document
      .querySelectorAll(".cp-mini-carte-button")
      .forEach((btn) =>
        btn.classList.toggle("active", btn.dataset.pays === pays)
      );
  }

  function showPopup(projet, pointElement) {
    if (!popup) return;

    const mapCenter = document.querySelector(".cp-map-center");
    if (mapCenter) {
      mapCenter.dataset.country = projet.country || currentPays;
    }

    // Si on clique sur le même point → juste focus
    // if (activePoint === pointElement) return;
    if (activeProjetId === projet.id) return;

    // Désactive l'ancien point actif
    document
      .querySelectorAll(".point-projet.active")
      .forEach((el) => el.classList.remove("active"));
    activePoint = pointElement;
    activeProjetId = projet.id;

    activePoint.classList.add("active");

    // Affiche le popup
    popup.style.display = "block";
    popup.dataset.country = projet.country || currentPays;
    popup.querySelector("#popup-number").textContent = projet.numero || "•";
    popup.querySelector("#popup-title").textContent = projet.title;
    popup.querySelector("#popup-excerpt").textContent =
      projet.popup_excerpt || "";
    popup.querySelector("#popup-link").href = projet.link;

    const icon = popup.querySelector("#popup-icon img");
    icon.src = projet.phase_icon || "";
    icon.alt = projet.phase_name || "";

    ["popup-phase", "popup-secteur", "popup-categorie"].forEach((id) => {
      const container = popup.querySelector(`#${id}`);
      container && (container.innerHTML = "");
    });

    // addMetadataPoint(popup.querySelector("#popup-phase"), projet.phase_name);
    addMetadataPoints(
      popup.querySelector("#popup-secteur"),
      projet.secteur_names || []
    );
    addMetadataPoints(
      popup.querySelector("#popup-categorie"),
      projet.categorie_names || []
    );
  }

  // function addMetadataPoint(container, label) {
  //   if (!container || !label) return;
  //   const bullet = `<div class="li-bullet"></div><span class="metadata-label">${label}</span>`;
  //   container.insertAdjacentHTML("beforeend", bullet);
  // }

  function addMetadataPoints(container, labels) {
    if (!container || !Array.isArray(labels) || labels.length === 0) return;
    labels.forEach((label) => {
      const bullet = `<div class="li-bullet"></div><span class="metadata-label">${label}</span>`;
      container.insertAdjacentHTML("beforeend", bullet);
    });
  }

  function loadPoints(pays, callback) {
    const filters = getSelectedFilters();
    const query = new URLSearchParams({
      action: "cp_get_projets",
      pays,
      ...filters,
    });

    fetch(`${CP_MAP_AJAX.ajax_url}?${query}`)
      .then((res) => res.json())
      .then((data) => {
        if (!data.success || !Array.isArray(data.data))
          throw new Error("Erreur AJAX");

        // Clear existing points
        document.querySelectorAll(".point-projet").forEach((p) => p.remove());
        activePoint = null; // Reset point actif
        activeProjetId = null; // Reset projet actif
        popup.style.display = "none"; // Cache popup

        // Add new points
        data.data.forEach((projet) => {
          const point = document.createElement("div");
          point.className = "point-projet";
          point.dataset.projetId = projet.id;
          point.style.cssText = `position:absolute;top:${projet.y}%;left:${projet.x}%;`;
          point.textContent = projet.numero || "•";

          Object.assign(point.dataset, {
            title: projet.title,
            excerpt: projet.excerpt,
            link: projet.link,
          });

          // Clic sur un point : ouvrir popup
          point.addEventListener("click", (e) => {
            e.stopPropagation(); // Empêche le document listener de fermer le popup
            showPopup(projet, point);
          });

          container.appendChild(point);
        });

        if (typeof callback === "function") callback();
      })
      .catch((err) => console.error("Erreur chargement projets :", err));
  }

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
  }

  function replaceCarte(pays, callback) {
    const oldCarte = document.getElementById("carte-svg");
    const newCarte = document.createElement("object");
    newCarte.id = "carte-svg";
    newCarte.type = "image/svg+xml";
    newCarte.data = `${CP_MAP_AJAX.svgBaseUrl}${pays}.svg`;
    newCarte.dataset.pays = pays;
    oldCarte.replaceWith(newCarte);

    loadPoints(pays, callback);
    currentPays = pays;
  }

  function focusOnProjet(projetId) {
    const targetPoint = document.querySelector(
      `.point-projet[data-projet-id="${projetId}"]`
    );
    if (targetPoint) {
      targetPoint.click();

      activeMiniCarteButton(currentPays);
      history.replaceState(null, "", "?pays=" + currentPays);
    }
  }

  // === EVENTS ===

  // Filtres : appliquer au changement
  form?.addEventListener("change", (e) => {
    if (e.target.matches('input[type="radio"]')) {
      const groupName = e.target.name;
      form
        .querySelectorAll(`input[name="${groupName}"]`)
        .forEach((input) =>
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
        group
          .querySelector('input[value=""]')
          ?.closest(".cp-filter-icon")
          ?.classList.add("active");
      });
      loadPoints(currentPays);
    }, 50);
  });

  // Mini-cartes : changement de pays
  document.querySelectorAll(".cp-mini-carte-form").forEach((miniForm) => {
    miniForm.addEventListener("submit", (e) => {
      e.preventDefault();
      
      const pays = miniForm.querySelector('[name="pays"]').value;
      const mapCenter = document.querySelector(".cp-map-center");
      if (mapCenter) {
        mapCenter.dataset.country = pays || currentPays;
      }

      activeMiniCarteButton(pays);
      history.replaceState(null, "", "?pays=" + pays);

      replaceCarte(pays);
    });
  });

  // Slider vertical : focus projet
  document.querySelectorAll(".cp-slider-point").forEach((point) => {
    point.addEventListener("click", (e) => {
      e.stopPropagation(); // Empêche le document listener de fermer le popup

      // Active visuellement ce point du slider
      document
        .querySelectorAll(".cp-slider-point.active")
        .forEach((btn) => btn.classList.remove("active"));
      point.classList.add("active");

      const pays = point.dataset.pays;
      const projetId = point.dataset.projetId;

      // === RESET FORM ===
      if (form) {
        form.reset(); // Réinitialise les valeurs du formulaire
        // Réactive les boutons "Tous"
        form
          .querySelectorAll(".cp-filter-icon.active")
          .forEach((el) => el.classList.remove("active"));
        form
          .querySelectorAll("#cp-filtres-avances-container > * > * > label")
          .forEach((group) => {
            group?.classList.add("active");
          });
      }

      if (currentPays !== pays) {
        replaceCarte(pays, () => focusOnProjet(projetId));
      } else {
        focusOnProjet(projetId);
      }
    });
  });

  // Fermer le popup quand on clique en dehors
  document.addEventListener("click", (e) => {
    if (
      !e.target.closest(".point-projet") &&
      !e.target.closest("#project-popup")
    ) {
      popup.style.display = "none";
      document
        .querySelectorAll(".point-projet.active")
        .forEach((p) => p.classList.remove("active"));
      activePoint = null;
      activeProjetId = null;

      document
        .querySelectorAll(".cp-slider-point.active")
        .forEach((btn) => btn.classList.remove("active"));
    }
  });

  // Fermer le popup via le bouton
  popup?.querySelector("#popup-close")?.addEventListener("click", (e) => {
    e.stopPropagation();
    popup.style.display = "none";
    if (activePoint) activePoint.classList.remove("active");
    activePoint = null;
    activeProjetId = null;

    document
      .querySelectorAll(".cp-slider-point.active")
      .forEach((btn) => btn.classList.remove("active"));
  });

  // Filtres dropdown (mobile)
  toggleBtn?.addEventListener("click", () => {
    const isOpen = toggleBtn.getAttribute("aria-expanded") === "true";
    toggleBtn.setAttribute("aria-expanded", !isOpen);
    filtresContainer?.classList.toggle("open");
    toggleBtn.querySelector(".cp-dropdown-icon > span").textContent = isOpen
      ? "+"
      : "–";
  });
});
