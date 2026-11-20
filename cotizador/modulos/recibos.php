<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=recibos");
    exit;
}
?>

<h1 class="section-heading">Subir Recibo de Luz (CFE)</h1>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert-banner alert-success">
        <span>✓ Recibo guardado correctamente con el folio
            <strong><?php echo htmlspecialchars($_GET['folio'] ?? ''); ?></strong>.</span>
        <a href="dashboard.php?seccion=historial" style="color:#166534; font-weight:700;">Ver en Historial →</a>
    </div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert-banner alert-error">
        <span>
            <?php
            if ($_GET['error'] === 'formato') {
                echo "Formato no válido. Solo se permiten archivos PDF, JPG o PNG.";
            } elseif ($_GET['error'] === 'datos') {
                echo "Por favor verifica los datos y selecciona un archivo.";
            } else {
                echo "Ocurrió un error al subir el archivo. Intenta de nuevo.";
            }
            ?>
        </span>
    </div>
<?php endif; ?>

<div class="card">
    <form action="subir_recibo.php" method="POST" enctype="multipart/form-data">
        <div class="form-group full-width" style="margin-bottom: 20px;">
            <label>Archivo del Recibo (PDF o Imagen) *</label>
            <div class="upload-dropzone" id="dropzone">
                <input type="file" name="archivo_recibo" id="archivo_recibo" accept=".pdf,.jpg,.jpeg,.png" required
                    onchange="procesarRecibo(this)">
                <div class="upload-icon">📄</div>
                <div class="upload-title">Haz clic o arrastra aquí el recibo de CFE</div>
                <div class="upload-sub">Si es PDF, extraerá automáticamente el No. de Servicio</div>
                <div class="file-selected-name" id="file-selected-name"></div>
                <div class="extract-loader" id="extract-loader">⚙️ Extrayendo No. de Servicio...</div>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre del Cliente *</label>
                <input type="text" name="cliente" id="r_cliente" class="form-control"
                    placeholder="Ej. Carreteras y Obras del Pacífico" required>
            </div>
            <div class="form-group">
                <label>No. de Servicio CFE</label>
                <input type="text" name="servicio_cfe" id="r_servicio" class="form-control"
                    placeholder="12 dígitos del servicio">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" id="r_direccion" class="form-control"
                    placeholder="Calle, número, colonia y ciudad">
            </div>
            <div class="form-group">
                <label>Agente de Ventas</label>
                <input type="text" name="agente_nombre" class="form-control"
                    placeholder="Nombre del asesor que registra el recibo">
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-primary-ems">
                <span>⬆</span> Guardar Recibo en Sistema
            </button>
        </div>
    </form>
</div>

<script>
    async function procesarRecibo(input) {
        const fileLabel = document.getElementById('file-selected-name');
        const loader = document.getElementById('extract-loader');

        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            fileLabel.innerText = 'Archivo seleccionado: ' + file.name;
            fileLabel.style.display = 'block';

            if (file.type === "application/pdf") {
                loader.style.display = 'flex';
                try {
                    const arrayBuffer = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
                    let fullText = "";

                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        const textContent = await page.getTextContent();
                        const pageText = textContent.items.map(item => item.str).join(' ');
                        fullText += pageText + " ";
                    }

                    let servicioExtracted = "";

                    // Buscar etiqueta explícita (NO. DE SERVICIO / RPU) con 12 dígitos
                    const regexServicio = /(?:NO\.?\s*DE\s*SERVICIO|SERVICIO|RPU)[:\s]*([0-9\s]{12,16})/i;
                    const matchServ = fullText.match(regexServicio);
                    if (matchServ) {
                        const limpio = matchServ[1].replace(/\D/g, '');
                        if (limpio.length === 12) servicioExtracted = limpio;
                    }

                    // Respaldo: primer bloque de 12 dígitos seguidos
                    if (!servicioExtracted) {
                        const match12 = fullText.match(/\b(\d{12})\b/);
                        if (match12) servicioExtracted = match12[1];
                    }

                    if (servicioExtracted.length === 12) {
                        document.getElementById('r_servicio').value = servicioExtracted;
                    }
                } catch (e) {
                    console.log("No se pudo extraer el No. de Servicio:", e);
                }
                loader.style.display = 'none';
            }

            document.getElementById('r_cliente').focus();
        } else {
            fileLabel.style.display = 'none';
            loader.style.display = 'none';
        }
    }
</script>