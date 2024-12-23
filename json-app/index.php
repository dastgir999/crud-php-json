<?php
// Handle AJAX Requests
$dataFile = 'data.json';
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id']) && $_POST['action'] === 'delete') {
        // Delete record
        $id = $_POST['id'];
        $data = array_filter($data, fn($item) => $item['id'] != $id);
    } else {
        // Add/Update record
        $id = $_POST['id'] ?: uniqid();
        $name = $_POST['name'];

        $image = $_FILES['image']['name'];
        if ($image) {
            $target = 'upload/' . basename($image);
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        } else {
            $image = $_POST['existing_image'] ?? '';
        }

        $record = [
            'id' => $id,
            'name' => $name,
            'image' => $image
        ];

        $index = array_search($id, array_column($data, 'id'));
        if ($index !== false) {
            $data[$index] = $record; // Update existing record
        } else {
            $data[] = $record; // Add new record
        }
    }

    file_put_contents($dataFile, json_encode(array_values($data)));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP AJAX CRUD</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        img { max-width: 50px; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>PHP AJAX CRUD with Image Upload</h1>

    <!-- Form to Add/Edit -->
    <form id="dataForm" enctype="multipart/form-data">
        <input type="hidden" name="id" id="id">
        <input type="text" name="name" id="name" placeholder="Name" required>
        <input type="file" name="image" id="image">
        <input type="hidden" name="existing_image" id="existing_image">
        <button type="submit">Save</button>
    </form>

    <!-- Table to Display Data -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="dataTable"></tbody>
    </table>

    <script>
        $(document).ready(function () {
            // Load Data
            function loadData() {
                $.getJSON('data.json', function (data) {
                    let rows = '';
                    $.each(data, function (index, record) {
                        rows += `
                        <tr>
                            <td>${record.id}</td>
                            <td>${record.name}</td>
                            <td><img src="upload/${record.image}" alt="No Image"></td>
                            <td>
                                <button onclick="editData('${record.id}')">Edit</button>
                                <button onclick="deleteData('${record.id}')">Delete</button>
                            </td>
                        </tr>`;
                    });
                    $('#dataTable').html(rows);
                });
            }

            loadData();

            // Add or Update Data
            $('#dataForm').on('submit', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function () {
                        loadData();
                        $('#dataForm')[0].reset();
                        $('#id').val('');
                        $('#existing_image').val('');
                    }
                });
            });

            // Edit Data
            window.editData = function (id) {
                $.getJSON('data.json', function (data) {
                    const record = data.find(item => item.id === id);
                    if (record) {
                        $('#id').val(record.id); // Hidden field for ID
                        $('#name').val(record.name);
                        $('#existing_image').val(record.image);
                    }
                });
            };

            // Delete Data
            window.deleteData = function (id) {
                if (confirm('Are you sure you want to delete this record?')) {
                    $.post('', { id: id, action: 'delete' }, function () {
                        loadData();
                    });
                }
            };
        });
    </script>
</body>
</html>
