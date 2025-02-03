<!-- admin.php -->
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

// Handle form submission for adding new locations
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_location'])) {
    // Prepare data from the form
    $place_name = $_POST['place_name'];
    $travel_date = $_POST['travel_date'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle file uploads
    $image_paths = [];
    $target_dir = "uploads/";

    foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
        $file_name = basename($_FILES["images"]["name"][$key]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $image_paths[] = $target_file;
        }
    }

    // Convert image paths array to a JSON string for storage
    $images_json = json_encode($image_paths);

    // Insert into database
    $sql = "INSERT INTO locations (place_name, travel_date, latitude, longitude, image_path) VALUES ('$place_name', '$travel_date', '$latitude', '$longitude', '$images_json')";

    if ($conn->query($sql) === TRUE) {
        echo "New location added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM locations WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle edit request
if (isset($_POST['edit_location'])) {
    $id = intval($_POST['id']);
    $place_name = $_POST['place_name'];
    $travel_date = $_POST['travel_date'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle file uploads
    $image_paths = [];
    $target_dir = "uploads/";

    foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
        $file_name = basename($_FILES["images"]["name"][$key]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $image_paths[] = $target_file;
        }
    }

    // Convert image paths array to a JSON string for storage
    $images_json = json_encode($image_paths);

    // Update database record
    $sql = "UPDATE locations SET place_name='$place_name', travel_date='$travel_date', latitude='$latitude', longitude='$longitude', image_path='$images_json' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch all locations for display
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
    <title>Admin Panel</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-img {
            width: 100%;
        }
        .img-thumbnail {
            cursor: pointer;
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Add New Travel Location</h2>
    <form action="admin.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="add_location" value="1">
        <div class="form-group">
            <label for="place_name">Place Name:</label>
            <input type="text" class="form-control" id="place_name" name="place_name" required>
        </div>
        <div class="form-group">
            <label for="place_search">Search Place:</label>
            <input type="text" class="form-control" id="place_search" placeholder="Search for a place">
            <button type="button" class="btn btn-secondary mt-2" onclick="geocodePlace()">Search</button>
        </div>
        <div class="form-group">
            <label for="travel_date">Travel Date:</label>
            <input type="date" class="form-control" id="travel_date" name="travel_date" required>
        </div>
        <div class="form-group">
            <label for="latitude">Latitude:</label>
            <input type="text" class="form-control" id="latitude" name="latitude" readonly required>
        </div>
        <div class="form-group">
            <label for="longitude">Longitude:</label>
            <input type="text" class="form-control" id="longitude" name="longitude" readonly required>
        </div>
        <div class="form-group">
            <label for="images">Upload Images:</label>
            <input type="file" class="form-control-file" id="images" name="images[]" multiple required>
        </div>
        <button type="submit" class="btn btn-primary">Add Location</button>
    </form>

    <h2 class="mt-5">Existing Locations</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Place Name</th>
                <th>Travel Date</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Images</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $location): ?>
            <tr>
                <td><?php echo htmlspecialchars($location['place_name']); ?></td>
                <td><?php echo htmlspecialchars($location['travel_date']); ?></td>
                <td><?php echo htmlspecialchars($location['latitude']); ?></td>
                <td><?php echo htmlspecialchars($location['longitude']); ?></td>
                <td>
                    <?php if (!empty($location['image_path'])): ?>
                        <?php foreach ($location['image_path'] as $image): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Image" class="img-thumbnail" onclick="openImageModal('<?php echo htmlspecialchars(json_encode($location['image_path'])); ?>', <?php echo array_search($image, $location['image_path']); ?>)">
                        <?php endforeach; ?>
                    <?php else: ?>
                        No images
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editLocation(<?php echo $location['id']; ?>)">Edit</button>
                    <a href="admin.php?action=delete&id=<?php echo $location['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Location</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" action="admin.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="edit_location" value="1">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_place_name">Place Name:</label>
                        <input type="text" class="form-control" id="edit_place_name" name="place_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_travel_date">Travel Date:</label>
                        <input type="date" class="form-control" id="edit_travel_date" name="travel_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_latitude">Latitude:</label>
                        <input type="text" class="form-control" id="edit_latitude" name="latitude" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="edit_longitude">Longitude:</label>
                        <input type="text" class="form-control" id="edit_longitude" name="longitude" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="edit_images">Upload Images:</label>
                        <input type="file" class="form-control-file" id="edit_images" name="images[]" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function geocodePlace() {
    var place = document.getElementById('place_search').value;
    var url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(place)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                var result = data[0]; // Use the first result
                document.getElementById('place_name').value = result.display_name;
                document.getElementById('latitude').value = result.lat;
                document.getElementById('longitude').value = result.lon;
            } else {
                alert("No results found.");
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

function openImageModal(imagesJson, index) {
    var images = JSON.parse(imagesJson);
    var modal = new bootstrap.Modal(document.getElementById('imageModal'));
    var modalImage = document.getElementById('modalImage');
    var prevImage = document.getElementById('prevImage');
    var nextImage = document.getElementById('nextImage');
    
    var currentIndex = index;
    
    function showImage(index) {
        modalImage.src = images[index];
        currentIndex = index;
    }
    
    showImage(currentIndex);

    prevImage.addEventListener('click', function() {
        if (currentIndex > 0) {
            showImage(currentIndex - 1);
        }
    });

    nextImage.addEventListener('click', function() {
        if (currentIndex < images.length - 1) {
            showImage(currentIndex + 1);
        }
    });

    modal.show();
}

function editLocation(id) {
    var row = document.querySelector(`tr[data-id='${id}']`);
    document.getElementById('editId').value = id;
    document.getElementById('edit_place_name').value = row.querySelector('td:nth-child(1)').textContent;
    document.getElementById('edit_travel_date').value = row.querySelector('td:nth-child(2)').textContent;
    document.getElementById('edit_latitude').value = row.querySelector('td:nth-child(3)').textContent;
    document.getElementById('edit_longitude').value = row.querySelector('td:nth-child(4)').textContent;
    $('#editModal').modal('show');
}
</script>
</body>
</html>
