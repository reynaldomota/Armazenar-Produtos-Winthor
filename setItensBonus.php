<?php

function setItensBonus($listaItensArmazenar, $listaConferente, $acao) {
    header('Content-Type: application/json; charset=UTF-8');
    require __DIR__ . '/config/conexao.php';
    $executar = function ($sql, $params = []) use ($conn) {
        $stmt = oci_parse($conn, $sql);
        if (!$stmt) throw new RuntimeException('Não foi possível preparar a gravação.');
        foreach ($params as $nome => &$valor) oci_bind_by_name($stmt, $nome, $valor);
        unset($valor);
        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            oci_free_statement($stmt);
            throw new RuntimeException('Não foi possível gravar o armazenamento.');
        }
        return $stmt;
    };
    try {
        if (!in_array($acao, ['iniciar', 'finalizar'], true)) throw new RuntimeException('Ação inválida.');
        if ($acao === 'iniciar' && (count($listaConferente) < 1 || count($listaConferente) > 4)) {
            throw new RuntimeException('Informe de 1 a 4 conferentes.');
        }
        foreach ($listaConferente as &$codigo) {
            $codigo = filter_var($codigo, FILTER_VALIDATE_INT);
            if (!$codigo || $codigo < 1) throw new RuntimeException('Código de conferente inválido.');
        }
        unset($codigo);
        $itens = [];
        foreach ($listaItensArmazenar as $item) {
            $bonus = filter_var($item['numbonus'], FILTER_VALIDATE_INT);
            $produto = filter_var($item['codprod'], FILTER_VALIDATE_INT);
            if (!$bonus || !$produto || $bonus < 1 || $produto < 1) throw new RuntimeException('Produto ou bônus inválido.');
            $itens[$bonus][$produto] = $produto;
        }
        ksort($itens, SORT_NUMERIC);
        foreach ($itens as $bonus => $produtos) {
            // Serializa as gravações do mesmo bônus, inclusive a conclusão do último item.
            $stmt = $executar('SELECT numbonus FROM pcbonusc WHERE numbonus = :bonus FOR UPDATE', [':bonus' => $bonus]);
            $existe = oci_fetch_array($stmt, OCI_ASSOC);
            oci_free_statement($stmt);
            if (!$existe) throw new RuntimeException('Bônus não encontrado.');
            foreach ($produtos as $produto) {
                $params = [':bonus' => $bonus, ':produto' => $produto];
                $stmt = $executar('SELECT codprod FROM pcbonusi WHERE numbonus = :bonus AND codprod = :produto', $params);
                $existe = oci_fetch_array($stmt, OCI_ASSOC);
                oci_free_statement($stmt);
                if (!$existe) throw new RuntimeException('Produto não pertence ao bônus.');
                $stmt = $executar('SELECT COUNT(*) AS TOTAL,
                    MAX(dtarmazenagemini) AS dtarmazenagemini,
                    MAX(dtarmazenagemfin) AS dtarmazenagemfin,
                    MAX(dtarmazenagem) AS dtarmazenagem
                    FROM iwbonusarmazenar WHERE numbonus = :bonus AND codprod = :produto', $params);
                $arm = oci_fetch_array($stmt, OCI_ASSOC);
                oci_free_statement($stmt);
                if (!$arm || (int) $arm['TOTAL'] === 0) $arm = false;
                if ($arm && ($arm['DTARMAZENAGEMFIN'] || $arm['DTARMAZENAGEM'])) throw new RuntimeException('Um dos itens já foi finalizado. Atualize a tabela.');
                if ($acao === 'iniciar') {
                    if ($arm && $arm['DTARMAZENAGEMINI']) continue;
                    for ($i = 1; $i <= 4; $i++) $params[':sep' . $i] = isset($listaConferente[$i - 1]) ? $listaConferente[$i - 1] : null;
                    $sql = $arm
                        ? 'UPDATE iwbonusarmazenar SET dtarmazenagemini = SYSDATE, codseparador1 = :sep1, codseparador2 = :sep2, codseparador3 = :sep3, codseparador4 = :sep4 WHERE numbonus = :bonus AND codprod = :produto'
                        : 'INSERT INTO iwbonusarmazenar (numbonus, codprod, dtarmazenagemini, codseparador1, codseparador2, codseparador3, codseparador4) VALUES (:bonus, :produto, SYSDATE, :sep1, :sep2, :sep3, :sep4)';
                } else {
                    if (!$arm || !$arm['DTARMAZENAGEMINI']) throw new RuntimeException('Inicie todos os itens selecionados antes de finalizar.');
                    $sql = 'UPDATE iwbonusarmazenar SET dtarmazenagemfin = SYSDATE WHERE numbonus = :bonus AND codprod = :produto';
                }
                $stmt = $executar($sql, $params);
                oci_free_statement($stmt);
                if ($acao === 'finalizar') {
                    // A data geral pertence somente ao item que conclui o bônus.
                    $stmt = $executar('UPDATE iwbonusarmazenar arm SET dtarmazenagem = SYSDATE
                        WHERE arm.numbonus = :bonus AND arm.codprod = :produto
                        AND NOT EXISTS (
                            SELECT 1 FROM pcbonusi item WHERE item.numbonus = arm.numbonus
                            AND NOT EXISTS (
                                SELECT 1 FROM iwbonusarmazenar concluido
                                WHERE concluido.numbonus = item.numbonus AND concluido.codprod = item.codprod
                                AND (concluido.dtarmazenagemfin IS NOT NULL OR concluido.dtarmazenagem IS NOT NULL)
                            )
                        )', [':bonus' => $bonus, ':produto' => $produto]);
                    oci_free_statement($stmt);
                }
            }
        }
        if (!oci_commit($conn)) throw new RuntimeException('Não foi possível confirmar a gravação.');
        echo json_encode(['erro' => false, 'tipo' => 'success', 'msg' => $acao === 'iniciar' ? 'Armazenamento iniciado.' : 'Armazenamento finalizado.']);
    } catch (Exception $erro) {
        oci_rollback($conn);
        echo json_encode(['erro' => true, 'tipo' => 'warning', 'msg' => $erro->getMessage()]);
    }
}
