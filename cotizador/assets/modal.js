let currentModalDocId = 0;
let currentModalDocType = "ems";
let isEditingModal = false;

const modFmt = new Intl.NumberFormat("en-US", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});
const modIntFmt = new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 });

const modIds = [
  "m-cliente",
  "m-direccion",
  "m-servicio",
  "m-tabla-direccion",
  "m-energia",
  "m-cargo",
  "m-subtotal",
  "m-dato-fab",
  "m-agente",
  "m-telefono",
];

const modSolarIds = [
  "ms-cliente",
  "ms-servicio",
  "ms-watts",
  "ms-paneles",
  "ms-kw",
  "ms-m2",
  "ms-rango",
];

function autoResizeEl(el) {
  if (!el) return;
  el.style.height = "auto";
  el.style.height = el.scrollHeight + "px";
}

function syncModalDireccion() {
  const dirEl = document.getElementById("m-direccion");
  const td = document.getElementById("m-tabla-direccion");
  if (!dirEl || !td) return;
  td.value = dirEl.value;
  autoResizeEl(td);
}

function syncModalSolarBanner() {
  const bannerEl = document.getElementById("ms-banner-cliente");
  const clienteEl = document.getElementById("ms-cliente");
  if (!bannerEl || !clienteEl) return;
  const v = clienteEl.value.trim();
  bannerEl.innerText = v !== "" ? v.toUpperCase() : "CLIENTE";
}

function actualizarModalEtiquetaFab() {
  const tipo =
    document.getElementById("m-tipo-demanda")?.value || "Gran Demanda";
  const lbl = document.getElementById("m-label-dato-fab");
  if (lbl) {
    lbl.innerText =
      tipo === "Baja Demanda" ? "Lectura total:" : "Demanda contratada (kW):";
  }
  calcularModalCostoFab();
}

function calcularModalCostoFab() {
  const tipo =
    document.getElementById("m-tipo-demanda")?.value || "Gran Demanda";
  const val = parseFloat(document.getElementById("m-dato-fab")?.value) || 0;
  let costo = 0;

  if (val > 0) {
    if (tipo === "Baja Demanda") {
      costo = (val / 60) * 1.5 * 1000;
    } else {
      costo = val * 1.5 * 1000;
    }
  }
  const resEl = document.getElementById("m-costo-fab");
  if (resEl) resEl.innerText = "$" + modFmt.format(costo);
}

function recalcularModalSolar() {
  const paneles = parseFloat(document.getElementById("ms-paneles")?.value) || 0;
  const watts = parseFloat(document.getElementById("ms-watts")?.value) || 600;
  const kwEl = document.getElementById("ms-kw");
  const m2El = document.getElementById("ms-m2");
  const rangoEl = document.getElementById("ms-rango");

  if (paneles > 0) {
    const kw = (paneles * watts) / 1000;
    if (kwEl) kwEl.value = parseFloat(kw.toFixed(2));
    if (m2El) m2El.value = Math.ceil(paneles * 2.8);
    const minInv = Math.round(kw * 17550);
    const maxInv = Math.round(kw * 20475);
    if (rangoEl)
      rangoEl.value =
        "$" + modIntFmt.format(minInv) + " - $" + modIntFmt.format(maxInv);
  } else {
    if (kwEl) kwEl.value = "";
    if (m2El) m2El.value = "";
    if (rangoEl) rangoEl.value = "";
  }
}

function calcMFE() {
  const e =
    parseFloat(
      document.getElementById("m-energia")?.value.replace(/[^0-9.-]+/g, ""),
    ) || 0;
  const c =
    parseFloat(
      document.getElementById("m-cargo")?.value.replace(/[^0-9.-]+/g, ""),
    ) || 0;
  const ivaEl = document.getElementById("m-iva-cfe");
  const totalEl = document.getElementById("m-total-cfe");

  if (e > 0 || c > 0) {
    const s = e + c;
    const i = s * 0.16;
    if (ivaEl) ivaEl.value = modFmt.format(i);
    if (totalEl) totalEl.value = modFmt.format(s + i);
  } else {
    if (ivaEl) ivaEl.value = "0.00";
    if (totalEl) totalEl.value = "0.00";
  }
}

function calcMP() {
  const s =
    parseFloat(
      document.getElementById("m-subtotal")?.value.replace(/[^0-9.-]+/g, ""),
    ) || 0;
  const i = s * 0.16;
  const t = s + i;

  const ivaEl = document.getElementById("m-iva");
  const totalEl = document.getElementById("m-total");
  const antEl = document.getElementById("m-anticipo");
  const resEl = document.getElementById("m-restante");

  if (ivaEl) ivaEl.value = modFmt.format(i);
  if (totalEl) totalEl.value = modFmt.format(t);
  if (antEl) antEl.innerText = modFmt.format(t * 0.6);
  if (resEl) resEl.innerText = modFmt.format(t * 0.4);
}

