<?php
/**
 * Hub Mobile Handoff - Front18 Pro
 * A interface dedicada que roda no celular quando o usuário escaneia o QR Code.
 */
$token = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['token'] ?? '');
if (!$token) {
    die("Token Inválido ou Ausente.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sincronismo Mobile | Front18 Pro</title>
    <style>
        body { margin: 0; padding: 0; background-color: #0f172a; color: #f8fafc; font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .header { background: #1e293b; padding: 16px; text-align: center; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); z-index: 10; }
        .header h1 { margin: 0; font-size: 16px; font-weight: 600; }
        .content { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; position: relative; }
        
        #video-container { position: relative; width: 280px; height: 280px; border-radius: 50%; overflow: hidden; border: 4px solid #34d399; box-shadow: 0 0 40px rgba(52, 211, 153, 0.4); display: none; margin: 0 auto; background: #000; }
        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        
        button { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 18px 32px; font-size: 18px; font-weight: bold; border-radius: 12px; cursor: pointer; width: 100%; max-width: 300px; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); text-transform: uppercase; letter-spacing: 1px; }
        button:disabled { background: #334155; box-shadow: none; cursor: not-allowed; color: #94a3b8; }
        
        .ag-spinner { border: 3px solid rgba(255, 255, 255, 0.1); border-left-color: #fff; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        #hud-msg { margin-top: 24px; font-size: 14px; color: #cbd5e1; min-height: 48px; }
        #ag-time { font-size: 24px; font-weight: bold; color: #ef4444; margin-top: 10px; display: none; }
        #success-state { display: none; flex-direction: column; align-items: center; gap: 16px; }
    </style>
</head>
<body>

    <div class="header">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        <h1>Crossover Handoff B2B</h1>
    </div>

    <div class="content">
        
        <div id="intro-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2" style="margin-bottom:20px;"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            <h2 style="margin: 0 0 10px; font-size:22px;">Sessão Conectada!</h2>
            <p style="color:#94a3b8; font-size:14px; margin-bottom: 30px; line-height: 1.5;">O seu computador está aguardando você terminar por aqui. Habilite a câmera para validarmos seu Liveness.</p>
            <button id="btn-start" onclick="initEngine()">Habilitar Câmera</button>
        </div>

        <div id="video-container">
            <video id="videoElement" playsinline autoplay muted></video>
        </div>
        <div id="ag-time">15s</div>
        <div id="hud-msg"></div>

        <div id="success-state">
            <div style="width:80px; height:80px; border-radius:40px; background:#10b981; display:flex; align-items:center; justify-content:center; box-shadow: 0 0 30px rgba(16, 185, 129, 0.5);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h2 style="margin: 0;">Sincronizado!</h2>
            <p style="color:#94a3b8; font-size:14px;">Identidade aprovada. Pode voltar a olhar para a tela do seu computador, o acesso será liberado.</p>
        </div>

    </div>

    <!-- IA Neural Core via CDN Fast Edge -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <script>
        const token = '<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>';
        const video = document.getElementById('videoElement');
        const hud = document.getElementById('hud-msg');
        
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';

        async function initEngine() {
            document.getElementById('intro-state').style.display = 'none';
            document.getElementById('video-container').style.display = 'block';
            hud.innerHTML = `<span style="color:#38bdf8;">Solicitando Permissão da Câmera...</span>`;
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                video.srcObject = stream;
                
                video.onloadedmetadata = () => {
                    video.play();
                    startLiveness();
                };
            } catch (err) {
                hud.innerHTML = `<span style="color:#ef4444;">Erro de Câmera: Você bloqueou o acesso. Feche, recarregue e permita.</span>`;
            }
        }

        async function startLiveness() {
            hud.innerHTML = `<span style="color:#f59e0b;"><div class="ag-spinner" style="border-top-color:#f59e0b; width:14px; height:14px; border-width:2px; margin-right:5px;"></div> Carregando Rede Neural...</span>`;
            
            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL)
                ]);
            } catch (e) {
                hud.innerHTML = `<span style="color:#ef4444;">Falha de Conexão Neural CDN. Tente em outra rede WiFi.</span>`;
                return;
            }

            hud.innerHTML = `Por favor, olhe para a câmera (Mova o rosto suavemente).`;
            const timer = document.getElementById('ag-time');
            timer.style.display = 'block';
            
            let timeleft = 15;
            let success = false;
            
            const physicsLoop = setInterval(() => {
                timeleft--;
                timer.innerText = `${timeleft}s`;
                if(timeleft <= 0) {
                    clearInterval(physicsLoop);
                    if(!success) {
                        video.srcObject.getTracks().forEach(t => t.stop());
                        hud.innerHTML = `<span style="color:#ef4444;">Tempo Esgotado! Atualize a página e tente de novo.</span>`;
                    }
                }
            }, 1000);

            const analyzeFrame = async () => {
                if (success || timeleft <= 0) return;

                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224 }))
                    .withFaceLandmarks();

                if (detection) {
                    const jawLeft = detection.landmarks.positions[0];
                    const jawRight = detection.landmarks.positions[16];
                    const nose = detection.landmarks.positions[30];
                    const faceW = jawRight.x - jawLeft.x;
                    const noseRatio = (nose.x - jawLeft.x) / faceW;
                    
                    // Simple Mobile Liveness: just ensure the face is slightly moving and clear.
                    // To prevent static photo spoofing, we demand the nose moves out of the dead center slightly.
                    if (noseRatio < 0.35 || noseRatio > 0.65) {
                        // Vida Comprovada (Mudou o yaw)
                        success = true;
                        clearInterval(physicsLoop);
                        timer.style.display = 'none';
                        completeHandoff();
                        return;
                    } else {
                        hud.innerHTML = `Vire levemente o rosto...`;
                    }
                } else {
                    hud.innerHTML = `Rosto não detectado. Centralize-se.`;
                }
                
                requestAnimationFrame(analyzeFrame);
            };
            
            analyzeFrame();
        }

        async function completeHandoff() {
            video.srcObject.getTracks().forEach(t => t.stop());
            document.getElementById('video-container').style.display = 'none';
            hud.style.display = 'none';
            
            try {
                const fd = new FormData();
                fd.append('action', 'complete');
                fd.append('token', token);
                fd.append('status', 'approved');
                fd.append('methods', JSON.stringify(['mobile_liveness']));
                
                const res = await fetch('api/handoff.php', {
                    method: 'POST',
                    body: fd
                });
                
                const data = await res.json();
                if(data.success) {
                    document.getElementById('success-state').style.display = 'flex';
                } else {
                    hud.style.display = 'block';
                    hud.innerHTML = `<span style="color:#ef4444;">Dispositivo Sincronizado, mas o tempo da sessão já expirou no PC principal.</span>`;
                }
            } catch(e) {
                hud.style.display = 'block';
                hud.innerHTML = `<span style="color:#ef4444;">Erro de Conexão com o Hub B2B.</span>`;
            }
        }
    </script>
</body>
</html>
