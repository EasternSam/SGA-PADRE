<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PIN Kiosco - {{ $studentName }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            /* Fuente comúnmente usada para simular tickets */
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 10px; /* Margen interno del ticket */
            text-align: center;
            color: #000;
            font-size: 12px;
            background-color: #fff;
        }
        .header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .subheader {
            font-size: 10px;
            margin-bottom: 10px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .student-info { 
            margin-bottom: 15px; 
            text-align: left;
        }
        .student-name { 
            font-size: 14px; 
            font-weight: bold; 
            margin: 0 0 3px 0; 
            text-transform: uppercase;
        }
        .student-code { 
            font-size: 12px; 
            margin: 0; 
        }
        .pin-container { 
            margin: 15px 0; 
            padding: 10px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .pin-label { 
            font-size: 12px; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        .pin-code { 
            font-size: 36px; 
            font-weight: bold; 
            letter-spacing: 5px; 
            margin: 0; 
            line-height: 1; 
        }
        .instructions { 
            font-size: 10px; 
            text-align: left; 
            margin-top: 15px;
        }
        .instructions p { 
            margin: 3px 0; 
        }
        .warning { 
            font-weight: bold; 
            margin-top: 8px; 
            text-align: center;
        }
        .footer {
            font-size: 9px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">*** SGA CENTU ***</div>
    <div class="subheader">TICKET DE ACCESO - KIOSCO</div>
    
    <div class="divider"></div>

    <div class="student-info">
        <p class="student-name">{{ $studentName }}</p>
        <p class="student-code">ID/Doc: {{ $studentCode }}</p>
    </div>
    
    <div class="pin-container">
        <div class="pin-label">TU PIN DE ACCESO:</div>
        <div class="pin-code">{{ $pin }}</div>
    </div>
    
    <div class="instructions">
        <p>1. Ve a cualquier Kiosco</p>
        <p>2. Ingresa tu ID/Doc</p>
        <p>3. Ingresa tu PIN</p>
        <p class="warning">¡NO COMPARTAS ESTE PIN!</p>
    </div>

    <div class="divider"></div>

    <div class="footer">
        Generado: {{ date('d/m/Y h:i A') }}<br>
        ¡Guarde este ticket!
    </div>
</body>
</html>
