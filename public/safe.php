<?php
/**
 * Safe Exit Page - Fallback Neutro e Premium para Menores
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Protegido | Redirecionamento de Segurança</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="glass-panel max-w-lg w-full rounded-3xl p-8 text-center relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500"></div>
        
        <div class="w-20 h-20 bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-slate-800">
            <i class="ph-fill ph-hand-waving text-4xl text-indigo-400"></i>
        </div>

        <h1 class="text-2xl font-bold text-white mb-3">Este ambiente requer confirmação de capacidade civil</h1>
        <p class="text-slate-400 text-sm leading-relaxed mb-8">
            Você foi redirecionado com segurança pois escolheu não prosseguir com a verificação de idade estabelecida pelos proprietários do domínio anterior. Esta é uma medida de proteção técnica.
        </p>

        <div class="space-y-3">
            <button onclick="window.history.length > 2 ? window.history.back() : window.location.href='https://google.com'" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-lg shadow-indigo-500/20">
                Retornar à Navegação Anterior
            </button>
            <a href="https://google.com" class="block w-full bg-transparent hover:bg-slate-800 text-slate-300 font-bold py-3 px-6 rounded-xl transition-colors border border-slate-700">
                Página Inicial Segura (Google)
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-800/50">
            <p class="text-[10px] text-slate-500 font-mono flex items-center justify-center gap-1 uppercase tracking-widest">
                <i class="ph-fill ph-shield-check text-indigo-500 text-sm"></i> Operação de Diligência Protegida por AgeGate Pro
            </p>
        </div>
    </div>

</body>
</html>
