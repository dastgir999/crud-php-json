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
                    <td><img src="upload/${record.image}" width="50"></td>
                    <td>
                        <button onclick="editData(${record.id})">Edit</button>
                        <button onclick="deleteData(${record.id})">Delete</button>
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
            url: 'crud.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                loadData();
                $('#dataForm')[0].reset();
            }
        });
    });

    // Edit Data
    window.editData = function (id) {
        $.getJSON('data.json', function (data) {
            const record = data.find(item => item.id == id);
            $('#id').val(record.id);
            $('#name').val(record.name);
        });
    };

    // Delete Data
    window.deleteData = function (id) {
        $.post('crud.php', { id: id, action: 'delete' }, function () {
            loadData();
        });
    };
});