// Formateo numérico y cálculos en tiempo real dentro del modal EMS
["m-energia", "m-cargo", "m-subtotal"].forEach((id) => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener("focus", function () {
    if (!this.readOnly) this.value = this.value.replace(/[^0-9.-]+/g, "");
  });
  el.addEventListener("blur", function () {
    if (!this.readOnly) {
      const v = parseFloat(this.value.replace(/[^0-9.-]+/g, ""));
      this.value = !isNaN(v) ? modFmt.format(v) : "0.00";
    }
  });
});

document.getElementById("m-energia")?.addEventListener("input", calcMFE);
document.getElementById("m-cargo")?.addEventListener("input", calcMFE);
document.getElementById("m-subtotal")?.addEventListener("input", calcMP);

function setModalEditState(editing) {
  isEditingModal = editing;
  const btn = document.getElementById("btn-modal-edit");
  const pEms = document.getElementById("modal-page");
  const pSol = document.getElementById("modal-solar-page");
  const ml = document.getElementById("modal-mode-label");
  const demSelect = document.getElementById("m-tipo-demanda");

  modIds.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.readOnly = !editing;
  });
  modSolarIds.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.readOnly = !editing;
  });

  if (demSelect) demSelect.disabled = !editing;

  if (editing) {
    if (currentModalDocType === "solar") {
      pSol?.classList.add("editing-active");
      document.getElementById("ms-cliente")?.focus();
    } else {
      pEms?.classList.add("editing-active");
      document.getElementById("m-cliente")?.focus();
    }
    if (btn) {
      btn.innerText = "💾 Guardar Cambios";
      btn.classList.add("saving-state");
    }
    if (ml) ml.innerText = "Editando";
  } else {
    pEms?.classList.remove("editing-active");
    pSol?.classList.remove("editing-active");
    if (btn) {
      btn.innerText = "✏️ Editar";
      btn.classList.remove("saving-state");
    }
    if (ml) ml.innerText = "Vista Previa";
  }
}

