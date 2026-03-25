<?php
/**
 * Arquivo: index.php | Controlador Frontal e Roteador Principal do Sistema SaaS
 * @author Documentado por Gil Santos e Leandro Satt
 * @projeto Front18 Pro SaaS Architecture
 */
/**
 * Ponto de Entrada Global (Redirecionador de Raiz)
 * Envia o tráfego do domínio principal direto para o Front Controller blindado em /public
 */
require_once __DIR__ . '/public/index.php';
