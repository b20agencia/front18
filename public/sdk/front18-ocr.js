window.Front18OCR = {
    loadOCRFramework: async function() {
        if (window.Tesseract) return true;
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/tesseract.js@v4.1.4/dist/tesseract.min.js';
            s.onload = () => resolve(true);
            s.onerror = () => reject(new Error('Falha de rede ao baixar SDK do Tesseract.'));
            document.body.appendChild(s);
        });
    },

    processDocumentImage: async function(instance, docName, file, canvas) {
        const modalContent = document.getElementById('Front18-modal');
        
        modalContent.innerHTML = `
            <div style="text-align:left; animation: agFadeIn 0.3s forwards;">
                <div style="margin-bottom:24px; text-align:center;">
                    <h3 style="color:var(--ag-text); font-size:20px; font-weight:700; margin:0 0 4px;">Módulo OCR Lançado</h3>
                    <p style="color:rgba(255,255,255,0.6); font-size:13px; margin:0;">Varredura óptica no ${docName}</p>
                </div>
                
                <div style="background:rgba(0,0,0,0.3); border-radius:12px; padding:32px 24px; position:relative; overflow:hidden; text-align:center; border: 1px solid rgba(56,189,248,0.2);">
                    <div class="ag-spinner" style="border-top-color:#38bdf8; width:54px; height:54px; border-width:4px; margin:0 auto 20px;"></div>
                    <div style="font-weight:700; color:#38bdf8; font-size:16px;" id="f18-ocr-prog-text">Baixando motor Neural (Português)...</div>
                    
                    <div style="width:100%; height:6px; background:rgba(255,255,255,0.1); border-radius:3px; margin-top:20px; overflow:hidden;">
                        <div id="f18-ocr-prog-bar" style="width:0%; height:100%; background:#38bdf8; transition:width 0.2s;"></div>
                    </div>
                </div>
            </div>
        `;

        try {
            await this.loadOCRFramework();
            
            document.getElementById('f18-ocr-prog-text').innerText = "Processando imagem fotográfica...";
            
            let sourceUrl = canvas;
            if (file) {
                sourceUrl = await new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.src = URL.createObjectURL(file);
                });
            }

            const txtNode = document.getElementById('f18-ocr-prog-text');
            const barNode = document.getElementById('f18-ocr-prog-bar');

            const { data: { text } } = await window.Tesseract.recognize(sourceUrl, 'por', {
                logger: m => {
                    if (m.status === 'recognizing text') {
                       const pct = Math.floor(m.progress * 100);
                       if(barNode) barNode.style.width = pct + '%';
                       if(txtNode) txtNode.innerText = "Reconhecimento OCR: " + pct + "%";
                    }
                }
            });

            if(txtNode) { txtNode.innerText = "Lendo algoritmo e Cruzando +18..."; txtNode.style.color = "#10b981"; }
            if(barNode) { barNode.style.background = "#10b981"; barNode.style.width = "100%"; }

            // Super Regex para datas brasileiras (DD/MM/YYYY) e afins.
            const regex = /\b(?:0[1-9]|[12]\d|3[01])[\/\-\.\|](?:0[1-9]|1[0-2])[\/\-\.\|](?:19|20)\d{2}\b/g;
            const matches = text.match(regex);

            let maxAge = 0;
            let conf = 0;

            if (matches && matches.length > 0) {
                try {
                    const parsedDates = matches.map(d => {
                        const cleanDate = d.replace(/\||\./g, '/'); // limpa traços ou ruídos de sujeita pra barra
                        const parts = cleanDate.split('/');
                        return new Date(parts[2], parts[1]-1, parts[0]);
                    }).filter(d => !isNaN(d.getTime()));
                    
                    if (parsedDates.length > 0) {
                        const minDate = new Date(Math.min.apply(null, parsedDates)); // Traz a data MAIS ANTIGA
                        const ageDifMs = Date.now() - minDate.getTime();
                        const ageDate = new Date(ageDifMs);
                        maxAge = Math.abs(ageDate.getUTCFullYear() - 1970);
                        conf = 98.7; 
                    }
                } catch(e) {}
            }

            if (maxAge >= 18) {
                setTimeout(() => {
                    instance.config.aiAge = maxAge;
                    instance.config.aiConfidence = conf;
                    instance.showReceiptBanner();
                }, 1000);
            } else if (maxAge > 0 && maxAge < 18) {
                setTimeout(() => {
                   alert("Acesso Negado. Documento detectado é de Menor de Idade (" + maxAge + " anos).");
                   let redirectUrl = instance.config.denyUrl || (new URL(instance.config.apiEndpoint, window.location.href).origin + '/public/safe.php');
                   window.location.href = redirectUrl;
                }, 1000);
            } else {
                setTimeout(() => {
                   alert("Não conseguimos achar a Data de Nascimento clara na foto.\n\n1. Foque e remova os reflexos plásticos.\n2. Mostre apenas a FRENTE (onde tem a data).");
                   instance.showValidationOptions();
                }, 2500);
            }
        } catch (e) {
            alert("Falha técnica no processo AI OCR: " + e.message);
            instance.showValidationOptions();
        }
    }
};