async function toggleEdicionModal() {
  if (!isEditingModal) {
    setModalEditState(true);
    return;
  }

  // Guardar edición de Cotización Solar (PS-)
  if (currentModalDocType === "solar") {
    const payloadSol = {
      id: currentModalDocId,
      cliente: document.getElementById("ms-cliente")?.value || "",
      servicio_cfe: document.getElementById("ms-servicio")?.value || "",
      paneles: parseInt(document.getElementById("ms-paneles")?.value) || 0,
      watts_panel: parseInt(document.getElementById("ms-watts")?.value) || 600,
      potencia_kw: document.getElementById("ms-kw")?.value || "",
      metros_cuadrados: document.getElementById("ms-m2")?.value || "",
      inversion_rango: document.getElementById("ms-rango")?.value || "",
      agente_nombre: "",
      agente_telefono: "",
    };

    try {
      const res = await fetch("guardar_solar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payloadSol),
      });
      const result = await res.json();

      if (result.status === "success") {
        setModalEditState(false);
        const row = document.getElementById("row-solar-" + currentModalDocId);
        if (row) {
          const cEl = row.querySelector(".row-cliente");
          const tEl = row.querySelector(".row-total");
          if (cEl) cEl.innerText = payloadSol.cliente;
          if (tEl) tEl.innerText = payloadSol.inversion_rango;
        }

        const ud = {
          tipo: "solar",
          id: currentModalDocId,
          folio: document.getElementById("ms-folio")?.innerText || "",
          cliente: payloadSol.cliente,
          servicio: payloadSol.servicio_cfe,
          paneles: payloadSol.paneles,
          watts_panel: payloadSol.watts_panel,
          potencia_kw: payloadSol.potencia_kw,
          metros_cuadrados: payloadSol.metros_cuadrados,
          inversion_rango: payloadSol.inversion_rango,
        };
        const udStr = JSON.stringify(ud);
        document
          .getElementById("btn-view-solar-" + currentModalDocId)
          ?.setAttribute("data-doc", udStr);
        document
          .getElementById("btn-pill-solar-" + currentModalDocId)
          ?.setAttribute("data-doc", udStr);

        mostrarFeedbackGuardado();
      } else {
        alert("Error: " + result.message);
      }
    } catch (err) {
      console.error(err);
      alert("Error al guardar.");
    }
    return;
  }

  // Guardar edición de Cotización EMS
  const cv = (v) => (v.trim() !== "" ? "$" + v.trim() : "");
  const payload = {
    id: currentModalDocId,
    folio: document.getElementById("m-folio")?.innerText || "",
    cliente: document.getElementById("m-cliente")?.value || "",
    direccion: document.getElementById("m-direccion")?.value || "",
    servicio: document.getElementById("m-servicio")?.value || "",
    tipo_demanda: document.getElementById("m-tipo-demanda")?.value || "",
    energia: cv(document.getElementById("m-energia")?.value || ""),
    cargo: cv(document.getElementById("m-cargo")?.value || ""),
    total_cfe: cv(document.getElementById("m-total-cfe")?.value || ""),
    subtotal: cv(document.getElementById("m-subtotal")?.value || ""),
    iva: cv(document.getElementById("m-iva")?.value || ""),
    total: cv(document.getElementById("m-total")?.value || ""),
    anticipo: cv(document.getElementById("m-anticipo")?.innerText || ""),
    restante: cv(document.getElementById("m-restante")?.innerText || ""),
    dato_fabricacion: document.getElementById("m-dato-fab")?.value.trim() || "",
    costo_fabricacion:
      document.getElementById("m-costo-fab")?.innerText || "$0.00",
    agente_nombre: document.getElementById("m-agente")?.value || "",
    agente_telefono: document.getElementById("m-telefono")?.value || "",
  };

  try {
    const res = await fetch("guardar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await res.json();

    if (result.status === "success") {
      setModalEditState(false);
      const row = document.getElementById("row-ems-" + currentModalDocId);
      if (row) {
        const cEl = row.querySelector(".row-cliente");
        const aEl = row.querySelector(".row-agente");
        const tEl = row.querySelector(".row-total");
        if (cEl) cEl.innerText = payload.cliente;
        if (aEl) aEl.innerText = payload.agente_nombre;
        if (tEl) tEl.innerText = payload.total;
      }

      const ud = {
        tipo: "ems",
        id: currentModalDocId,
        folio: payload.folio,
        cliente: payload.cliente,
        direccion: payload.direccion,
        servicio: payload.servicio,
        tipo_demanda: payload.tipo_demanda,
        energia: document.getElementById("m-energia")?.value || "",
        cargo: document.getElementById("m-cargo")?.value || "",
        iva_cfe: document.getElementById("m-iva-cfe")?.value || "",
        total_cfe: document.getElementById("m-total-cfe")?.value || "",
        subtotal: document.getElementById("m-subtotal")?.value || "",
        iva: document.getElementById("m-iva")?.value || "",
        total: document.getElementById("m-total")?.value || "",
        anticipo: document.getElementById("m-anticipo")?.innerText || "",
        restante: document.getElementById("m-restante")?.innerText || "",
        dato_fabricacion: payload.dato_fabricacion,
        costo_fabricacion: payload.costo_fabricacion,
        agente: payload.agente_nombre,
        telefono: payload.agente_telefono,
      };
      const udStr = JSON.stringify(ud);
      document
        .getElementById("btn-view-ems-" + currentModalDocId)
        ?.setAttribute("data-doc", udStr);
      document
        .getElementById("btn-pill-ems-" + currentModalDocId)
        ?.setAttribute("data-doc", udStr);

      mostrarFeedbackGuardado();
    } else {
      alert("Error: " + result.message);
    }
  } catch (err) {
    console.error(err);
    alert("Error al guardar.");
  }
}

function mostrarFeedbackGuardado() {
  const badge = document.getElementById("modal-save-feedback");
  if (!badge) return;
  badge.style.display = "inline-block";
  setTimeout(() => {
    badge.style.display = "none";
  }, 2500);
}

async function imprimirDesdeModal() {
  if (currentModalDocType === "ems" || currentModalDocType === "solar") {
    if (isEditingModal) await toggleEdicionModal();
    window.print();
  } else {
    const iframe = document.getElementById("r-iframe");
    if (iframe && iframe.style.display !== "none" && iframe.contentWindow) {
      iframe.contentWindow.print();
    } else {
      window.print();
    }
  }
}

