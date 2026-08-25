<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    /* Leaflet Tooltips for addresses and resto */
    .leaflet-tooltip.address-tooltip, .leaflet-tooltip.resto-tooltip {
        background: white;
        color: #111827;
        font-weight: 600;
        font-size: 12px;
        border: 1px solid #F3F4F6;
        border-radius: 8px;
        padding: 6px 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        text-align: center;
    }
    .leaflet-tooltip.address-tooltip::before, .leaflet-tooltip.resto-tooltip::before {
        border-top-color: white;
    }
    #map-container {
        position: relative;
        isolation: isolate;
        z-index: 10;
    }
    #map-address-card {
        position: absolute;
        bottom: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 30;
        background: white;
        border-radius: 12px;
        padding: 10px 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        min-width: 260px;
        max-width: 88%;
        text-align: center;
        pointer-events: none;
        border: 1px solid #E5E7EB;
    }
    #map-address-card .card-label {
        font-size: 10px;
        color: #6B7280;
        font-weight: 700;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #map-address-card .card-address {
        font-size: 12px;
        font-weight: 700;
        color: #111827;
        line-height: 1.4;
    }
</style>