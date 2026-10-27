{{--
    Cargador de fotos del carrusel con título por foto.
    Variables: $label (texto del encabezado), $hint (ayuda opcional), $categoryName (respaldo si la foto no tiene título)
--}}
<div class="carousel-uploader space-y-4" data-category-name="{{ $categoryName ?? '' }}">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                {{ $label ?? 'Fotos del Carrusel' }}
            </label>
            <p class="text-xs text-slate-500 mt-1">
                {{ $hint ?? 'Ponele un título a cada foto: es el nombre que verán los clientes debajo de la miniatura y al ampliarla.' }}
            </p>
        </div>
        <span id="carousel-new-count" class="hidden text-[11px] font-black text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-full"></span>
    </div>

    <label for="carousel_images_input" id="carousel-dropzone"
           class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/40 hover:bg-purple-50 hover:border-purple-400 transition px-5 py-6 cursor-pointer text-center sm:text-left">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-600 to-fuchsia-600 text-white flex items-center justify-center shadow-lg shadow-purple-500/30 flex-shrink-0">
            <i class="fas fa-images text-lg"></i>
        </div>
        <div>
            <span class="text-sm font-black text-slate-800 block">Elegir o arrastrar fotos</span>
            <span class="text-xs text-slate-500">Podés sumar varias, de a una o todas juntas · JPG, PNG o WEBP</span>
        </div>
        <input type="file" id="carousel_images_input" name="carousel_images[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
    </label>

    <div id="carousel-new-photos" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5"></div>
</div>

