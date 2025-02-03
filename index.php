<!-- index.php -->
<?php
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "travel_map";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch locations from the database
$sql = "SELECT * FROM locations";
$result = $conn->query($sql);

$locations = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['image_path'] = json_decode($row['image_path']); // Decode JSON string to array
        $locations[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #map {
            width: 100%;
            height: 600px;
        }
        .modal-img {
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>My Travel Map</h2>
    <div id="map"></div>
</div>

<!-- Bootstrap Modal for Image Gallery -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image Viewer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" class="modal-img" src="" alt="Image">
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" id="prevImage">Previous</button>
                <button type="button" class="btn btn-secondary" id="nextImage">Next</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
// Initialize the map
var map = L.map('map').setView([7.8731, 80.7718], 7); // Coordinates for Sri Lanka

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
}).addTo(map);

// Load locations from PHP
var locations = <?php echo json_encode($locations); ?>;
var currentImageIndex = 0;
var currentImages = [];

// Function to create a popup content with images
function createPopupContent(location) {
    var content = '<h5>' + location.place_name + '</h5>';
    content += '<p>Date: ' + location.travel_date + '</p>';
    
    if (location.image_path && location.image_path.length > 0) {
        content += '<div>';
        location.image_path.forEach(function(image, index) {
            content += '<img src="' + image + '" alt="Image" style="width:100px; height:100px; margin:5px; cursor: pointer;" onclick="openImageModal(' + location.id + ', ' + index + ')">';
        });
        content += '</div>';
    } else {
        content += '<p>No images available.</p>';
    }

    return content;
}

// Function to open modal and display the image
function openImageModal(locationId, index) {
    var location = locations.find(loc => loc.id == locationId);
    if (!location) return;

    currentImages = location.image_path;
    currentImageIndex = index;
    document.getElementById('modalImage').src = currentImages[currentImageIndex];
    $('#imageModal').modal('show');
}

// Navigate to the previous image
document.getElementById('prevImage').addEventListener('click', function() {
    if (currentImageIndex > 0) {
        currentImageIndex--;
        document.getElementById('modalImage').src = currentImages[currentImageIndex];
    }
});

// Navigate to the next image
document.getElementById('nextImage').addEventListener('click', function() {
    if (currentImageIndex < currentImages.length - 1) {
        currentImageIndex++;
        document.getElementById('modalImage').src = currentImages[currentImageIndex];
    }
});

// Add markers to the map
locations.forEach(function(location) {
    var marker = L.marker([location.latitude, location.longitude]).addTo(map);
    var popupContent = createPopupContent(location);
    marker.bindPopup(popupContent);
});
</script>
</body>
</html>
