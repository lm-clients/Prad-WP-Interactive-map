function waitForElement(selector, callback, interval = 100, timeout = 5000) {
  const startTime = Date.now();
  const timer = setInterval(() => {
    const element = document.querySelector(selector);
    if (element) {
      clearInterval(timer);
      callback(element);
    } else if (Date.now() - startTime > timeout) {
      clearInterval(timer);
      console.warn(`Élément ${selector} introuvable après ${timeout}ms`);
    }
  }, interval);
}

function initCarteClickListener() {
  const svgObject = document.getElementById("carte-svg");
  const pointPreview = document.getElementById("point-preview");
  if (!svgObject || !svgObject.contentDocument) return;

  const svg = svgObject.contentDocument.querySelector("svg");
  if (!svg) return;

  const projectNumberInput = document.querySelector(
    '[name="acf[field_numero_projet]"]'
  );

  if (projectNumberInput && pointPreview) {
    projectNumberInput.addEventListener("input", function () {
      pointPreview?.innerHTML = `
        <div class="point-bulle">${this.value || ""}</div>
      `;
    });
  }

  svg.addEventListener("click", function (e) {
    const svgRect = svg.getBoundingClientRect();
    const clickX = e.clientX - svgRect.left;
    const clickY = e.clientY - svgRect.top;

    const xPercent = ((clickX / svgRect.width) * 100).toFixed(2);
    const yPercent = ((clickY / svgRect.height) * 100).toFixed(2);

    const inputX = document.querySelector('[name="acf[field_x_position]"]');
    const inputY = document.querySelector('[name="acf[field_y_position]"]');

    if (inputX && inputY) {
      inputX.value = xPercent;
      inputY.value = yPercent;
      pointPreview.style.left = xPercent + "%";
      pointPreview.style.top = yPercent + "%";

      const currentNumber = projectNumberInput ? projectNumberInput.value : "";
      pointPreview?.innerHTML = `
        <div class="point-bulle">${currentNumber || "•"}</div>
      `;
    }
  });
}

function handlePaysChange() {
  const wrapper = document.getElementById("carte-coord-wrapper");
  if (!wrapper) return;

  jQuery(document).on("change", "#acf-field_pays", function () {
    const pays = this.value || "france";
    const svgUrl = CP_COORD_PICKER.svgBaseUrl + pays + ".svg";

    const newObject = document.createElement("object");
    newObject.setAttribute("id", "carte-svg");
    newObject.setAttribute("type", "image/svg+xml");
    newObject.setAttribute("data", svgUrl);
    newObject.setAttribute("style", "width:100%; height:auto;");

    const oldObject = document.getElementById("carte-svg");
    if (oldObject) {
      oldObject.replaceWith(newObject);
    } else {
      wrapper.appendChild(newObject);
    }

    const pointPreview = document.getElementById("point-preview");
    if (pointPreview) {
      pointPreview.style.left = "-9999px";
      pointPreview.style.top = "-9999px";
    }

    newObject.addEventListener("load", () => {
      initCarteClickListener();
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  waitForElement("#acf-field_pays", handlePaysChange);
  initCarteClickListener();
});

function initCarteClickListenerWhenReady() {
  const svgObject = document.getElementById("carte-svg");

  if (!svgObject) return;

  // Always wait for the object to load
  svgObject.addEventListener("load", function () {
    initCarteClickListener(); // Attach listeners to embedded SVG
  });
}

document.addEventListener("DOMContentLoaded", () => {
  waitForElement("#carte-svg", initCarteClickListenerWhenReady);
  waitForElement("#acf-field_pays", handlePaysChange);
});
