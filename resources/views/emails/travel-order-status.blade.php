<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Pedido de Viagem</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            margin: 20px 0;
            background-color: {{ $statusColor }};
            color: #ffffff;
        }
        .message {
            background-color: #f9fafb;
            border-left: 4px solid {{ $statusColor }};
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message h2 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 18px;
        }
        .details {
            margin: 30px 0;
        }
        .details h3 {
            color: #374151;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #1f2937;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-value {
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Travel Request Service</h1>
        </div>

        <div style="text-align: center;">
            <span class="status-badge">{{ ucfirst($status) }}</span>
        </div>

        <div class="message">
            <h2>{{ $statusMessage }}</h2>
            <p>Olá {{ $user->name }},</p>
            <p>Informamos que o status do seu pedido de viagem foi atualizado.</p>
        </div>

        <div class="details">
            <h3>Detalhes do Pedido</h3>
            
            <div class="detail-row">
                <span class="detail-label">Destino:</span>
                <span class="detail-value">{{ $travelOrder->destination }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Data de Partida:</span>
                <span class="detail-value">{{ $travelOrder->departure_date->format('d/m/Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Data de Retorno:</span>
                <span class="detail-value">{{ $travelOrder->return_date->format('d/m/Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value" style="color: {{ $statusColor }}; font-weight: 600;">
                    {{ ucfirst($status) }}
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Data da Solicitação:</span>
                <span class="detail-value">{{ $travelOrder->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
            <p>&copy; {{ date('Y') }} Travel Request Service. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
