<x-filament-panels::page>
    <div class="grid gap-6 xl:grid-cols-2">
        <x-filament::section
            heading="Barcode Configuration"
            description="Set barcode options and generate a preview."
        >
            <form wire:submit="generate" class="space-y-5">
                <div class="space-y-2">
                    <label
                        for="barcode-value"
                        class="text-sm font-medium text-gray-900 dark:text-gray-100"
                    >
                        Barcode Value
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            id="barcode-value"
                            type="text"
                            wire:model.live.debounce.300ms="barcodeValue"
                            placeholder="123456789012"
                            autocomplete="off"
                        />
                    </x-filament::input.wrapper>

                    @error('barcodeValue')
                        <p class="text-sm text-danger-600 dark:text-danger-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label
                            for="barcode-format"
                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Format
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                id="barcode-format"
                                wire:model.live="barcodeFormat"
                            >
                                <option value="CODE128">CODE128</option>
                                <option value="CODE39">CODE39</option>
                                <option value="EAN13">EAN-13</option>
                                <option value="EAN8">EAN-8</option>
                                <option value="UPC">UPC</option>
                                <option value="ITF14">ITF-14</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>

                        @error('barcodeFormat')
                            <p class="text-sm text-danger-600 dark:text-danger-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label
                            for="barcode-width"
                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Line Width (1-4)
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="barcode-width"
                                type="number"
                                min="1"
                                max="4"
                                step="1"
                                wire:model.live="barcodeWidth"
                            />
                        </x-filament::input.wrapper>

                        @error('barcodeWidth')
                            <p class="text-sm text-danger-600 dark:text-danger-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label
                            for="barcode-height"
                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Height (40-200)
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="barcode-height"
                                type="number"
                                min="40"
                                max="200"
                                step="1"
                                wire:model.live="barcodeHeight"
                            />
                        </x-filament::input.wrapper>

                        @error('barcodeHeight')
                            <p class="text-sm text-danger-600 dark:text-danger-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            Display Text
                        </label>

                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                wire:model.live="showText"
                                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                            Show value under barcode
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button type="submit" wire:target="generate">
                        Generate Barcode
                    </x-filament::button>

                    <span
                        wire:loading
                        wire:target="generate"
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        Generating...
                    </span>
                </div>
            </form>
        </x-filament::section>

        <x-filament::section
            heading="Preview"
            description="Preview and download the generated barcode."
        >
            <div class="space-y-4">
                <div
                    wire:ignore
                    class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900"
                >
                    <div class="flex min-h-[220px] items-center justify-center">
                        <svg id="barcode-preview"></svg>
                    </div>
                </div>

                <p id="barcode-preview-status" class="text-sm text-gray-500 dark:text-gray-400">
                    Use the form and click "Generate Barcode" to render a preview.
                </p>

                <x-filament::button
                    type="button"
                    color="gray"
                    icon="heroicon-o-arrow-down-tray"
                    onclick="window.downloadBarcodePng()"
                >
                    Download PNG
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>

    @script
        <script>
            let currentBarcodePayload = @js([
                'value' => $barcodeValue,
                'format' => $barcodeFormat,
                'width' => $barcodeWidth,
                'height' => $barcodeHeight,
                'displayText' => $showText,
            ]);

            const setStatusMessage = (message, type = 'info') => {
                const statusElement = document.getElementById('barcode-preview-status');

                if (! statusElement) {
                    return;
                }

                statusElement.textContent = message;
                statusElement.classList.remove(
                    'text-danger-600',
                    'dark:text-danger-400',
                    'text-gray-500',
                    'dark:text-gray-400',
                );

                if (type === 'error') {
                    statusElement.classList.add('text-danger-600', 'dark:text-danger-400');

                    return;
                }

                statusElement.classList.add('text-gray-500', 'dark:text-gray-400');
            };

            const ensureJsBarcodeLoaded = () => {
                return new Promise((resolve, reject) => {
                    if (window.JsBarcode) {
                        resolve();

                        return;
                    }

                    const existingScript = document.getElementById('jsbarcode-cdn');

                    if (existingScript) {
                        existingScript.addEventListener('load', () => resolve(), { once: true });
                        existingScript.addEventListener('error', () => reject(new Error('Unable to load JsBarcode library.')), { once: true });

                        return;
                    }

                    const script = document.createElement('script');
                    script.id = 'jsbarcode-cdn';
                    script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js';
                    script.defer = true;
                    script.addEventListener('load', () => resolve(), { once: true });
                    script.addEventListener('error', () => reject(new Error('Unable to load JsBarcode library.')), { once: true });
                    document.head.appendChild(script);
                });
            };

            const renderBarcode = async (payload) => {
                const previewElement = document.getElementById('barcode-preview');

                if (! previewElement || ! payload?.value) {
                    return;
                }

                try {
                    await ensureJsBarcodeLoaded();

                    window.JsBarcode(previewElement, payload.value, {
                        format: payload.format,
                        width: Number(payload.width),
                        height: Number(payload.height),
                        displayValue: Boolean(payload.displayText),
                        margin: 10,
                        lineColor: '#111827',
                        background: '#ffffff',
                    });

                    setStatusMessage(`Rendered ${payload.format} barcode.`);
                } catch (error) {
                    setStatusMessage(error?.message ?? 'Unable to render barcode.', 'error');
                }
            };

            window.downloadBarcodePng = () => {
                const previewElement = document.getElementById('barcode-preview');

                if (! previewElement || ! previewElement.hasChildNodes()) {
                    setStatusMessage('Generate a barcode before downloading.', 'error');

                    return;
                }

                const serializedSvg = new XMLSerializer().serializeToString(previewElement);
                const svgBlob = new Blob([serializedSvg], { type: 'image/svg+xml;charset=utf-8' });
                const objectUrl = URL.createObjectURL(svgBlob);
                const image = new Image();

                image.onload = () => {
                    const padding = 24;
                    const canvas = document.createElement('canvas');
                    canvas.width = image.width + (padding * 2);
                    canvas.height = image.height + (padding * 2);

                    const context = canvas.getContext('2d');

                    if (! context) {
                        URL.revokeObjectURL(objectUrl);
                        setStatusMessage('Unable to prepare image download.', 'error');

                        return;
                    }

                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(image, padding, padding);

                    const filenamePart = String(currentBarcodePayload.value ?? 'barcode')
                        .replace(/[^a-zA-Z0-9_-]+/g, '-')
                        .replace(/^-+|-+$/g, '')
                        .slice(0, 40) || 'barcode';

                    const downloadLink = document.createElement('a');
                    downloadLink.href = canvas.toDataURL('image/png');
                    downloadLink.download = `${filenamePart}.png`;
                    downloadLink.click();

                    URL.revokeObjectURL(objectUrl);
                };

                image.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    setStatusMessage('Unable to convert barcode to PNG.', 'error');
                };

                image.src = objectUrl;
            };

            $wire.$on('barcode-generated', (event) => {
                currentBarcodePayload = event?.detail ?? event ?? currentBarcodePayload;
                renderBarcode(currentBarcodePayload);
            });

            renderBarcode(currentBarcodePayload);
        </script>
    @endscript
</x-filament-panels::page>
