<!DOCTYPE html>
<html>
<head>
    <title>Test QR Code</title>
</head>
<body>
    <h1>Test QR Code Generation</h1>
    
    <h2>Barcode: TRX20250730000038</h2>
    <p>URL: {{ url('/barcode/scan/TRX20250730000038') }}</p>
    
    <h3>QR Code:</h3>
    <div>
        {!! QrCode::size(200)->generate(url('/barcode/scan/TRX20250730000038')) !!}
    </div>
    
    <h3>Test Link:</h3>
    <a href="{{ url('/barcode/scan/TRX20250730000038') }}" target="_blank">
        Test Scan Direct
    </a>
</body>
</html> 