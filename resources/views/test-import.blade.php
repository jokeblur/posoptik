<!DOCTYPE html>
<html>
<head>
    <title>Test Import Frame & Lensa</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test Import Frame & Lensa</h1>
    
    <h2>Test 1: Upload File Sederhana</h2>
    <form id="test-upload-form" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="file1">File Excel (.xlsx, .xls):</label>
            <input type="file" id="file1" name="file" accept=".xlsx,.xls" required>
        </div>
        <br>
        <button type="submit">Test Upload</button>
    </form>
    
    <h2>Test 2: Import Frame</h2>
    <form id="test-import-frame-form" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="file2">File Excel Frame (.xlsx, .xls):</label>
            <input type="file" id="file2" name="file" accept=".xlsx,.xls" required>
        </div>
        <br>
        <button type="submit">Test Import Frame</button>
    </form>
    
    <h2>Test 3: Import Lensa</h2>
    <form id="test-import-lensa-form" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="file3">File Excel Lensa (.xlsx, .xls):</label>
            <input type="file" id="file3" name="file" accept=".xlsx,.xls" required>
        </div>
        <br>
        <button type="submit">Test Import Lensa</button>
    </form>
    
    <div id="result"></div>
    <div id="debug"></div>
    
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Test 1: Upload sederhana
        $('#test-upload-form').on('submit', function(e) {
            e.preventDefault();
            
            $('#result').html('<p>Testing upload...</p>');
            $('#debug').html('');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '/test-upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#result').html('<p style="color: green;">' + response.message + '</p>');
                    $('#debug').append('<p>Upload success: ' + JSON.stringify(response.data) + '</p>');
                },
                error: function(xhr, status, error) {
                    $('#debug').append('<p>Upload Status: ' + status + '</p>');
                    $('#debug').append('<p>Upload Error: ' + error + '</p>');
                    $('#debug').append('<p>Upload Response: ' + xhr.responseText + '</p>');
                    
                    let message = 'Upload gagal';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    $('#result').html('<p style="color: red;">' + message + '</p>');
                }
            });
        });
        
        // Test 2: Import Frame
        $('#test-import-frame-form').on('submit', function(e) {
            e.preventDefault();
            
            $('#result').html('<p>Testing import frame...</p>');
            $('#debug').append('<p>Starting frame import test...</p>');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '/test-import-simple',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#result').html('<p style="color: green;">' + response.message + '</p>');
                    $('#debug').append('<p>Frame import success: ' + JSON.stringify(response) + '</p>');
                },
                error: function(xhr, status, error) {
                    $('#debug').append('<p>Frame Import Status: ' + status + '</p>');
                    $('#debug').append('<p>Frame Import Error: ' + error + '</p>');
                    $('#debug').append('<p>Frame Import Response: ' + xhr.responseText + '</p>');
                    
                    let message = 'Frame import gagal';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    $('#result').html('<p style="color: red;">' + message + '</p>');
                }
            });
        });
        
        // Test 3: Import Lensa
        $('#test-import-lensa-form').on('submit', function(e) {
            e.preventDefault();
            
            $('#result').html('<p>Testing import lensa...</p>');
            $('#debug').append('<p>Starting lensa import test...</p>');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '/test-import-lensa',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#result').html('<p style="color: green;">' + response.message + '</p>');
                    $('#debug').append('<p>Lensa import success: ' + JSON.stringify(response) + '</p>');
                },
                error: function(xhr, status, error) {
                    $('#debug').append('<p>Lensa Import Status: ' + status + '</p>');
                    $('#debug').append('<p>Lensa Import Error: ' + error + '</p>');
                    $('#debug').append('<p>Lensa Import Response: ' + xhr.responseText + '</p>');
                    
                    let message = 'Lensa import gagal';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    $('#result').html('<p style="color: red;">' + message + '</p>');
                }
            });
        });
    </script>
</body>
</html> 