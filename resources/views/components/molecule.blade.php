<div
    x-data="moleculeViewer({
        moleculeData: @js($moleculeData),
        moleculeFormat: @js($moleculeFormat),
        mode: @js($mode),
        style: @js($style),
        backgroundColor: @js($backgroundColor),
        viewerOptions: @js($viewerOptions),
        modelOptions: @js($modelOptions),
        styleOptions: @js($styleOptions),
    })"
    x-init="init()"
    wire:ignore.self
    class="molecule-viewer-container"
    style="width: {{ $width }}; height: {{ $height }}; position: relative;"
>
    @if($error)
        <div class="molecule-error" style="
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #dc2626;
            padding: 1rem;
            text-align: center;
        ">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px; margin: 0 auto 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p style="margin: 0; font-size: 14px;">{{ $error }}</p>
            </div>
        </div>
    @else
        <div
            x-ref="viewer"
            x-show="!loading"
            style="width: 100%; height: 100%;"
        ></div>

        <div
            x-show="loading"
            style="
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,0.8);
            "
        >
            <svg style="width: 32px; height: 32px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    @endif
</div>

@assets
<script src="https://3Dmol.org/build/3Dmol-min.js"></script>
<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endassets

@script
<script>
    Alpine.data('moleculeViewer', (config) => ({
        viewer: null,
        rotationInterval: null,
        loading: true,

        init() {
            this.$nextTick(() => {
                this.createViewer();
            });

            this.$wire.$watch('moleculeData', () => {
                this.$nextTick(() => this.createViewer());
            });

            this.$wire.$watch('mode', () => {
                this.applyMode();
            });
        },

        createViewer() {
            if (!config.moleculeData) {
                this.loading = false;
                return;
            }

            this.loading = true;
            const element = this.$refs.viewer;
            if (!element) {
                this.loading = false;
                return;
            }

            element.innerHTML = '';
            this.stopRotation();

            const viewerOptions = {
                backgroundColor: config.backgroundColor,
                ...(config.viewerOptions || {}),
            };
            this.viewer = $3Dmol.createViewer(element, viewerOptions);

            this.viewer.addModel(config.moleculeData, config.moleculeFormat, config.modelOptions || {});
            this.applyStyle();
            this.applyMode();
            this.viewer.zoomTo();
            this.viewer.render();
            this.loading = false;
        },

        mergeStyle(baseStyle, overrides) {
            const output = { ...baseStyle };

            Object.entries(overrides || {}).forEach(([key, value]) => {
                if (value && typeof value === 'object' && !Array.isArray(value)) {
                    output[key] = this.mergeStyle(output[key] || {}, value);
                } else {
                    output[key] = value;
                }
            });

            return output;
        },

        applyStyle() {
            if (!this.viewer) return;

            const styleMap = {
                'stick': { stick: {} },
                'sphere': { sphere: {} },
                'cartoon': { cartoon: { color: 'spectrum' } },
                'line': { line: {} },
                'cross': { cross: {} },
                'ball-and-stick': { stick: {}, sphere: { scale: 0.3 } },
            };

            const baseStyle = styleMap[config.style] || styleMap['stick'];
            const mergedStyle = this.mergeStyle(baseStyle, config.styleOptions || {});
            this.viewer.setStyle({}, mergedStyle);
        },

        applyMode() {
            if (!this.viewer) return;

            this.stopRotation();
            const viewerElement = this.$refs.viewer;

            if (config.mode === 'rotating') {
                // Disable mouse interactions in rotating mode
                if (viewerElement) {
                    viewerElement.style.pointerEvents = 'none';
                }
                this.startRotation();
            } else if (config.mode === 'static') {
                // Disable mouse interactions in static mode
                if (viewerElement) {
                    viewerElement.style.pointerEvents = 'none';
                }
            } else {
                // Enable mouse interactions for interactive mode
                if (viewerElement) {
                    viewerElement.style.pointerEvents = 'auto';
                }
            }
        },

        startRotation() {
            if (this.rotationInterval) return;

            this.rotationInterval = setInterval(() => {
                if (this.viewer) {
                    this.viewer.rotate(1, 'y');
                    this.viewer.render();
                }
            }, 50);
        },

        stopRotation() {
            if (this.rotationInterval) {
                clearInterval(this.rotationInterval);
                this.rotationInterval = null;
            }
        },

        destroy() {
            this.stopRotation();
            if (this.viewer) {
                this.viewer = null;
            }
        }
    }));
</script>
@endscript
