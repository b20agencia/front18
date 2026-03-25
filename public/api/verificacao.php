<?php
/**
 * Arquivo: verificacao.php | Endpoint central para verificação de Liveness (FaceID)
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
    // Geração dinâmica perfeita independente de Laragon, CPanel, Subpastas ou Root DNS
    $basePath = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')) . '/';
    // Fix para o servidor nativo local (php -S) que executa da raiz do projeto:
    if ($basePath === '/' && is_dir(__DIR__ . '/../../public')) {
        $basePath = '/public/';
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front18 | Age Gate</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>css/front18.css?v=<?= time() ?>">
    <style>
        body {
            background-color: #080808; /* fundo sólido — sem transparência no iframe */
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            margin: 0; padding: 0;
            overflow: hidden;
        }

        .catraca-container {
            width: 100%;
            max-width: 480px;
            padding: 24px 32px;
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-top: 4px solid var(--accent-red);
            text-align: center;
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: popIn 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .badge-ai {
            display: inline-block;
            background-color: rgba(230, 0, 0, 0.1);
            color: var(--accent-red);
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            font-family: var(--font-display);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 20px;
            border: 1px solid rgba(230, 0, 0, 0.3);
        }

        h1 {
            font-size: 24px; margin-bottom: 10px; color: #fff;
        }

        p.description {
            font-size: 13px; color: var(--text-secondary); margin-bottom: 16px; line-height: 1.4;
        }

        .video-wrapper {
            width: 100%; height: 240px; background-color: #000;
            border: 1px solid var(--border-color);
            position: relative; margin-bottom: 16px;
            overflow: hidden; border-radius: 8px;
        }

        #camera {
            width: 100%; height: 100%; object-fit: cover;
            transform: scaleX(-1); /* Mirror camera */
        }

        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; justify-content: center; align-items: center;
            background: rgba(3, 3, 3, 0.85); backdrop-filter: blur(4px);
            z-index: 10; color: #fff; font-weight: 700; font-family: var(--font-display);
        }

        button:disabled {
            opacity: 0.5; cursor: not-allowed;
            pointer-events: none;
        }
        
        button.analyzing {
            opacity: 1 !important; 
            background-color: transparent;
            border: 2px solid var(--accent-red);
            color: #fff;
            animation: pulse-border 1.5s infinite;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(230, 0, 0, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(230, 0, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(230, 0, 0, 0); }
        }

        .btn-spinner {
            display: inline-block;
            width: 14px; height: 14px; margin-right: 8px; vertical-align: middle;
            border: 2px solid rgba(255, 255, 255, 0.3); border-top-color: #fff;
            border-radius: 50%; animation: spin 1s linear infinite;
        }

        /* Status Messages */
        .status { font-size: 13px; font-weight: 600; margin-top: 15px; min-height: 20px; font-family: var(--font-display); text-transform: uppercase; letter-spacing: 0.05em; }
        .status.success { color: #00FF80; }
        .status.error { color: var(--accent-red); }
        .status.info { color: var(--text-secondary); }

        .spinner {
            width: 24px; height: 24px; margin: 0 auto 10px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid var(--accent-red);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <div class="catraca-container">
        <!-- Badge -->
        <span class="badge-ai">Engine Neural Local</span>
        
        <!-- Títulos -->
        <h1>Acesso Restrito (+18)</h1>
        <p class="description">Nosso motor verifica sua idade através da biometria facial usando a placa de vídeo do seu dispositivo. Nenhuma imagem é gravada ou enviada.</p>

        <!-- Área do Vídeo -->
        <div class="video-wrapper">
            <!-- Overlay inicial de Loading -->
            <div id="loadingOverlay" class="overlay">
                <div style="text-align:center;">
                    <div class="spinner"></div>
                    <span id="loadingText" style="font-size: 0.9rem;">Baixando I.A. (5MB)...</span>
                </div>
            </div>
            
            <video id="camera" autoplay muted playsinline></video>
        </div>

        <!-- Ação usando o CSS do Front18 (.btn.btn-primary) -->
        <button id="verifyBtn" class="btn btn-primary" style="width: 100%;" disabled>
            Analisar Biometria
        </button>

        <!-- Status -->
        <div id="statusMessage" class="status info"></div>
        
        <!-- Cancelar -->
        <a id="cancelLink" href="#" onclick="window.parent.postMessage('FRONT18_CONTENT_CANCEL', '*'); return false;" style="display:block; text-align:center; color: var(--text-secondary); margin-top: 20px; text-decoration: none; font-size: 0.85rem; font-family: var(--font-display);">← Cancelar e Voltar</a>
    </div>

    <!-- Scripts da IA -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        window.__F18_HOST_SITE = <?= json_encode($_GET['host'] ?? 'Acesso Direto') ?>;
    </script>
    <!-- App Js com as Regras de Negócio e o PostMessage pro Pai -->
    <script src="<?= $basePath ?>js/app.js"></script>
</body>
</html>
