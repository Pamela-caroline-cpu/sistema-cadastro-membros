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

// descobre o IP da máquina na rede local, sem precisar de extensão nenhuma:
// "conecta" um socket udp a um endereço externo (não manda nada de verdade,
// só faz o SO escolher a rota) e lê qual IP local foi usado nessa rota
function ip_local_da_maquina(): ?string
{
    $conexao = @stream_socket_client('udp://8.8.8.8:53', $codigoErro, $mensagemErro, 1);
    if (!$conexao) {
        return null;
    }
    $nome = stream_socket_get_name($conexao, false);
    fclose($conexao);

    $posicao = strrpos($nome, ':');
    return $posicao !== false ? substr($nome, 0, $posicao) : $nome;
}