function abrirModal(btn) {
  const doc = JSON.parse(btn.getAttribute("data-doc"));
  currentModalDocId = doc.id;
  currentModalDocType = doc.tipo || "ems";
  setModalEditState(false);

  const pEms = document.getElementById("modal-page");
  const pSol = document.getElementById("modal-solar-page");
  const vRec = document.getElementById("modal-recibo-view");
  const bEdit = document.getElementById("btn-modal-edit");
  const bDown = document.getElementById("btn-modal-download");

  if (currentModalDocType === "recibo") {
    if (pEms) pEms.style.display = "none";
    if (pSol) pSol.style.display = "none";
    if (vRec) vRec.style.display = "block";
    if (bEdit) bEdit.style.display = "none";
    if (bDown) {
      bDown.style.display = "inline-block";
      bDown.href = doc.archivo_ruta;
    }

    document.getElementById("modal-folio-label").innerText =
      "Recibo " + doc.folio;
    document.getElementById("r-cliente").innerText = doc.cliente;
    document.getElementById("r-servicio").innerText = doc.servicio || "N/A";

    const ifr = document.getElementById("r-iframe");
    const img = document.getElementById("r-img");
    if (doc.archivo_tipo === "pdf") {
      img.style.display = "none";
      ifr.src = doc.archivo_ruta;
      ifr.style.display = "block";
    } else {
      ifr.style.display = "none";
      ifr.src = "";
      img.src = doc.archivo_ruta;
      img.style.display = "block";
    }
  } else if (currentModalDocType === "solar") {
    if (pEms) pEms.style.display = "none";
    if (vRec) vRec.style.display = "none";
    if (pSol) pSol.style.display = "flex";
    if (bEdit) bEdit.style.display = "inline-block";
    if (bDown) bDown.style.display = "none";

    document.getElementById("modal-folio-label").innerText =
      "Cotización " + doc.folio;
    document.getElementById("ms-folio").innerText = doc.folio;
    document.getElementById("ms-cliente").value = doc.cliente;
    document.getElementById("ms-servicio").value = doc.servicio;
    document.getElementById("ms-watts").value = doc.watts_panel || 600;
    document.getElementById("ms-paneles").value = doc.paneles;
    document.getElementById("ms-kw").value = doc.potencia_kw;
    const msM2El = document.getElementById("ms-m2");
    if (msM2El) msM2El.value = doc.metros_cuadrados || "";
    document.getElementById("ms-rango").value = doc.inversion_rango;
    syncModalSolarBanner();
  } else {
    if (vRec) vRec.style.display = "none";
    if (pSol) pSol.style.display = "none";
    if (pEms) pEms.style.display = "flex";
    if (bEdit) bEdit.style.display = "inline-block";
    if (bDown) bDown.style.display = "none";

    document.getElementById("modal-folio-label").innerText =
      "Cotización " + doc.folio;
    document.getElementById("m-folio").innerText = doc.folio;
    document.getElementById("m-cliente").value = doc.cliente;
    document.getElementById("m-direccion").value = doc.direccion;
    document.getElementById("m-tabla-direccion").value = doc.direccion;
    document.getElementById("m-servicio").value = doc.servicio;
    document.getElementById("m-energia").value = doc.energia;
    document.getElementById("m-cargo").value = doc.cargo;
    document.getElementById("m-iva-cfe").value = doc.iva_cfe;
    document.getElementById("m-total-cfe").value = doc.total_cfe;
    document.getElementById("m-subtotal").value = doc.subtotal;
    document.getElementById("m-iva").value = doc.iva;
    document.getElementById("m-total").value = doc.total;
    document.getElementById("m-anticipo").innerText = doc.anticipo;
    document.getElementById("m-restante").innerText = doc.restante;
    document.getElementById("m-agente").value = doc.agente;
    document.getElementById("m-telefono").value = doc.telefono;

    // Cargar Costo de Fabricación
    const demEl = document.getElementById("m-tipo-demanda");
    const datoFabEl = document.getElementById("m-dato-fab");
    const costoFabEl = document.getElementById("m-costo-fab");

    if (demEl) {
      demEl.value =
        doc.tipo_demanda === "Baja Demanda" ? "Baja Demanda" : "Gran Demanda";
    }
    if (datoFabEl) datoFabEl.value = doc.dato_fabricacion || "";

    actualizarModalEtiquetaFab();
    if (costoFabEl && doc.costo_fabricacion) {
      costoFabEl.innerText = doc.costo_fabricacion;
    }
  }

  document.getElementById("docModal")?.classList.add("open");
  document.body.classList.add("modal-open");
  const modalBody = document.getElementById("modal-body");
  if (modalBody) modalBody.scrollTop = 0;

  if (currentModalDocType === "ems") {
    ["m-cliente", "m-direccion", "m-servicio", "m-tabla-direccion"].forEach(
      (id) => autoResizeEl(document.getElementById(id)),
    );
  } else if (currentModalDocType === "solar") {
    ["ms-cliente", "ms-servicio"].forEach((id) =>
      autoResizeEl(document.getElementById(id)),
    );
  }
}

function cerrarModal() {
  setModalEditState(false);
  const ifr = document.getElementById("r-iframe");
  if (ifr) ifr.src = "";
  document.getElementById("docModal")?.classList.remove("open");
  document.body.classList.remove("modal-open");
}

document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") cerrarModal();
});
