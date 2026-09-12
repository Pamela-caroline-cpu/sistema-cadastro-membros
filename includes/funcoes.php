<?php
// Funções utilitárias compartilhadas pelas páginas do sistema

function formatarDataBr(?string $data): string
{
    if (empty($data) || $data === '0000-00-00') {
        return '-';
    }
    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y', $timestamp) : '-';
}

function exibir(?string $valor): string
{
    return $valor !== null && $valor !== '' ? htmlspecialchars($valor) : '-';
}

// mesma lista usada no form admin e no autocadastro público
function funcoes_disponiveis(): array
{
    return ['Membro', 'Líder de Louvor', 'Diácono', 'Diaconisa', 'Obreiro(a)', 'Pastor Auxiliar', 'Pastor Titular'];
}