@once
@push('scripts')
<script>
    // Vista previa idéntica al SweetAlert de la carta pública
    function previewPublicPhoto(imageUrl, title) {
        const safe = String(title || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: title || '',
            title: safe ? `<span class="text-xl font-bold text-gray-800">${safe}</span>` : '',
            showConfirmButton: false,
            showCloseButton: true,
            background: 'rgba(255, 255, 255, 0.95)',
            backdrop: 'rgba(0, 0, 0, 0.65)',
            customClass: {
                image: 'rounded-2xl max-h-[70vh] object-contain shadow-xl mx-auto',
                popup: 'rounded-3xl p-4'
            }
        });
    }

    (function () {
        const root = document.querySelector('.carousel-uploader');
        const input = document.getElementById('carousel_images_input');
        const grid = document.getElementById('carousel-new-photos');
        const dropzone = document.getElementById('carousel-dropzone');
        const counter = document.getElementById('carousel-new-count');
        const nameInput = document.querySelector('input[name="name"]');
        if (!root || !input || !grid) return;

        const canSyncFiles = (() => { try { return !!new DataTransfer(); } catch (e) { return false; } })();
        let items = []; // { file, url, title }

        const escapeAttr = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
        const fallbackTitle = () => (nameInput && nameInput.value.trim()) || root.dataset.categoryName || 'Nombre de la categoría';

        // Sugerir título a partir del nombre del archivo solo si es legible (ej: "pizza-napolitana.jpg")
        function suggestTitle(filename) {
            const base = filename.replace(/\.[^.]+$/, '');
            if (/\d{3,}|^(img|dsc|pxl|whatsapp|screenshot|captura|image|photo|foto)[\s_-]?/i.test(base)) return '';
            const clean = base.replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
            if (clean.length < 3) return '';
            return clean.charAt(0).toUpperCase() + clean.slice(1);
        }

        function syncInput() {
            if (!canSyncFiles) return;
            const dt = new DataTransfer();
            items.forEach(it => dt.items.add(it.file));
            input.files = dt.files;
        }

        function addFiles(fileList) {
            const incoming = Array.from(fileList || []).filter(f => f.type.startsWith('image/'));
            if (!incoming.length) return;
            if (!canSyncFiles) items = []; // sin DataTransfer el input solo conserva la última selección
            incoming.forEach(file => items.push({ file, url: URL.createObjectURL(file), title: suggestTitle(file.name) }));
            syncInput();
            render();
            // Llevar el foco al primer título vacío para escribirlo enseguida
            const firstEmpty = Array.from(grid.querySelectorAll('[data-title-idx]')).find(el => !el.value);
            if (firstEmpty) firstEmpty.focus({ preventScroll: true });
        }

        function removeItem(idx) {
            URL.revokeObjectURL(items[idx].url);
            items.splice(idx, 1);
            syncInput();
            render();
        }

        function render() {
            counter.classList.toggle('hidden', items.length === 0);
            counter.textContent = items.length === 1 ? '1 foto nueva' : `${items.length} fotos nuevas`;

            grid.innerHTML = items.map((it, idx) => `
                <div class="relative rounded-2xl border-2 border-purple-200 bg-white p-2 shadow-sm flex flex-col">
                    <div class="relative h-28 rounded-xl overflow-hidden bg-slate-100 group">
                        <img src="${it.url}" alt="" class="w-full h-full object-cover">
                        <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-purple-600 text-white text-[10px] font-black uppercase tracking-wider shadow">Nueva</span>
                        <button type="button" data-remove-idx="${idx}" title="Quitar esta foto"
                                class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md transition">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                        <button type="button" data-preview-idx="${idx}" title="Ver cómo se verá en la carta"
                                class="absolute bottom-1.5 right-1.5 px-2 py-1 rounded-full bg-white/95 text-purple-700 text-[10px] font-black flex items-center gap-1 shadow opacity-0 group-hover:opacity-100 focus:opacity-100 transition">
                            <i class="fas fa-magnifying-glass-plus"></i> Vista previa
                        </button>
                    </div>
                    <label class="block mt-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Título de la foto</span>
                        <span class="relative block mt-1">
                            <i class="fas fa-pen absolute left-2.5 top-2.5 text-[10px] text-purple-400 pointer-events-none"></i>
                            <textarea name="carousel_titles[]" data-title-idx="${idx}" rows="2" maxlength="120"
                                      placeholder="${escapeAttr(fallbackTitle())}" style="field-sizing: content;"
                                      class="block w-full resize-none overflow-hidden leading-snug pl-7 pr-2 py-2 text-xs font-bold text-slate-800 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-white transition">${escapeAttr(it.title)}</textarea>
                        </span>
                    </label>
                </div>
            `).join('');
        }

        grid.addEventListener('input', (e) => {
            const idx = e.target.dataset.titleIdx;
            if (idx !== undefined) items[+idx].title = e.target.value;
        });

        grid.addEventListener('keydown', (e) => {
            // Enter pasa al siguiente título en vez de enviar el formulario
            if (e.key === 'Enter' && e.target.dataset.titleIdx !== undefined) {
                e.preventDefault();
                const next = grid.querySelector(`[data-title-idx="${+e.target.dataset.titleIdx + 1}"]`);
                next ? next.focus() : e.target.blur();
            }
        });

        grid.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('[data-remove-idx]');
            if (removeBtn) return removeItem(+removeBtn.dataset.removeIdx);
            const previewBtn = e.target.closest('[data-preview-idx]');
            if (previewBtn) {
                const it = items[+previewBtn.dataset.previewIdx];
                previewPublicPhoto(it.url, it.title.trim() || fallbackTitle());
            }
        });

        input.addEventListener('change', () => addFiles(input.files));

        if (nameInput) {
            nameInput.addEventListener('input', () => {
                grid.querySelectorAll('[data-title-idx]').forEach(el => el.placeholder = fallbackTitle());
            });
        }

        if (dropzone) {
            ['dragenter', 'dragover'].forEach(evt => dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.add('border-purple-500', 'bg-purple-100');
            }));
            ['dragleave', 'drop'].forEach(evt => dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-purple-500', 'bg-purple-100');
            }));
            dropzone.addEventListener('drop', (e) => addFiles(e.dataTransfer?.files));
        }
    })();
</script>
@endpush
@endonce
