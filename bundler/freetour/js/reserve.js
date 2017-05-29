function initMap() {
    var point = {
        lat: {{ app.config.get('general/reserves/meeting/lat') }},
    lng: {{ app.config.get('general/reserves/meeting/lon') }} };
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 18,
        center: point
    });
    var marker = new google.maps.Marker({
        position: point,
        map: map
    });
};