@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endonce

{{-- Nouvelle-Caledonie par defaut tant qu'aucune coordonnee n'est renseignee. --}}
<div
    wire:ignore
    x-data="{
        lat: $wire.entangle('data.latitude', true),
        lng: $wire.entangle('data.longitude', true),
        map: null,
        marker: null,
        isDragging: false,
        initMap() {
            const hasCoords = this.lat && this.lng;
            this.map = L.map(this.$refs.mapContainer).setView(
                hasCoords ? [this.lat, this.lng] : [-21.5, 165.5],
                hasCoords ? 15 : 6,
            );

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);

            if (hasCoords) {
                this.placeMarker([this.lat, this.lng]);
            }

            this.$watch('lat', () => this.isDragging || this.syncMarker());
            this.$watch('lng', () => this.isDragging || this.syncMarker());
        },
        placeMarker(position) {
            this.marker = L.marker(position, { draggable: true }).addTo(this.map);
            this.marker.on('dragstart', () => { this.isDragging = true; });
            this.marker.on('dragend', () => {
                const position = this.marker.getLatLng();
                this.lat = position.lat;
                this.lng = position.lng;
                this.isDragging = false;
            });
        },
        syncMarker() {
            if (!this.lat || !this.lng) {
                if (this.marker) {
                    this.map.removeLayer(this.marker);
                    this.marker = null;
                }
                return;
            }

            const position = [this.lat, this.lng];

            if (this.marker) {
                this.marker.setLatLng(position);
            } else {
                this.placeMarker(position);
            }

            this.map.setView(position, 15);
        },
    }"
    x-init="$nextTick(() => initMap())"
>
    <div x-ref="mapContainer" style="height: 320px; border-radius: 0.5rem;"></div>
    <p class="fi-fo-field-wrp-helper-text text-xs text-gray-500 dark:text-gray-400 mt-1">
        Glisse le pin pour corriger la position.
    </p>
</div>
