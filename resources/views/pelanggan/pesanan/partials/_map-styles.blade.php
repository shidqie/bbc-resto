<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    /* Custom Geocoder Search Bar (Google Maps Style) */
    .leaflet-top.leaflet-right {
        top: 10px !important;
        right: 10px !important;
        left: 10px !important;
        display: flex !important;
        justify-content: center !important;
        pointer-events: none !important;
        z-index: 1000 !important;
    }
    .leaflet-control-geocoder {
        pointer-events: auto !important;
        width: 90% !important;
        max-width: 360px !important;
        margin: 0 !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12) !important;
        border: 1px solid #E5E7EB !important;
        background: white !important;
        overflow: hidden !important;
    }
    .leaflet-control-geocoder-form {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }
    .leaflet-control-geocoder-form input {
        border: none !important;
        padding: 8px 12px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        width: 100% !important;
        background: transparent !important;
        color: #111827 !important;
        outline: none !important;
    }
    .leaflet-control-geocoder-form input:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    .leaflet-control-geocoder-icon {
        background-color: transparent !important;
        border-radius: 0 !important;
        width: 36px !important;
        height: 36px !important;
        background-size: 18px 18px !important;
        opacity: 0.6;
        flex-shrink: 0;
    }
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
    #map-container { position: relative; }
    #map-address-card {
        position: absolute;
        bottom: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
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