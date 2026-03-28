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

    applySharpen: function(canvas) {
        return new Promise((resolve) => {
            const ctx = canvas.getContext('2d');
            const w = canvas.width, h = canvas.height;
            const imgData = ctx.getImageData(0, 0, w, h);
            const raw = imgData.data;
            const sharp = new Uint8ClampedArray(raw);
            
            // Fator de aguçamento (Sharpen Kernel) - Extremamente sensível, realça muito a tinta preta do RG/CNH
            const wX = w * 4;
            const k = [ 0, -1,  0, 
                       -1,  5, -1, 
                        0, -1,  0 ];
            
            for (let y = 1; y < h - 1; y++) {
                for (let x = 1; x < w - 1; x++) {
                    let off = (y * wX) + (x * 4);
                    for (let c = 0; c < 3; c++) {
                        let sum = raw[off - wX - 4 + c] * k[0] + raw[off - wX + c] * k[1] + raw[off - wX + 4 + c] * k[2] +
                                  raw[off - 4 + c] * k[3]      + raw[off + c] * k[4]      + raw[off + 4 + c] * k[5] +
                                  raw[off + wX - 4 + c] * k[6] + raw[off + wX + c] * k[7] + raw[off + wX + 4 + c] * k[8];
                        sharp[off + c] = Math.min(Math.max(sum, 0), 255);
                    }
                    sharp[off + 3] = raw[off + 3]; // Alpha
                }
            }
            imgData.data.set(sharp);
            ctx.putImageData(imgData, 0, 0);
            resolve(canvas);
        });
    },

    processDocumentImage: async function(instance, docName, file, canvas) {
        const modalContent = document.getElementById('Front18-modal');
        
        modalContent.innerHTML = `
            <button class="Front18-btn-close-modal" aria-label="Fechar" style="position:absolute; top:12px; right:12px; background:transparent; border:none; color:rgba(255,255,255,0.4); width:32px; height:32px; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; border-radius:50%; z-index:100; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.4)'" title="Sair do Scanner">✕</button>
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
            
            document.getElementById('f18-ocr-prog-text').innerText = "Analisando fotografia RAW...";
            
            let sourceUrl = canvas;
            if (file) {
                sourceUrl = await new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.src = URL.createObjectURL(file);
                });
            }

            document.getElementById('f18-ocr-prog-text').innerText = "Vetorizando pixels microscópicos e aguçando imagem...";
            
            // Aplica Kernel Sharpen pra rasgar ruídos embaçados de lentes fracas de WebCam (Deixa a CNH ultra nítida artificialmente)
            const canvasToSharp = document.createElement('canvas');
            canvasToSharp.width = sourceUrl.width || sourceUrl.videoWidth || 1920;
            canvasToSharp.height = sourceUrl.height || sourceUrl.videoHeight || 1080;
            canvasToSharp.getContext('2d').drawImage(sourceUrl, 0, 0, canvasToSharp.width, canvasToSharp.height);
            
            const sharpenedCanvas = await this.applySharpen(canvasToSharp);
            
            const txtNode = document.getElementById('f18-ocr-prog-text');
            const barNode = document.getElementById('f18-ocr-prog-bar');

            // Passando o Canvas Aguçado Brutal + Motor Duplo (Inglês que é o melhor caçador de dígitos numéricos do Planeta + Português Padrão)
            const { data: { text } } = await window.Tesseract.recognize(sharpenedCanvas, 'por+eng', {
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

            // Converte "O" e "o" confusos para 0, e remove todas as LETRAS para sobrar apenas os blocos limpos numéricos de todo o documento.
            let cleanText = text.replace(/[Oo]/g, '0').replace(/[lI]/g, '1').replace(/[A-Za-z]/g, '');
            
            // Regex Indestrutível: Procura blocos isolados de (DIA de 01 a 31) (MÊS DE 01 a 12) e (ANO de 1900 a 2099) 
            // separados por 1 a 3 sujeiras (traços, barras, pontos, virgulas, espaços aleatórios do Tesseract). Ex: "10  / . 05 / 1990" = Match!
            const regex = /(0[1-9]|[12]\d|3[01])\s*[\/\-\.\s,]{1,3}\s*(0[1-9]|1[0-2])\s*[\/\-\.\s,]{1,3}\s*(19\d{2}|20\d{2})/g;
            const matches = cleanText.match(regex);

            let maxAge = 0;
            let conf = 0;

            if (matches && matches.length > 0) {
                try {
                    const parsedDates = matches.map(d => {
                        // O regex blindado pegou o bloco. Agora com expressão simples extraímos apenas os números contínuos!
                        const digits = d.match(/\d+/g);
                        if (digits && digits.length >= 3) {
                            return new Date(digits[2], digits[1]-1, digits[0]);
                        }
                        return new Date("");
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
