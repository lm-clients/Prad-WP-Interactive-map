document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("carte-container");
  const carte = document.getElementById("carte-svg");

  if (!container || !carte || typeof CP_MAP_AJAX === "undefined") return;

  const urlParams = new URLSearchParams(window.location.search);
  const currentPays = urlParams.get("pays") || "suisse";

  loadPoints(currentPays);

  function getSelectedFilters() {
    const form = document.getElementById("cp-filtres-avances");
    if (!form) return {};

    const formData = new FormData(form);
    const filters = {};
    for (let [key, value] of formData.entries()) {
      if (value) filters[key] = value;
    }
    return filters;
  }

  function loadPoints(pays) {
    const filters = getSelectedFilters();
    const query = new URLSearchParams({
      action: "cp_get_projets",
      pays,
      ...filters,
    });

    fetch(`${CP_MAP_AJAX.ajax_url}?${query.toString()}`)
      .then((res) => res.json())
      .then((data) => {
        console.log("Chargement des projets pour", pays, data);
        if (!data.success || !Array.isArray(data.data))
          throw new Error("Erreur AJAX");
        document.querySelectorAll(".point-projet").forEach((p) => p.remove());
        data.data.forEach((projet) => {
          const point = document.createElement("div");
          point.className = "point-projet";
          point.style.cssText = `position:absolute;top:${projet.y}%;left:${projet.x}%;...`;
          point.textContent = projet.numero || "•";
          point.dataset.title = projet.title;
          point.dataset.excerpt = projet.excerpt;
          point.dataset.link = projet.link;

          container.appendChild(point);

          point.addEventListener("click", () => {
            showPopup(projet);
          });
        });
      })
      .catch((err) => console.error("Erreur chargement projets :", err));
  }

  // Sur submit du formulaire de filtres
  document
    .getElementById("cp-filtres-avances")
    ?.addEventListener("submit", (e) => {
      e.preventDefault();
      const pays =
        new URLSearchParams(window.location.search).get("pays") || "suisse";
      loadPoints(pays);
    });

  // Mini-carte

  document.querySelectorAll(".cp-mini-carte-form").forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      // cp-mini-carte add or remove active class on click
      // const activeButton = form.querySelector(".cp-mini-carte-button.active");
      // if (activeButton) {
      //   activeButton.classList.remove("active");
      // } else {
      //   form.querySelector(".cp-mini-carte-button").classList.add("active");
      // }

      const pays = form.querySelector('[name="pays"]').value;

      // Active le bouton de la mini-carte
      activeMiniCarteButton(pays);

      // Mise à jour URL
      history.replaceState(null, "", "?pays=" + pays);

      // Remplace la carte
      const oldCarte = document.getElementById("carte-svg");
      const newCarte = document.createElement("object");
      newCarte.id = "carte-svg";
      newCarte.type = "image/svg+xml";
      newCarte.data = CP_MAP_AJAX.svgBaseUrl + pays + ".svg";
      newCarte.style.width = "100%";
      // newCarte.style.height = "70vh";

      oldCarte.replaceWith(newCarte);

      // Recharge les points
      loadPoints(pays);
    });

    document.getElementById("reset-filtres")?.addEventListener("click", () => {
      const pays =
        new URLSearchParams(window.location.search).get("pays") || "suisse";
      loadPoints(pays);
    });
  });

  function activeMiniCarteButton(pays) {
    document.querySelectorAll(".cp-mini-carte-button").forEach((btn) => {
      btn.classList.remove("active");
      console.log("Removing active class from button:", btn);
    });
    const activeButton = document.querySelector(
      `.cp-mini-carte-button[data-pays="${pays}"]`
    );
    console.log("Activating button for pays:", pays, activeButton);
    if (activeButton) {
      activeButton.classList.add("active");
    }
  }

  function showPopup(projet) {
    const popup = document.getElementById("project-popup");
    if (!popup) return;

    // Fill in basic content
    popup.querySelector("#popup-number").textContent = projet.numero || "•";
    popup.querySelector("#popup-title").textContent = projet.title;
    popup.querySelector("#popup-excerpt").textContent = projet.popup_excerpt || "";
    popup.querySelector("#popup-link").href = projet.link;
    const icon = popup.querySelector("#popup-icon img");

    console.log("Showing popup for projet:", projet);

    // Set icon if available
    if (projet.phase_icon) {
      icon.src = projet.phase_icon;
      icon.alt = projet.phase_name || "Phase Icon";
    } else {
      icon.src = ""; // Clear icon if not available
      icon.alt = "";
    }

    // Clear previous metadata containers (phase, type, category)
    ["popup-phase", "popup-secteur", "popup-category"].forEach((id) => {
      const container = popup.querySelector(`#${id}`);
      if (container) container.innerHTML = "";
    });

    // Add metadata points
    addMetadataPoint(popup.querySelector("#popup-phase"), projet.phase_name);
    addMetadataPoint(
      popup.querySelector("#popup-secteur"),
      projet.secteur_name
    );
    addMetadataPoint(
      popup.querySelector("#popup-category"),
      projet.category_name
    );

    // Show popup
    popup.style.display = "block";
  }

  // Helper: Create a metadata point if data exists
  function addMetadataPoint(container, label) {
    if (!container || !label) return;

    const pradPoint = document.createElement("div");
    pradPoint.className = "li-bullet";

    const span = document.createElement("span");
    span.className = "metadata-label";
    span.textContent = label;

    container.appendChild(pradPoint);
    container.appendChild(span);
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("cp-filtres-avances");

  if (form) {
    form.querySelectorAll('input[type="radio"]').forEach((input) => {
      input.addEventListener("change", () => {
        form.dispatchEvent(new Event("submit")); // déclenche le filtrage AJAX
      });
    });

    form.querySelectorAll('input[type="radio"]').forEach((input) => {
      input.addEventListener("change", () => {
        const name = input.name;

        // Supprime la classe active pour tous les boutons de ce groupe
        form
          .querySelectorAll(`input[name="${name}"]`)
          .forEach((el) =>
            el.closest(".cp-filter-icon")?.classList.remove("active")
          );

        // Ajoute la classe active au bouton sélectionné
        input.closest(".cp-filter-icon")?.classList.add("active");

        // Déclenche le filtrage AJAX
        form.dispatchEvent(new Event("submit"));
      });
    });

    document.getElementById("reset-filtres")?.addEventListener("click", (e) => {
      setTimeout(() => {
        form.querySelectorAll(".cp-filter-icon.active").forEach((el) => {
          el.classList.remove("active");
        });
        // Remet l'état "Tous" comme actif pour chaque groupe
        form.querySelectorAll("fieldset").forEach((group) => {
          group
            .querySelector('input[value=""]')
            ?.closest(".cp-filter-icon")
            ?.classList.add("active");
        });

        form.dispatchEvent(new Event("submit"));
      }, 50);
    });
  }
});

function renderMiniCartePoints() {
  document.querySelectorAll(".mini-points").forEach((container) => {
    const pays = container.dataset.pays;

    fetch(`${CP_MAP_AJAX.ajax_url}?action=cp_get_projets&pays=${pays}`)
      .then((res) => res.json())
      .then((data) => {
        if (!data.success || !Array.isArray(data.data)) return;

        const { width, height } = container.getBoundingClientRect();
        data.data.forEach((projet) => {
          const point = document.createElement("div");
          point.className = "point-projet-mini";
          point.style.left = `${projet.x}%`;
          point.style.top = `${projet.y}%`;
          container.appendChild(point);
        });
      });
  });
}

document.addEventListener("DOMContentLoaded", renderMiniCartePoints);

document.addEventListener("DOMContentLoaded", function () {
  const popup = document.getElementById("project-popup");
  const closeBtn = document.getElementById("popup-close");

  // Ferme la popup au clic en dehors
  document.addEventListener("click", (e) => {
    const isPoint = e.target.closest(".point-projet");
    const isPopup = e.target.closest("#project-popup");
    if (!isPoint && !isPopup) {
      popup.style.display = "none";
    }
  });

  // Ferme la popup via la croix
  closeBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    popup.style.display = "none";
  });
});
